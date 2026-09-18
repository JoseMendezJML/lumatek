import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/config/app_config.dart';
import '../../../core/format.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/app_card.dart';
import '../../../core/widgets/async_view.dart';
import '../../../core/widgets/status_pill.dart';
import '../../../shared/models/metric_statuses.dart';
import '../../../shared/models/telemetry_reading.dart';
import '../../alerts/domain/alert.dart';
import '../../auth/presentation/auth_controller.dart';
import '../domain/dashboard_snapshot.dart';
import 'dashboard_providers.dart';

class DashboardScreen extends ConsumerStatefulWidget {
  const DashboardScreen({super.key});

  @override
  ConsumerState<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends ConsumerState<DashboardScreen> {
  Timer? _refreshTimer;

  @override
  void initState() {
    super.initState();
    // La telemetría se refresca sola: el operador no debería tener que
    // recordar bajar la pantalla para ver un dato fresco.
    _refreshTimer = Timer.periodic(
      AppConfig.telemetryRefreshInterval,
      (_) => ref.invalidate(dashboardProvider),
    );
  }

  @override
  void dispose() {
    _refreshTimer?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final snapshot = ref.watch(dashboardProvider);

    return RefreshIndicator(
      onRefresh: () async => ref.invalidate(dashboardProvider),
      child: AsyncView(
        value: snapshot,
        onRetry: () => ref.invalidate(dashboardProvider),
        data: (data) => _DashboardBody(snapshot: data),
      ),
    );
  }
}

class _DashboardBody extends ConsumerWidget {
  const _DashboardBody({required this.snapshot});

  final DashboardSnapshot snapshot;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final textTheme = Theme.of(context).textTheme;
    final reading = snapshot.reading;
    final user = ref.watch(currentUserProvider);

    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 20, 16, 32),
      children: [
        Row(
          children: [
            Text(
              'Hola, ${user?.name.split(' ').first ?? 'Javier'}',
              style: textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.w900),
            ),
            const SizedBox(width: 6),
            const Text('👋', style: TextStyle(fontSize: 22)),
          ],
        ),
        const SizedBox(height: 16),
        // Selector de invernadero (simulado como dropdown en mockup)
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: Colors.grey.withOpacity(0.2)),
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                snapshot.greenhouse.name,
                style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 16),
              ),
              const Icon(Icons.keyboard_arrow_down),
            ],
          ),
        ),
        const SizedBox(height: 24),
        _OverallCard(snapshot: snapshot),
        const SizedBox(height: 24),
        const Text(
          'Variables en tiempo real',
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
        ),
        const SizedBox(height: 12),
        if (reading == null)
          const _NoReadingCard()
        else
          _MetricGrid(reading: reading, statuses: snapshot.statuses),
        const SizedBox(height: 24),
        const Text(
          'Resumen rápido',
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
        ),
        const SizedBox(height: 12),
        _IrrigationSummary(snapshot: snapshot),
        const SizedBox(height: 16),
        _RecentAlerts(alerts: snapshot.recentAlerts),
      ],
    );
  }
}

class _OverallCard extends StatelessWidget {
  const _OverallCard({required this.snapshot});

  final DashboardSnapshot snapshot;

