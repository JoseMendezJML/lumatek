import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/format.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/async_view.dart';
import '../../auth/presentation/auth_controller.dart';
import '../domain/greenhouse.dart';

/// La web guarda el invernadero activo en sesión; la API móvil es stateless,
/// así que la elección vive aquí y su id viaja en cada URL.
class GreenhousePickerScreen extends ConsumerWidget {
  const GreenhousePickerScreen({super.key, this.canGoBack = false});

  /// Cuando se abre desde el menú (para cambiar de invernadero) se puede
  /// cerrar; cuando es el paso obligado tras el login, no.
  final bool canGoBack;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final auth = ref.watch(authControllerProvider);
    final greenhouses = auth.greenhouses;

    return Scaffold(
      appBar: AppBar(
        backgroundColor: AppColors.canopy,
        foregroundColor: Colors.white,
        elevation: 0,
        centerTitle: false,
        title: const Text('Elige un invernadero'),
        automaticallyImplyLeading: canGoBack,
      ),
      body: RefreshIndicator(
        onRefresh: () =>
            ref.read(authControllerProvider.notifier).refreshGreenhouses(),
        child: greenhouses.isEmpty
            ? ListView(
                children: const [
                  SizedBox(height: 120),
                  EmptyState(
                    icon: Icons.grass_outlined,
                    title: 'No tienes invernaderos asignados',
                    message:
                        'Pide a un administrador que te asigne como responsable de al menos un invernadero.',
                  ),
                ],
              )
            : ListView.separated(
                padding: const EdgeInsets.fromLTRB(16, 16, 16, 28),
                itemCount: greenhouses.length,
                separatorBuilder: (_, __) => const SizedBox(height: 12),
                itemBuilder: (context, index) {
                  final greenhouse = greenhouses[index];
                  return _GreenhouseCard(
                    greenhouse: greenhouse,
                    isSelected: greenhouse.id == auth.selectedGreenhouseId,
                    onTap: () {
                      ref
                          .read(authControllerProvider.notifier)
                          .selectGreenhouse(greenhouse.id);
                      if (canGoBack && Navigator.of(context).canPop()) {
                        Navigator.of(context).pop();
                      }
                    },
                  );
                },
              ),
      ),
    );
  }
}

class _GreenhouseCard extends StatelessWidget {
  const _GreenhouseCard({
    required this.greenhouse,
    required this.isSelected,
    required this.onTap,
  });

  final Greenhouse greenhouse;
  final bool isSelected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;
    final reading = greenhouse.latestReading;

    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(14),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(14),
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(14),
            border: Border.all(
              color: isSelected
                  ? AppColors.leaf
                  : Colors.black.withOpacity(0.07),
              width: isSelected ? 1.8 : 1,
            ),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          greenhouse.name,
                          style: textTheme.titleMedium?.copyWith(
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          greenhouse.code,
                          style: textTheme.bodySmall?.copyWith(
                            color: AppColors.slate.withOpacity(0.55),
                          ),
                        ),
                      ],
                    ),
                  ),
                  if (isSelected)
                    const Icon(Icons.check_circle, color: AppColors.leaf),
                ],
              ),
              const SizedBox(height: 12),
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: [
                  if (greenhouse.cropType != null)
                    _Tag(icon: Icons.spa_outlined, label: greenhouse.cropType!),
                  if (greenhouse.location != null)
                    _Tag(
                      icon: Icons.place_outlined,
                      label: greenhouse.location!,
                    ),
                  _Tag(
                    icon: greenhouse.isActive
                        ? Icons.play_circle_outline
                        : Icons.pause_circle_outline,
                    label: AppLabels.greenhouseStatus(greenhouse.status),
                  ),
                ],
              ),
              if (reading != null) ...[
                const SizedBox(height: 12),
                const Divider(),
                const SizedBox(height: 10),
                Row(
                  children: [
                    Expanded(
                      child: _MiniMetric(
                        label: 'Temperatura',
                        value: Fmt.metric(reading.temperature, '°C'),
                      ),
                    ),
                    Expanded(
                      child: _MiniMetric(
                        label: 'Humedad suelo',
                        value: Fmt.metric(reading.soilHumidity, '%'),
                      ),
                    ),
                    Expanded(
                      child: _MiniMetric(
                        label: 'Última lectura',
                        value: Fmt.relative(reading.recordedAt),
                      ),
                    ),
                  ],
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}

class _Tag extends StatelessWidget {
  const _Tag({required this.icon, required this.label});

  final IconData icon;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
      decoration: BoxDecoration(
        color: AppColors.mist,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: AppColors.slate.withOpacity(0.6)),
          const SizedBox(width: 5),
          Text(
            label,
            style: TextStyle(
              fontSize: 12,
              color: AppColors.slate.withOpacity(0.75),
            ),
          ),
        ],
      ),
    );
  }
}

class _MiniMetric extends StatelessWidget {
  const _MiniMetric({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: TextStyle(
            fontSize: 11,
            color: AppColors.slate.withOpacity(0.5),
          ),
        ),
        const SizedBox(height: 2),
        Text(
          value,
          style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w600),
        ),
      ],
    );
  }
}
