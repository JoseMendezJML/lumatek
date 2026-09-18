import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_client.dart';
import '../domain/dashboard_snapshot.dart';

class DashboardRepository {
  DashboardRepository(this._client);

  final ApiClient _client;

  /// GET /api/mobile/greenhouses/{id}/dashboard
  Future<DashboardSnapshot> snapshot(int greenhouseId) async {
    final json = await _client.get('/greenhouses/$greenhouseId/dashboard');
    return DashboardSnapshot.fromJson(json);
  }
}

final dashboardRepositoryProvider = Provider<DashboardRepository>((ref) {
  return DashboardRepository(ref.watch(apiClientProvider));
});