  @override
  Widget build(BuildContext context) {
    final overall = snapshot.statuses.overall;
    final color = AppColors.forStatus(overall);

    final headline = switch (overall) {
      'normal' => 'Óptimo',
      'warning' => 'Alerta',
      'critical' => 'Crítico',
      _ => 'Sin datos',
    };

    final detail = switch (overall) {
      'normal' => 'Todo en condiciones ideales',
      'warning' => 'Valores fuera de rango',
      'critical' => 'Requiere atención inmediata',
      _ => 'Esperando lecturas...',
    };

    return AppCard(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Estado actual',
                    style: TextStyle(fontSize: 13, color: Colors.grey),
                  ),
                  Text(
                    headline,
                    style: TextStyle(
                      fontSize: 24,
                      fontWeight: FontWeight.w900,
                      color: color,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    detail,
                    style: TextStyle(fontSize: 13, color: Colors.grey.shade600),
                  ),
                ],
              ),
            ),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: color.withOpacity(0.1),
                shape: BoxShape.circle,
              ),
              child: Icon(
                Icons.eco,
                color: color,
                size: 32,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _NoReadingCard extends StatelessWidget {
  const _NoReadingCard();

  @override
  Widget build(BuildContext context) {
    return AppCard(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Row(
          children: [
            Icon(
              Icons.sensors_off_outlined,
              color: AppColors.slate.withOpacity(0.4),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                'Aún no hay lecturas de sensores para este invernadero.',
                style: Theme.of(context).textTheme.bodyMedium,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _MetricGrid extends StatelessWidget {
  const _MetricGrid({required this.reading, required this.statuses});

  final TelemetryReading reading;
  final MetricStatuses statuses;

  @override
  Widget build(BuildContext context) {
    return GridView.count(
      crossAxisCount: 2,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      mainAxisSpacing: 12,
      crossAxisSpacing: 12,
      childAspectRatio: 1.45,
      children: [
        for (final key in MetricStatuses.metricKeys)
          _MetricTile(
            metric: key,
            value: reading.metric(key),
            status: statuses.of(key),
          ),
      ],
    );
  }
}

class _MetricTile extends StatelessWidget {
  const _MetricTile({
    required this.metric,
    required this.value,
    required this.status,
  });

  final String metric;
  final double value;
  final String status;

  @override
  Widget build(BuildContext context) {
    final color = AppColors.forStatus(status);
    final unit = TelemetryReading.unitFor(metric);

    return AppCard(
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(
                  switch (metric) {
                    'temperature' => Icons.thermostat_outlined,
                    'soil_humidity' => Icons.grass_outlined,
                    'ambient_humidity' => Icons.water_drop_outlined,
                    'luminosity' => Icons.light_mode_outlined,
                    'water_level' => Icons.opacity_outlined,
                    _ => Icons.sensors,
                  },
                  size: 18,
                  color: color,
                ),
                const Spacer(),
                Container(
                  width: 8,
                  height: 8,
                  decoration: BoxDecoration(
                    color: color,
                    shape: BoxShape.circle,
                  ),
                ),
              ],
            ),
            const Spacer(),
            Text(
              Fmt.metric(value, unit),
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                    fontWeight: FontWeight.w700,
                    letterSpacing: -0.5,
                  ),
            ),
            const SizedBox(height: 2),
            Text(
              AppLabels.metric(metric),
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

class _IrrigationSummary extends StatelessWidget {
  const _IrrigationSummary({required this.snapshot});

  final DashboardSnapshot snapshot;

  @override
  Widget build(BuildContext context) {
    final active = snapshot.activeIrrigation;
    final next = snapshot.nextSchedule;
    final automatic = snapshot.greenhouse.automaticIrrigation;

    return AppCard(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                const Icon(Icons.water_drop, color: AppColors.water, size: 20),
                const SizedBox(width: 8),
                Text(
                  'Riego',
                  style: Theme.of(context).textTheme.titleSmall?.copyWith(
                        fontWeight: FontWeight.w700,
                      ),
                ),
                const Spacer(),
                StatusPill(
                  label: automatic ? 'Automático' : 'Manual',
                  color: automatic ? AppColors.leaf : AppColors.soil,
                  compact: true,
                ),
              ],
            ),
            const SizedBox(height: 12),
            if (active != null)
              Row(
                children: [
                  const SizedBox(
                    width: 16,
                    height: 16,
                    child: CircularProgressIndicator(
                      strokeWidth: 2,
                      color: AppColors.water,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      'Regando desde ${Fmt.time(active.startedAt)} · ${active.durationMinutes ?? 0} min programados',
                      style: Theme.of(context).textTheme.bodyMedium,
                    ),
                  ),
                ],
              )
            else
              Text(
                'No hay riego en curso.',
                style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                      color: AppColors.slate.withOpacity(0.65),
                    ),
              ),
            if (next != null) ...[
              const SizedBox(height: 8),
              Text(
                'Próximo horario: ${next.displayTime} · ${next.durationMinutes} min',
                style: TextStyle(
                  fontSize: 13,
                  color: AppColors.slate.withOpacity(0.6),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

class _RecentAlerts extends StatelessWidget {
  const _RecentAlerts({required this.alerts});

  final List<Alert> alerts;

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
                const Icon(
                  Icons.notifications_none,
                  size: 20,
                  color: AppColors.canopy,
                ),
                const SizedBox(width: 8),
                Text(
                  'Alertas recientes',
                  style: Theme.of(context).textTheme.titleSmall?.copyWith(
                        fontWeight: FontWeight.w700,
                      ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            if (alerts.isEmpty)
              Text(
                'Sin alertas registradas.',
                style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                      color: AppColors.slate.withOpacity(0.65),
                    ),
              )
            else
              for (final alert in alerts) ...[
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      margin: const EdgeInsets.only(top: 5),
                      width: 8,
                      height: 8,
                      decoration: BoxDecoration(
                        color: AppColors.forSeverity(alert.severity),
                        shape: BoxShape.circle,
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            alert.title,
                            style: const TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          Text(
                            '${AppLabels.alertStatus(alert.status)} · ${Fmt.relative(alert.lastTriggeredAt)}',
                            style: TextStyle(
                              fontSize: 12,
                              color: AppColors.slate.withOpacity(0.55),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                if (alert != alerts.last) const SizedBox(height: 12),
              ],
          ],
        ),
      ),
    );
  }
}
