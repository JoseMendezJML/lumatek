import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_client.dart';
import '../../../shared/models/json_utils.dart';
import '../../../shared/models/paginated.dart';
import '../domain/irrigation_event.dart';
import '../domain/irrigation_schedule.dart';
import '../domain/irrigation_status.dart';

class IrrigationRepository {
  IrrigationRepository(this._client);

  final ApiClient _client;

  /// GET /api/mobile/greenhouses/{id}/irrigation/status
  Future<IrrigationStatus> status(int greenhouseId) async {
    final json = await _client.get('/greenhouses/$greenhouseId/irrigation/status');
    return IrrigationStatus.fromJson(json);
  }

  /// POST /api/mobile/greenhouses/{id}/irrigation/start
  /// duration_minutes debe estar entre 1 y 180 (validación del backend).
  Future<IrrigationEvent> start(int greenhouseId, int durationMinutes) async {
    final json = await _client.post(
      '/greenhouses/$greenhouseId/irrigation/start',
      body: {'duration_minutes': durationMinutes},
    );
    return IrrigationEvent.fromJson(asMap(json['event']));
  }

  /// POST /api/mobile/greenhouses/{id}/irrigation/stop
  /// Devuelve null si no había riego activo.
  Future<IrrigationEvent?> stop(int greenhouseId) async {
    final json = await _client.post('/greenhouses/$greenhouseId/irrigation/stop');
    final rawEvent = json['event'];
    return rawEvent is Map ? IrrigationEvent.fromJson(asMap(rawEvent)) : null;
  }

  /// GET /api/mobile/greenhouses/{id}/irrigation/history
  Future<Paginated<IrrigationEvent>> history(
    int greenhouseId, {
    int page = 1,
    int perPage = 15,
  }) async {
    final json = await _client.get(
      '/greenhouses/$greenhouseId/irrigation/history',
      query: {'page': page, 'per_page': perPage},
    );
    return Paginated.fromJson(json, IrrigationEvent.fromJson);
  }

  /// PATCH /api/mobile/greenhouses/{id}/irrigation/automatic
  /// Alterna el valor; devuelve el nuevo estado.
  Future<bool> toggleAutomatic(int greenhouseId) async {
    final json = await _client.patch(
      '/greenhouses/$greenhouseId/irrigation/automatic',
    );
    return asBool(json['automatic_irrigation']);
  }

  /// GET /api/mobile/greenhouses/{id}/irrigation/schedules
  Future<List<IrrigationSchedule>> schedules(int greenhouseId) async {
    final json = await _client.get(
      '/greenhouses/$greenhouseId/irrigation/schedules',
    );
    final raw = json['schedules'];
    if (raw is! List) return const [];
    return raw.map((item) => IrrigationSchedule.fromJson(asMap(item))).toList();
  }

  /// POST /api/mobile/greenhouses/{id}/irrigation/schedules
  /// [time] en formato HH:mm; [days] en inglés (monday..sunday), vacío = diario.
  Future<IrrigationSchedule> createSchedule(
    int greenhouseId, {
    required String time,
    required int durationMinutes,
    List<String> days = const [],
  }) async {
    final json = await _client.post(
      '/greenhouses/$greenhouseId/irrigation/schedules',
      body: {
        'time': time,
        'duration_minutes': durationMinutes,
        'days': days,
      },
    );
    return IrrigationSchedule.fromJson(asMap(json['schedule']));
  }

  /// PATCH /api/mobile/irrigation/schedules/{id} — alterna activo/pausado.
  Future<IrrigationSchedule> toggleSchedule(int scheduleId) async {
    final json = await _client.patch('/irrigation/schedules/$scheduleId');
    return IrrigationSchedule.fromJson(asMap(json['schedule']));
  }

  /// DELETE /api/mobile/irrigation/schedules/{id}
  Future<void> deleteSchedule(int scheduleId) async {
    await _client.delete('/irrigation/schedules/$scheduleId');
  }
}

final irrigationRepositoryProvider = Provider<IrrigationRepository>((ref) {
  return IrrigationRepository(ref.watch(apiClientProvider));
});
