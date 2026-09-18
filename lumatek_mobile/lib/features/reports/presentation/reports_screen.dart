import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/format.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/app_card.dart';
import '../../../core/widgets/async_view.dart';
import '../domain/report_summary.dart';
import 'report_providers.dart';

class ReportsScreen extends ConsumerWidget {
  const ReportsScreen({super.key});

  static const _ranges = {7: '7 días', 15: '15 días', 30: '30 días', 90: '90 días'};

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final days = ref.watch(reportDaysProvider);
    final summary = ref.watch(reportSummaryProvider);

    return RefreshIndicator(
      onRefresh: () async => ref.invalidate(reportSummaryProvider),
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
        children: [
          Text(
            'Reporte del periodo',
            style: Theme.of(context)
                .textTheme
                .titleLarge
                ?.copyWith(fontWeight: FontWeight.w700),
          ),
          const SizedBox(height: 12),
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: [
                for (final entry in _ranges.entries)
                  Padding(
                    padding: const EdgeInsets.only(right: 8),
                    child: ChoiceChip(
                      label: Text(entry.value),
                      selected: days == entry.key,
                      onSelected: (_) =>
                          ref.read(reportDaysProvider.notifier).state = entry.key,
                    ),
                  ),
              ],
            ),
          ),
          const SizedBox(height: 18),
          AsyncView(
            value: summary,
            onRetry: () => ref.invalidate(reportSummaryProvider),
            loading: const Padding(
              padding: EdgeInsets.all(40),
              child: Center(child: CircularProgressIndicator()),
            ),
            data: (data) => _ReportBody(summary: data),
          ),
        ],
      ),
    );
  }
}

class _ReportBody extends StatelessWidget {
  const _ReportBody({required this.summary});

  final ReportSummary summary;

  @override
  Widget build(BuildContext context) {
    if (!summary.hasData) {
      return const Padding(
        padding: EdgeInsets.symmetric(vertical: 40),
        child: EmptyState(
          icon: Icons.insert_chart_outlined,
          title: 'Sin datos en este periodo',
          message: 'No se registraron lecturas en el rango seleccionado.',
        ),
      );
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Text(
          'Del ${Fmt.date(summary.from)} al ${Fmt.date(summary.to)}',
          style: TextStyle(
            fontSize: 13,
            color: AppColors.slate.withOpacity(0.6),
          ),
        ),
        const SizedBox(height: 16),
        Row(
          children: [
            Expanded(
              child: _CountCard(
                icon: Icons.sensors,
                label: 'Lecturas',
                value: '${summary.readings}',
                color: AppColors.canopy,
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _CountCard(
                icon: Icons.water_drop_outlined,
                label: 'Riegos',
                value: '${summary.irrigations}',
                color: AppColors.water,
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _CountCard(
                icon: Icons.notifications_none,
                label: 'Alertas',
                value: '${summary.alerts}',
                color: AppColors.sun,
              ),
            ),
          ],
        ),
        const SizedBox(height: 20),
        Text(
          'Promedios del periodo',
          style: Theme.of(context)
              .textTheme
              .titleSmall
              ?.copyWith(fontWeight: FontWeight.w700),
        ),
        const SizedBox(height: 10),
        AppCard(
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
            child: Column(
              children: [
                _AverageRow(
                  label: 'Temperatura',
                  value: Fmt.metric(summary.temperatureAvg, '°C'),
                ),
                const Divider(height: 1),
                _AverageRow(
                  label: 'Humedad del suelo',
                  value: Fmt.metric(summary.soilHumidityAvg, '%'),
                ),
                const Divider(height: 1),
                _AverageRow(
                  label: 'Humedad ambiental',
                  value: Fmt.metric(summary.ambientHumidityAvg, '%'),
                ),
                const Divider(height: 1),
                _AverageRow(
                  label: 'Luminosidad',
                  value: Fmt.metric(summary.luminosityAvg, 'lux'),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }
}

class _CountCard extends StatelessWidget {
  const _CountCard({
    required this.icon,
    required this.label,
    required this.value,
    required this.color,
  });

  final IconData icon;
  final String label;
  final String value;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: Colors.black.withOpacity(0.06)),
      ),
      child: Column(
        children: [
          Icon(icon, color: color, size: 20),
          const SizedBox(height: 8),
          Text(
            value,
            style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w700),
          ),
          Text(
            label,
            style: TextStyle(
              fontSize: 12,
              color: AppColors.slate.withOpacity(0.6),
            ),
          ),
        ],
      ),
    );
  }
}

class _AverageRow extends StatelessWidget {
  const _AverageRow({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 14),
      child: Row(
        children: [
          Expanded(child: Text(label, style: const TextStyle(fontSize: 14))),
          Text(
            value,
            style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700),
          ),
        ],
      ),
    );
  }
}
