import 'package:fl_chart/fl_chart.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/format.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/app_card.dart';
import '../../../core/widgets/async_view.dart';
import '../../../core/widgets/status_pill.dart';
import '../../../shared/models/metric_statuses.dart';
import '../../../shared/models/telemetry_reading.dart';
import 'telemetry_providers.dart';

class TelemetryScreen extends ConsumerStatefulWidget {
  const TelemetryScreen({super.key});

  @override
  ConsumerState<TelemetryScreen> createState() => _TelemetryScreenState();
}

class _TelemetryScreenState extends ConsumerState<TelemetryScreen> {
  String _chartMetric = 'temperature';

  @override
  Widget build(BuildContext context) {
    final current = ref.watch(currentTelemetryProvider);
    final history = ref.watch(telemetryHistoryProvider);

    return RefreshIndicator(
      onRefresh: () async {
        ref.invalidate(currentTelemetryProvider);
        await ref.read(telemetryHistoryProvider.notifier).load();
      },
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
        children: [
          Text(
            'Lectura actual',
            style: Theme.of(context)
                .textTheme
                .titleLarge
                ?.copyWith(fontWeight: FontWeight.w700),
          ),
          const SizedBox(height: 12),
          SizedBox(
            height: 320,
            child: AsyncView(
              value: current,
              onRetry: () => ref.invalidate(currentTelemetryProvider),
              data: (data) => _CurrentReadingCard(
                reading: data.reading,
                statuses: data.statuses,
                activeAlerts: data.activeAlerts,
              ),
            ),
          ),
          const SizedBox(height: 24),
          Row(
            children: [
              Expanded(
                child: Text(
                  'Historial',
                  style: Theme.of(context)
                      .textTheme
                      .titleLarge
                      ?.copyWith(fontWeight: FontWeight.w700),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: [
                for (final key in MetricStatuses.metricKeys)
                  Padding(
                    padding: const EdgeInsets.only(right: 8),
                    child: ChoiceChip(
                      label: Text(AppLabels.metric(key)),
                      selected: _chartMetric == key,
                      onSelected: (_) => setState(() => _chartMetric = key),
                    ),
                  ),
              ],
            ),
          ),
          const SizedBox(height: 14),
          AsyncView(
            value: history,
            onRetry: () => ref.read(telemetryHistoryProvider.notifier).load(),
            loading: const SizedBox(
              height: 220,
              child: Center(child: CircularProgressIndicator()),
            ),
            data: (page) {
              if (page.isEmpty) {
                return const SizedBox(
                  height: 200,
                  child: EmptyState(
                    icon: Icons.show_chart,
                    title: 'Sin historial todavía',
                    message: 'Cuando lleguen lecturas aparecerán aquí.',
                  ),
                );
              }

              // El backend devuelve de la más reciente a la más antigua;
              // la gráfica necesita el orden cronológico.
              final readings = page.items.reversed.toList();

              return Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  _HistoryChart(readings: readings, metric: _chartMetric),
                  const SizedBox(height: 20),
                  Text(
                    'Últimas lecturas',
                    style: Theme.of(context)
                        .textTheme
                        .titleSmall
                        ?.copyWith(fontWeight: FontWeight.w700),
                  ),
                  const SizedBox(height: 8),
                  for (final reading in page.items)
                    _ReadingRow(reading: reading),
                  if (page.hasMore) ...[
                    const SizedBox(height: 12),
                    OutlinedButton(
                      onPressed: () =>
                          ref.read(telemetryHistoryProvider.notifier).loadMore(),
                      child: const Text('Cargar más lecturas'),
                    ),
                  ],
                ],
              );
            },
          ),
        ],
      ),
    );
  }
}

class _CurrentReadingCard extends StatelessWidget {
  const _CurrentReadingCard({
    required this.reading,
    required this.statuses,
    required this.activeAlerts,
  });

  final TelemetryReading reading;
  final MetricStatuses statuses;
  final int activeAlerts;

