import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/format.dart';
import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/async_view.dart';
import '../../../core/widgets/status_pill.dart';
import '../domain/alert.dart';
import 'alert_providers.dart';

class AlertsScreen extends ConsumerWidget {
  const AlertsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final filters = ref.watch(alertFiltersProvider);
    final alerts = ref.watch(alertListProvider);

    return Column(
      children: [
        _FilterBar(filters: filters),
        const Divider(height: 1),
        Expanded(
          child: RefreshIndicator(
            onRefresh: () => ref.read(alertListProvider.notifier).load(),
            child: AsyncView(
              value: alerts,
              onRetry: () => ref.read(alertListProvider.notifier).load(),
              data: (page) {
                if (page.isEmpty) {
                  return ListView(
                    children: [
                      const SizedBox(height: 100),
                      EmptyState(
                        icon: Icons.notifications_off_outlined,
                        title: filters.isEmpty
                            ? 'Sin alertas'
                            : 'Ninguna alerta con esos filtros',
                        message: filters.isEmpty
                            ? 'El invernadero no ha disparado alertas.'
                            : 'Prueba quitando algún filtro.',
                        action: filters.isEmpty
                            ? null
                            : TextButton(
                                onPressed: () => ref
                                    .read(alertFiltersProvider.notifier)
                                    .state = const AlertFilters(),
                                child: const Text('Quitar filtros'),
                              ),
                      ),
                    ],
                  );
                }

                return ListView.builder(
                  padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
                  itemCount: page.items.length + (page.hasMore ? 1 : 0),
                  itemBuilder: (context, index) {
                    if (index >= page.items.length) {
                      return Padding(
                        padding: const EdgeInsets.only(top: 8),
                        child: OutlinedButton(
                          onPressed: () =>
                              ref.read(alertListProvider.notifier).loadMore(),
                          child: const Text('Cargar más'),
                        ),
                      );
                    }

                    return _AlertCard(alert: page.items[index]);
                  },
                );
              },
            ),
          ),
        ),
      ],
    );
  }
}

class _FilterBar extends ConsumerWidget {
  const _FilterBar({required this.filters});

  final AlertFilters filters;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    void setSeverity(String? value) {
      ref.read(alertFiltersProvider.notifier).state = filters.copyWith(
        severity: value,
        clearSeverity: value == null,
      );
    }

    void setStatus(String? value) {
      ref.read(alertFiltersProvider.notifier).state = filters.copyWith(
        status: value,
        clearStatus: value == null,
      );
    }

    return Container(
      color: Colors.white,
      padding: const EdgeInsets.symmetric(vertical: 10),
      child: SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16),
        child: Row(
          children: [
            _FilterChip(
              label: 'Todas',
              selected: filters.isEmpty,
              onSelected: () {
                ref.read(alertFiltersProvider.notifier).state =
                    const AlertFilters();
              },
            ),
            const SizedBox(width: 8),
            const _Separator(),
            const SizedBox(width: 8),
            for (final severity in ['critical', 'warning', 'info']) ...[
              _FilterChip(
                label: AppLabels.severity(severity),
                selected: filters.severity == severity,
                color: AppColors.forSeverity(severity),
                onSelected: () => setSeverity(
                  filters.severity == severity ? null : severity,
                ),
              ),
              const SizedBox(width: 8),
            ],
            const _Separator(),
            const SizedBox(width: 8),
            for (final status in ['new', 'viewed', 'resolved']) ...[
              _FilterChip(
                label: AppLabels.alertStatus(status),
                selected: filters.status == status,
                onSelected: () =>
                    setStatus(filters.status == status ? null : status),
              ),
              const SizedBox(width: 8),
            ],
          ],
        ),
      ),
    );
  }
}

class _Separator extends StatelessWidget {
  const _Separator();

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 1,
      height: 22,
      color: Colors.black.withOpacity(0.08),
    );
  }
}

class _FilterChip extends StatelessWidget {
  const _FilterChip({
    required this.label,
    required this.selected,
    required this.onSelected,
    this.color,
  });

