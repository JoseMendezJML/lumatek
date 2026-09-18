import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_client.dart';
import '../../../shared/models/json_utils.dart';
import '../domain/greenhouse.dart';

class GreenhouseRepository {
  GreenhouseRepository(this._client);

  final ApiClient _client;

  /// GET /api/mobile/greenhouses
  Future<List<Greenhouse>> index() async {
    final json = await _client.get('/greenhouses');
    final raw = json['greenhouses'];
    if (raw is! List) return const [];
    return raw.map((item) => Greenhouse.fromJson(asMap(item))).toList();
  }

  /// GET /api/mobile/greenhouses/{id}
  Future<Greenhouse> show(int greenhouseId) async {
    final json = await _client.get('/greenhouses/$greenhouseId');
    return Greenhouse.fromJson(asMap(json['greenhouse']));
  }
}

final greenhouseRepositoryProvider = Provider<GreenhouseRepository>((ref) {
  return GreenhouseRepository(ref.watch(apiClientProvider));
});
