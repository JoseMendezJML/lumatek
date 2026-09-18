import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/format.dart';
import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/app_card.dart';
import '../../../core/widgets/async_view.dart';
import '../../../core/widgets/status_pill.dart';
import '../../auth/presentation/auth_controller.dart';
import '../../dashboard/presentation/dashboard_providers.dart';
import '../domain/irrigation_event.dart';
import '../domain/irrigation_schedule.dart';
import '../domain/irrigation_status.dart';
import 'irrigation_providers.dart';

class IrrigationScreen extends ConsumerStatefulWidget {
  const IrrigationScreen({super.key});

  @override
  ConsumerState<IrrigationScreen> createState() => _IrrigationScreenState();
}

class _IrrigationScreenState extends ConsumerState<IrrigationScreen> {
  Timer? _ticker;

  @override
  void initState() {
    super.initState();
    // Refresca la cuenta regresiva del riego en curso.
    _ticker = Timer.periodic(const Duration(seconds: 1), (_) {
      if (mounted) setState(() {});
    });
  }

  @override
  void dispose() {
    _ticker?.cancel();
    super.dispose();
  }

  void _notify(String message, {bool isError = false}) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: isError ? AppColors.ember : null,
      ),
    );
  }

  Future<void> _guard(Future<void> Function() action) async {
    try {
      await action();
    } on ApiException catch (error) {
      _notify(error.message, isError: true);
    } catch (_) {
      _notify('No se pudo completar la acción.', isError: true);
    }
  }

  @override
  Widget build(BuildContext context) {
    final status = ref.watch(irrigationStatusProvider);
    final schedules = ref.watch(irrigationSchedulesProvider);
    final history = ref.watch(irrigationHistoryProvider);

    return RefreshIndicator(
      onRefresh: () async {
        ref.invalidate(irrigationStatusProvider);
        ref.invalidate(irrigationSchedulesProvider);
        ref.invalidate(irrigationHistoryProvider);
      },
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
        children: [
          AsyncView(
            value: status,
            onRetry: () => ref.invalidate(irrigationStatusProvider),
            loading: const SizedBox(
              height: 200,
              child: Center(child: CircularProgressIndicator()),
            ),
            data: (data) => _ControlCard(
              status: data,
              onStart: (minutes) => _guard(() async {
                await ref.read(irrigationActionsProvider).start(minutes);
                ref.invalidate(dashboardProvider);
                _notify('Riego iniciado por $minutes minutos.');
              }),
              onStop: () => _guard(() async {
                final event = await ref.read(irrigationActionsProvider).stop();
                ref.invalidate(dashboardProvider);
                _notify(
                  event != null ? 'Riego detenido.' : 'No había un riego activo.',
                );
              }),
              onToggleAutomatic: () => _guard(() async {
                final enabled =
                    await ref.read(irrigationActionsProvider).toggleAutomatic();
                ref.invalidate(dashboardProvider);
                await ref
                    .read(authControllerProvider.notifier)
                    .refreshGreenhouses();
                _notify(
                  enabled
                      ? 'Riego automático activado.'
                      : 'Riego automático desactivado.',
                );
              }),
            ),
          ),
          const SizedBox(height: 24),
          Row(
            children: [
              Expanded(
                child: Text(
                  'Horarios programados',
                  style: Theme.of(context)
                      .textTheme
                      .titleMedium
                      ?.copyWith(fontWeight: FontWeight.w700),
                ),
              ),
              TextButton.icon(
                onPressed: () => _openScheduleForm(context),
                icon: const Icon(Icons.add, size: 18),
                label: const Text('Agregar'),
              ),
            ],
          ),
          const SizedBox(height: 4),
          AsyncView(
            value: schedules,
            onRetry: () => ref.invalidate(irrigationSchedulesProvider),
            loading: const Padding(
              padding: EdgeInsets.all(24),
              child: Center(child: CircularProgressIndicator()),
            ),
            data: (items) {
              if (items.isEmpty) {
                return AppCard(
                  child: Padding(
                    padding: const EdgeInsets.all(20),
                    child: Text(
                      'No hay horarios. Agrega uno para que el riego corra solo.',
                      style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                            color: AppColors.slate.withOpacity(0.65),
                          ),
                    ),
                  ),
                );
              }

              return Column(
                children: [
                  for (final schedule in items)
                    _ScheduleTile(
                      schedule: schedule,
                      onToggle: () => _guard(() async {
                        await ref
                            .read(irrigationActionsProvider)
                            .toggleSchedule(schedule.id);
                        _notify(
                          schedule.active
                              ? 'Horario pausado.'
                              : 'Horario activado.',
                        );
                      }),
                      onDelete: () => _confirmDelete(context, schedule),
                    ),
                ],
              );
            },
          ),
          const SizedBox(height: 24),
          Text(
            'Historial de riegos',
            style: Theme.of(context)
                .textTheme
                .titleMedium
                ?.copyWith(fontWeight: FontWeight.w700),
          ),
          const SizedBox(height: 10),
          AsyncView(
            value: history,
            onRetry: () => ref.invalidate(irrigationHistoryProvider),
            loading: const Padding(
              padding: EdgeInsets.all(24),
              child: Center(child: CircularProgressIndicator()),
            ),
            data: (page) {
              if (page.isEmpty) {
                return AppCard(
                  child: Padding(
                    padding: const EdgeInsets.all(20),
                    child: Text(
                      'Todavía no se ha registrado ningún riego.',
                      style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                            color: AppColors.slate.withOpacity(0.65),
                          ),
                    ),
                  ),
                );
              }

              return Column(
                children: [
                  for (final event in page.items) _EventTile(event: event),
                ],
              );
            },
          ),
        ],
      ),
    );
  }

  Future<void> _confirmDelete(
    BuildContext context,
    IrrigationSchedule schedule,
  ) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Eliminar horario'),
        content: Text(
          'El riego de las ${schedule.displayTime} dejará de ejecutarse automáticamente.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            child: const Text('Cancelar'),
          ),
          FilledButton(
            onPressed: () => Navigator.of(context).pop(true),
            style: FilledButton.styleFrom(
              backgroundColor: AppColors.ember,
              minimumSize: const Size(0, 44),
            ),
            child: const Text('Eliminar'),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    await _guard(() async {
      await ref.read(irrigationActionsProvider).deleteSchedule(schedule.id);
      _notify('Horario eliminado.');
    });
  }

  Future<void> _openScheduleForm(BuildContext context) async {
    final result = await showModalBottomSheet<_ScheduleDraft>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => const _ScheduleFormSheet(),
    );

    if (result == null) return;

    await _guard(() async {
      await ref.read(irrigationActionsProvider).createSchedule(
            time: result.time,
            durationMinutes: result.durationMinutes,
            days: result.days,
          );
      _notify('Horario agregado.');
    });
  }
}

