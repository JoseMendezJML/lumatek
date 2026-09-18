import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_client.dart';
import '../../../shared/models/paginated.dart';
import '../../../shared/models/telemetry_reading.dart';
import '../domain/current_telemetry.dart';

class TelemetryRepository {
  TelemetryRepository(this._client);

  final ApiClient _client;

  /// GET /api/mobile/greenhouses/{id}/telemetry/current
  Future<CurrentTelemetry> current(int greenhouseId) async {
    final json = await _client.get('/greenhouses/$greenhouseId/telemetry/current');
    return CurrentTelemetry.fromJson(json);
  }

  /// GET /api/mobile/greenhouses/{id}/telemetry/history
  /// El backend limita per_page entre 5 y 100.
  Future<Paginated<TelemetryReading>> history(
    int greenhouseId, {
    int page = 1,
    int perPage = 25,
  }) async {
    final json = await _client.get(
      '/greenhouses/$greenhouseId/telemetry/history',
      query: {'page': page, 'per_page': perPage},
    );
    return Paginated.fromJson(json, TelemetryReading.fromJson);
  }
}

final telemetryRepositoryProvider = Provider<TelemetryRepository>((ref) {
  return TelemetryRepository(ref.watch(apiClientProvider));
});
