import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../shared/models/paginated.dart';
import '../../../shared/models/telemetry_reading.dart';
import '../../auth/presentation/auth_controller.dart';
import '../data/telemetry_repository.dart';
import '../domain/current_telemetry.dart';

final currentTelemetryProvider =
    FutureProvider.autoDispose<CurrentTelemetry>((ref) async {
  final greenhouseId = ref.watch(selectedGreenhouseIdProvider);
  if (greenhouseId == null) {
    throw StateError('No hay invernadero seleccionado.');
  }
  return ref.watch(telemetryRepositoryProvider).current(greenhouseId);
});

/// Historial paginado con carga incremental.
class TelemetryHistoryController
    extends StateNotifier<AsyncValue<Paginated<TelemetryReading>>> {
  TelemetryHistoryController(this._ref, this._greenhouseId)
      : super(const AsyncValue.loading()) {
    load();
  }

  final Ref _ref;
  final int _greenhouseId;
  bool _loadingMore = false;

  Future<void> load() async {
    state = const AsyncValue.loading();
    try {
      final page = await _ref
          .read(telemetryRepositoryProvider)
          .history(_greenhouseId, page: 1);
      if (mounted) state = AsyncValue.data(page);
    } catch (error, stack) {
      if (mounted) state = AsyncValue.error(error, stack);
    }
  }

  Future<void> loadMore() async {
    final current = state.valueOrNull;
    if (current == null || !current.hasMore || _loadingMore) return;

    _loadingMore = true;
    try {
      final next = await _ref
          .read(telemetryRepositoryProvider)
          .history(_greenhouseId, page: current.currentPage + 1);
      if (mounted) state = AsyncValue.data(current.merge(next));
    } catch (_) {
      // Se conserva lo ya cargado; el usuario puede reintentar bajando otra vez.
    } finally {
      _loadingMore = false;
    }
  }
}

final telemetryHistoryProvider = StateNotifierProvider.autoDispose<
    TelemetryHistoryController, AsyncValue<Paginated<TelemetryReading>>>((ref) {
  final greenhouseId = ref.watch(selectedGreenhouseIdProvider);
  return TelemetryHistoryController(ref, greenhouseId ?? 0);
});
