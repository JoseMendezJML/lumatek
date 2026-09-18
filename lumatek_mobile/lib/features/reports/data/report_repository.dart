import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_client.dart';
import '../domain/report_summary.dart';

class ReportRepository {
  ReportRepository(this._client);

  final ApiClient _client;

  /// GET /api/mobile/greenhouses/{id}/reports?days=N (el backend acepta 1..365)
  Future<ReportSummary> summary(int greenhouseId, {int days = 7}) async {
    final json = await _client.get(
      '/greenhouses/$greenhouseId/reports',
      query: {'days': days},
    );
    return ReportSummary.fromJson(json, days);
  }
}

final reportRepositoryProvider = Provider<ReportRepository>((ref) {
  return ReportRepository(ref.watch(apiClientProvider));
});