  final String label;
  final bool selected;
  final VoidCallback onSelected;
  final Color? color;

  @override
  Widget build(BuildContext context) {
    final accent = color ?? AppColors.canopy;

    return GestureDetector(
      onTap: onSelected,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
        decoration: BoxDecoration(
          color: selected ? accent.withOpacity(0.13) : AppColors.mist,
          borderRadius: BorderRadius.circular(999),
          border: Border.all(
            color: selected ? accent.withOpacity(0.5) : Colors.transparent,
          ),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 13,
            fontWeight: selected ? FontWeight.w700 : FontWeight.w500,
            color: selected ? accent : AppColors.slate.withOpacity(0.7),
          ),
        ),
      ),
    );
  }
}

class _AlertCard extends ConsumerStatefulWidget {
  const _AlertCard({required this.alert});

  final Alert alert;

  @override
  ConsumerState<_AlertCard> createState() => _AlertCardState();
}

class _AlertCardState extends ConsumerState<_AlertCard> {
  bool _busy = false;

  Future<void> _run(Future<void> Function() action, String successMessage) async {
    setState(() => _busy = true);
    try {
      await action();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(successMessage)),
        );
      }
    } on ApiException catch (error) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(error.message),
            backgroundColor: AppColors.ember,
          ),
        );
      }
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final alert = widget.alert;
    final controller = ref.read(alertListProvider.notifier);
    final color = AppColors.forSeverity(alert.severity);

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: Colors.black.withOpacity(0.06)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            height: 3,
            decoration: BoxDecoration(
              color: color,
              borderRadius: const BorderRadius.vertical(top: Radius.circular(13)),
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    StatusPill.forSeverity(alert.severity, compact: true),
                    const SizedBox(width: 8),
                    if (alert.isNew)
                      const StatusPill(
                        label: 'Nueva',
                        color: AppColors.water,
                        compact: true,
                      )
                    else if (alert.isResolved)
                      const StatusPill(
                        label: 'Resuelta',
                        color: AppColors.leaf,
                        icon: Icons.check,
                        compact: true,
                      ),
                    const Spacer(),
                    Text(
                      Fmt.relative(alert.lastTriggeredAt),
                      style: TextStyle(
                        fontSize: 11.5,
                        color: AppColors.slate.withOpacity(0.5),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                Text(
                  alert.title,
                  style: const TextStyle(
                    fontSize: 15.5,
                    fontWeight: FontWeight.w700,
                    height: 1.25,
                  ),
                ),
                if (alert.description != null) ...[
                  const SizedBox(height: 4),
                  Text(
                    alert.description!,
                    style: TextStyle(
                      fontSize: 13.5,
                      height: 1.35,
                      color: AppColors.slate.withOpacity(0.72),
                    ),
                  ),
                ],
                if (alert.variable != null) ...[
                  const SizedBox(height: 8),
                  Text(
                    '${AppLabels.metric(alert.variable!)}: ${Fmt.number(alert.value)}',
                    style: TextStyle(
                      fontSize: 12.5,
                      color: AppColors.slate.withOpacity(0.6),
                    ),
                  ),
                ],
                if (!alert.isResolved) ...[
                  const SizedBox(height: 14),
                  Row(
                    children: [
                      if (alert.isNew)
                        TextButton(
                          onPressed: _busy
                              ? null
                              : () => _run(
                                    () => controller.markViewed(alert),
                                    'Alerta marcada como vista.',
                                  ),
                          child: const Text('Marcar como vista'),
                        ),
                      const Spacer(),
                      FilledButton.tonal(
                        onPressed: _busy
                            ? null
                            : () => _run(
                                  () => controller.resolve(alert),
                                  'Alerta resuelta.',
                                ),
                        style: FilledButton.styleFrom(
                          minimumSize: const Size(0, 38),
                          padding: const EdgeInsets.symmetric(horizontal: 18),
                        ),
                        child: _busy
                            ? const SizedBox(
                                height: 16,
                                width: 16,
                                child: CircularProgressIndicator(strokeWidth: 2),
                              )
                            : const Text('Resolver'),
                      ),
                    ],
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}
