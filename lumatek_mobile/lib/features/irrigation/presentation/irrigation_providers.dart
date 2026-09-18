import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../shared/models/paginated.dart';
import '../../auth/presentation/auth_controller.dart';
import '../data/irrigation_repository.dart';
import '../domain/irrigation_event.dart';
import '../domain/irrigation_schedule.dart';
import '../domain/irrigation_status.dart';

final irrigationStatusProvider =
    FutureProvider.autoDispose<IrrigationStatus>((ref) async {
  final greenhouseId = ref.watch(selectedGreenhouseIdProvider);
  if (greenhouseId == null) {
    throw StateError('No hay invernadero seleccionado.');
  }
  return ref.watch(irrigationRepositoryProvider).status(greenhouseId);
});

final irrigationSchedulesProvider =
    FutureProvider.autoDispose<List<IrrigationSchedule>>((ref) async {
  final greenhouseId = ref.watch(selectedGreenhouseIdProvider);
  if (greenhouseId == null) return const [];
  return ref.watch(irrigationRepositoryProvider).schedules(greenhouseId);
});

final irrigationHistoryProvider =
    FutureProvider.autoDispose<Paginated<IrrigationEvent>>((ref) async {
  final greenhouseId = ref.watch(selectedGreenhouseIdProvider);
  if (greenhouseId == null) return Paginated.empty<IrrigationEvent>();
  return ref.watch(irrigationRepositoryProvider).history(greenhouseId);
});

/// Acciones del módulo de riego.
///
/// Cada acción invalida los providers afectados para que la pantalla vuelva a
/// leer del servidor: el estado real del riego lo decide IrrigationService en
/// Laravel, no la app.
class IrrigationActions {
  IrrigationActions(this._ref);

  final Ref _ref;

  int get _greenhouseId {
    final id = _ref.read(selectedGreenhouseIdProvider);
    if (id == null) throw StateError('No hay invernadero seleccionado.');
    return id;
  }

  IrrigationRepository get _repository => _ref.read(irrigationRepositoryProvider);

  Future<void> start(int durationMinutes) async {
    await _repository.start(_greenhouseId, durationMinutes);
    _refreshIrrigation();
  }

  Future<IrrigationEvent?> stop() async {
    final event = await _repository.stop(_greenhouseId);
    _refreshIrrigation();
    return event;
  }

  Future<bool> toggleAutomatic() async {
    final enabled = await _repository.toggleAutomatic(_greenhouseId);
    _refreshIrrigation();
    // El panel muestra el riego automático, así que también se refresca.
    return enabled;
  }

  Future<void> createSchedule({
    required String time,
    required int durationMinutes,
    required List<String> days,
  }) async {
    await _repository.createSchedule(
      _greenhouseId,
      time: time,
      durationMinutes: durationMinutes,
      days: days,
    );
    _ref.invalidate(irrigationSchedulesProvider);
  }

  Future<void> toggleSchedule(int scheduleId) async {
    await _repository.toggleSchedule(scheduleId);
    _ref.invalidate(irrigationSchedulesProvider);
  }

  Future<void> deleteSchedule(int scheduleId) async {
    await _repository.deleteSchedule(scheduleId);
    _ref.invalidate(irrigationSchedulesProvider);
  }

  void _refreshIrrigation() {
    _ref.invalidate(irrigationStatusProvider);
    _ref.invalidate(irrigationHistoryProvider);
  }
}

final irrigationActionsProvider = Provider<IrrigationActions>((ref) {
  return IrrigationActions(ref);
});
