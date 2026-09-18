import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_client.dart';
import '../../../shared/models/json_utils.dart';
import '../../../shared/models/paginated.dart';
import '../domain/alert.dart';

class AlertRepository {
  AlertRepository(this._client);

  final ApiClient _client;

  /// GET /api/mobile/greenhouses/{id}/alerts
  /// severity: critical | warning | info. status: new | viewed | resolved.
  Future<Paginated<Alert>> index(
    int greenhouseId, {
    String? severity,
    String? status,
    int page = 1,
    int perPage = 15,
  }) async {
    final json = await _client.get(
      '/greenhouses/$greenhouseId/alerts',
      query: {
        'page': page,
        'per_page': perPage,
        if (severity != null) 'severity': severity,
        if (status != null) 'status': status,
      },
    );
    return Paginated.fromJson(json, Alert.fromJson);
  }

  /// PATCH /api/mobile/alerts/{id}/viewed
  Future<Alert> markViewed(int alertId) async {
    final json = await _client.patch('/alerts/$alertId/viewed');
    return Alert.fromJson(asMap(json['alert']));
  }

  /// PATCH /api/mobile/alerts/{id}/resolve
  Future<Alert> resolve(int alertId) async {
    final json = await _client.patch('/alerts/$alertId/resolve');
    return Alert.fromJson(asMap(json['alert']));
  }
}

final alertRepositoryProvider = Provider<AlertRepository>((ref) {
  return AlertRepository(ref.watch(apiClientProvider));
});