class _ControlCard extends StatefulWidget {
  const _ControlCard({
    required this.status,
    required this.onStart,
    required this.onStop,
    required this.onToggleAutomatic,
  });

  final IrrigationStatus status;
  final Future<void> Function(int minutes) onStart;
  final Future<void> Function() onStop;
  final Future<void> Function() onToggleAutomatic;

  @override
  State<_ControlCard> createState() => _ControlCardState();
}

class _ControlCardState extends State<_ControlCard> {
  bool _busy = false;

  Future<void> _wrap(Future<void> Function() action) async {
    setState(() => _busy = true);
    await action();
    if (mounted) setState(() => _busy = false);
  }

  @override
  Widget build(BuildContext context) {
    final status = widget.status;
    final event = status.event;
    final isRunning = status.active && event != null;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        // Riego Automático Switch
        AppCard(
          child: SwitchListTile(
            value: status.automaticIrrigation,
            onChanged: _busy ? null : (_) => _wrap(widget.onToggleAutomatic),
            title: const Text('Riego automático', style: TextStyle(fontWeight: FontWeight.w600)),
            activeColor: AppColors.leaf,
          ),
        ),
        const SizedBox(height: 16),

        // Humedad del Suelo Card
        AppCard(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Humedad del suelo actual', style: TextStyle(color: Colors.grey)),
                      const Text(
                        '45 %', // Valor simulado para el mockup
                        style: TextStyle(fontSize: 32, fontWeight: FontWeight.w900),
                      ),
                      const Text('Ideal 40%-60%', style: TextStyle(color: Colors.grey, fontSize: 12)),
                    ],
                  ),
                ),
                const Icon(Icons.water_drop_outlined, size: 48, color: Colors.blueAccent),
              ],
            ),
          ),
        ),
        const SizedBox(height: 16),

        // Estado del Riego Card
        AppCard(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Estado del riego', style: TextStyle(color: Colors.grey)),
                      Text(
                        isRunning ? 'Regando' : 'Detenido',
                        style: TextStyle(
                          fontSize: 24,
                          fontWeight: FontWeight.w800,
                          color: isRunning ? AppColors.leaf : AppColors.slate,
                        ),
                      ),
                      Text(
                        isRunning ? 'Iniciado hoy 05:30 AM' : 'Sin actividad',
                        style: const TextStyle(color: Colors.grey, fontSize: 12),
                      ),
                    ],
                  ),
                ),
                Icon(Icons.eco, size: 48, color: isRunning ? AppColors.leaf : Colors.grey.shade300),
              ],
            ),
          ),
        ),
        const SizedBox(height: 20),

        // Botón Regar Ahora
        FilledButton(
          onPressed: _busy ? null : () => _wrap(() => isRunning ? widget.onStop() : widget.onStart(15)),
          style: AppStyles.primaryButton(background: AppColors.leaf),
          child: Text(isRunning ? 'Detener riego' : 'Regar ahora'),
        ),
        const SizedBox(height: 32),

        // Resumen Rápido
        const Text('Resumen rápido', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
        const SizedBox(height: 12),
        AppCard(
          child: Column(
            children: [
              const ListTile(
                leading: Icon(Icons.alarm),
                title: Text('Próximo riego', style: TextStyle(fontSize: 13, color: Colors.grey)),
                subtitle: Text('Mañana 06:00 AM', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 16, color: Colors.black)),
              ),
              const Divider(height: 1),
              const ListTile(
                leading: Icon(Icons.timer_outlined),
                title: Text('Duración estimada', style: TextStyle(fontSize: 13, color: Colors.grey)),
                subtitle: Text('25 min', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 16, color: Colors.black)),
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class _ScheduleTile extends StatelessWidget {
  const _ScheduleTile({
    required this.schedule,
    required this.onToggle,
    required this.onDelete,
  });

  final IrrigationSchedule schedule;
  final VoidCallback onToggle;
  final VoidCallback onDelete;

  @override
  Widget build(BuildContext context) {
    final daysLabel = schedule.isEveryDay
        ? 'Todos los días'
        : schedule.days.map(AppLabels.dayShort).join(' · ');

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.black.withOpacity(0.06)),
      ),
      child: Row(
        children: [
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                schedule.displayTime,
                style: TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.w700,
                  color: schedule.active
                      ? AppColors.slate
                      : AppColors.slate.withOpacity(0.4),
                ),
              ),
              Text(
                '${schedule.durationMinutes} min',
                style: TextStyle(
                  fontSize: 12,
                  color: AppColors.slate.withOpacity(0.55),
                ),
              ),
            ],
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Text(
              daysLabel,
              style: TextStyle(
                fontSize: 13,
                color: AppColors.slate.withOpacity(0.7),
              ),
            ),
          ),
          Switch(
            value: schedule.active,
            activeColor: AppColors.leaf,
            onChanged: (_) => onToggle(),
          ),
          IconButton(
            onPressed: onDelete,
            icon: const Icon(Icons.delete_outline),
            color: AppColors.slate.withOpacity(0.45),
            tooltip: 'Eliminar horario',
          ),
        ],
      ),
    );
  }
}