  @override
  Widget build(BuildContext context) {
    return AppCard(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                StatusPill.forStatus(statuses.overall),
                const Spacer(),
                if (activeAlerts > 0)
                  StatusPill(
                    label: '$activeAlerts activas',
                    color: AppColors.ember,
                    icon: Icons.notifications_active_outlined,
                    compact: true,
                  ),
              ],
            ),
            const SizedBox(height: 12),
            Expanded(
              child: ListView(
                padding: EdgeInsets.zero,
                children: [
                  for (final key in MetricStatuses.metricKeys)
                    Padding(
                      padding: const EdgeInsets.only(bottom: 10),
                      child: Row(
                        children: [
                          Container(
                            width: 7,
                            height: 7,
                            decoration: BoxDecoration(
                              color: AppColors.forStatus(statuses.of(key)),
                              shape: BoxShape.circle,
                            ),
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Text(
                              AppLabels.metric(key),
                              style: const TextStyle(fontSize: 14),
                            ),
                          ),
                          Text(
                            Fmt.metric(
                              reading.metric(key),
                              TelemetryReading.unitFor(key),
                            ),
                            style: const TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                        ],
                      ),
                    ),
                ],
              ),
            ),
            const Divider(),
            const SizedBox(height: 8),
            Text(
              '${AppLabels.deviceStatus(reading.deviceStatus)} · ${Fmt.relative(reading.recordedAt)}',
              style: TextStyle(
                fontSize: 12.5,
                color: AppColors.slate.withOpacity(0.6),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _HistoryChart extends StatelessWidget {
  const _HistoryChart({required this.readings, required this.metric});

  final List<TelemetryReading> readings;
  final String metric;

  @override
  Widget build(BuildContext context) {
    final spots = <FlSpot>[
      for (var i = 0; i < readings.length; i++)
        FlSpot(i.toDouble(), readings[i].metric(metric)),
    ];

    final values = spots.map((spot) => spot.y).toList()..sort();
    final min = values.first;
    final max = values.last;
    final padding = ((max - min).abs() * 0.15).clamp(0.5, double.infinity).toDouble();

    return AppCard(
      child: Padding(
        padding: const EdgeInsets.fromLTRB(8, 20, 18, 12),
        child: SizedBox(
          height: 210,
          child: LineChart(
            LineChartData(
              minY: min - padding,
              maxY: max + padding,
              gridData: FlGridData(
                show: true,
                drawVerticalLine: false,
                getDrawingHorizontalLine: (_) => FlLine(
                  color: Colors.black.withOpacity(0.06),
                  strokeWidth: 1,
                ),
              ),
              titlesData: FlTitlesData(
                rightTitles: const AxisTitles(
                  sideTitles: SideTitles(showTitles: false),
                ),
                topTitles: const AxisTitles(
                  sideTitles: SideTitles(showTitles: false),
                ),
                leftTitles: AxisTitles(
                  sideTitles: SideTitles(
                    showTitles: true,
                    reservedSize: 42,
                    getTitlesWidget: (value, meta) => Text(
                      value.toStringAsFixed(0),
                      style: TextStyle(
                        fontSize: 10,
                        color: AppColors.slate.withOpacity(0.5),
                      ),
                    ),
                  ),
                ),
                bottomTitles: AxisTitles(
                  sideTitles: SideTitles(
                    showTitles: true,
                    reservedSize: 28,
                    interval: (readings.length / 4).ceilToDouble().clamp(1, 999).toDouble(),
                    getTitlesWidget: (value, meta) {
                      final index = value.round();
                      if (index < 0 || index >= readings.length) {
                        return const SizedBox.shrink();
                      }
                      return Padding(
                        padding: const EdgeInsets.only(top: 6),
                        child: Text(
                          Fmt.time(readings[index].recordedAt),
                          style: TextStyle(
                            fontSize: 10,
                            color: AppColors.slate.withOpacity(0.5),
                          ),
                        ),
                      );
                    },
                  ),
                ),
              ),
              borderData: FlBorderData(show: false),
              lineTouchData: LineTouchData(
                touchTooltipData: LineTouchTooltipData(
                  getTooltipItems: (touchedSpots) => touchedSpots.map((spot) {
                    final reading = readings[spot.x.round()];
                    return LineTooltipItem(
                      '${Fmt.metric(spot.y, TelemetryReading.unitFor(metric))}\n${Fmt.shortDateTime(reading.recordedAt)}',
                      const TextStyle(
                        color: Colors.white,
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                      ),
                    );
                  }).toList(),
                ),
              ),
              lineBarsData: [
                LineChartBarData(
                  spots: spots,
                  isCurved: true,
                  curveSmoothness: 0.25,
                  color: AppColors.leaf,
                  barWidth: 2.4,
                  dotData: const FlDotData(show: false),
                  belowBarData: BarAreaData(
                    show: true,
                    color: AppColors.leaf.withOpacity(0.12),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _ReadingRow extends StatelessWidget {
  const _ReadingRow({required this.reading});

  final TelemetryReading reading;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.black.withOpacity(0.06)),
      ),
      child: Row(
        children: [
          Expanded(
            flex: 3,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  Fmt.shortDateTime(reading.recordedAt),
                  style: const TextStyle(
                    fontSize: 13.5,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                Text(
                  AppLabels.deviceStatus(reading.deviceStatus),
                  style: TextStyle(
                    fontSize: 11.5,
                    color: AppColors.slate.withOpacity(0.5),
                  ),
                ),
              ],
            ),
          ),
          Expanded(
            flex: 4,
            child: Text(
              '${Fmt.metric(reading.temperature, '°C')} · ${Fmt.metric(reading.soilHumidity, '%')} suelo',
              textAlign: TextAlign.end,
              style: const TextStyle(fontSize: 13),
            ),
          ),
        ],
      ),
    );
  }
}