class _EventTile extends StatelessWidget {
  const _EventTile({required this.event});

  final IrrigationEvent event;

  @override
  Widget build(BuildContext context) {
    final gain = event.humidityGain;

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
          Container(
            width: 36,
            height: 36,
            decoration: BoxDecoration(
              color: AppColors.water.withOpacity(0.10),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(
              event.isRunning ? Icons.autorenew : Icons.check,
              size: 18,
              color: AppColors.water,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  Fmt.shortDateTime(event.startedAt),
                  style: const TextStyle(
                    fontSize: 13.5,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                Text(
                  '${AppLabels.irrigationType(event.type)} · ${AppLabels.irrigationEventStatus(event.status)}',
                  style: TextStyle(
                    fontSize: 12,
                    color: AppColors.slate.withOpacity(0.55),
                  ),
                ),
              ],
            ),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(
                '${event.durationMinutes ?? 0} min',
                style: const TextStyle(
                  fontSize: 13.5,
                  fontWeight: FontWeight.w600,
                ),
              ),
              if (gain != null)
                Text(
                  '${gain >= 0 ? '+' : ''}${Fmt.number(gain)} % suelo',
                  style: TextStyle(
                    fontSize: 11.5,
                    color: gain >= 0
                        ? AppColors.leaf
                        : AppColors.slate.withOpacity(0.5),
                  ),
                ),
            ],
          ),
        ],
      ),
    );
  }
}

class _ScheduleDraft {
  const _ScheduleDraft({
    required this.time,
    required this.durationMinutes,
    required this.days,
  });

  final String time;
  final int durationMinutes;
  final List<String> days;
}

class _ScheduleFormSheet extends StatefulWidget {
  const _ScheduleFormSheet();

  @override
  State<_ScheduleFormSheet> createState() => _ScheduleFormSheetState();
}

class _ScheduleFormSheetState extends State<_ScheduleFormSheet> {
  TimeOfDay _time = const TimeOfDay(hour: 6, minute: 0);
  double _minutes = 15;
  final Set<String> _days = {};

  String get _formattedTime =>
      '${_time.hour.toString().padLeft(2, '0')}:${_time.minute.toString().padLeft(2, '0')}';

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        color: AppColors.mist,
        borderRadius: BorderRadius.vertical(top: Radius.circular(22)),
      ),
      padding: EdgeInsets.fromLTRB(
        20,
        18,
        20,
        MediaQuery.of(context).viewInsets.bottom + 24,
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Center(
            child: Container(
              width: 40,
              height: 4,
              decoration: BoxDecoration(
                color: Colors.black.withOpacity(0.12),
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ),
          const SizedBox(height: 18),
          Text(
            'Nuevo horario de riego',
            style: Theme.of(context)
                .textTheme
                .titleLarge
                ?.copyWith(fontWeight: FontWeight.w700),
          ),
          const SizedBox(height: 18),
          ListTile(
            contentPadding: EdgeInsets.zero,
            onTap: () async {
              final picked = await showTimePicker(
                context: context,
                initialTime: _time,
              );
              if (picked != null) setState(() => _time = picked);
            },
            leading: const Icon(Icons.schedule),
            title: const Text('Hora de inicio'),
            trailing: Text(
              _formattedTime,
              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w700),
            ),
          ),
          const Divider(),
          const SizedBox(height: 8),
          Row(
            children: [
              const Text('Duración'),
              const Spacer(),
              Text(
                '${_minutes.round()} min',
                style: const TextStyle(fontWeight: FontWeight.w700),
              ),
            ],
          ),
          Slider(
            value: _minutes,
            min: 1,
            max: 180,
            divisions: 179,
            activeColor: AppColors.water,
            onChanged: (value) => setState(() => _minutes = value),
          ),
          const SizedBox(height: 8),
          const Text('Días'),
          const SizedBox(height: 8),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              for (final day in AppLabels.weekDays)
                FilterChip(
                  label: Text(AppLabels.dayShort(day)),
                  selected: _days.contains(day),
                  onSelected: (selected) => setState(() {
                    selected ? _days.add(day) : _days.remove(day);
                  }),
                ),
            ],
          ),
          const SizedBox(height: 6),
          Text(
            _days.isEmpty
                ? 'Sin días seleccionados se ejecuta todos los días.'
                : 'Se ejecutará solo los días marcados.',
            style: TextStyle(
              fontSize: 12.5,
              color: AppColors.slate.withOpacity(0.6),
            ),
          ),
          const SizedBox(height: 20),
          FilledButton(
            style: AppStyles.primaryButton(),
            onPressed: () => Navigator.of(context).pop(
              _ScheduleDraft(
                time: _formattedTime,
                durationMinutes: _minutes.round(),
                days: _days.toList(),
              ),
            ),
            child: const Text('Guardar horario'),
          ),
        ],
      ),
    );
  }
}
