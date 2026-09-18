import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../shared/models/paginated.dart';
import '../../auth/presentation/auth_controller.dart';
import '../data/alert_repository.dart';
import '../domain/alert.dart';

@immutable
class AlertFilters {
  const AlertFilters({this.severity, this.status});

  /// null significa "todas".
  final String? severity;
  final String? status;

  bool get isEmpty => severity == null && status == null;

  AlertFilters copyWith({
    String? severity,
    String? status,
    bool clearSeverity = false,
    bool clearStatus = false,
  }) {
    return AlertFilters(
      severity: clearSeverity ? null : (severity ?? this.severity),
      status: clearStatus ? null : (status ?? this.status),
    );
  }

  @override
  bool operator ==(Object other) =>
      other is AlertFilters &&
      other.severity == severity &&
      other.status == status;

  @override
  int get hashCode => Object.hash(severity, status);
}

final alertFiltersProvider = StateProvider<AlertFilters>((ref) {
  return const AlertFilters();
});

class AlertListController extends StateNotifier<AsyncValue<Paginated<Alert>>> {
  AlertListController(this._ref, this._greenhouseId, this._filters)
      : super(const AsyncValue.loading()) {
    load();
  }

  final Ref _ref;
  final int _greenhouseId;
  final AlertFilters _filters;
  bool _loadingMore = false;

  Future<void> load() async {
    state = const AsyncValue.loading();
    try {
      final page = await _ref.read(alertRepositoryProvider).index(
            _greenhouseId,
            severity: _filters.severity,
            status: _filters.status,
          );
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
      final next = await _ref.read(alertRepositoryProvider).index(
            _greenhouseId,
            severity: _filters.severity,
            status: _filters.status,
            page: current.currentPage + 1,
          );
      if (mounted) state = AsyncValue.data(current.merge(next));
    } catch (_) {
      // Se mantiene la lista actual.
    } finally {
      _loadingMore = false;
    }
  }

  /// Marca como vista sin recargar toda la lista. Si el backend falla, el
  /// error sube a la pantalla y esta recarga para volver a la verdad.
  Future<void> markViewed(Alert alert) async {
    if (!alert.isNew) return;
    final updated = await _ref.read(alertRepositoryProvider).markViewed(alert.id);
    _replace(updated);
  }

  Future<void> resolve(Alert alert) async {
    final updated = await _ref.read(alertRepositoryProvider).resolve(alert.id);

    // Si hay filtro por estado y la alerta ya no encaja, se quita de la lista.
    if (_filters.status != null && _filters.status != updated.status) {
      _remove(updated.id);
      return;
    }
    _replace(updated);
  }

  void _replace(Alert updated) {
    final current = state.valueOrNull;
    if (current == null || !mounted) return;

    state = AsyncValue.data(
      Paginated<Alert>(
        items: current.items
            .map((item) => item.id == updated.id ? updated : item)
            .toList(),
        currentPage: current.currentPage,
        lastPage: current.lastPage,
        total: current.total,
        perPage: current.perPage,
      ),
    );
  }

  void _remove(int alertId) {
    final current = state.valueOrNull;
    if (current == null || !mounted) return;

    state = AsyncValue.data(
      Paginated<Alert>(
        items: current.items.where((item) => item.id != alertId).toList(),
        currentPage: current.currentPage,
        lastPage: current.lastPage,
        total: current.total > 0 ? current.total - 1 : 0,
        perPage: current.perPage,
      ),
    );
  }
}

final alertListProvider = StateNotifierProvider.autoDispose<AlertListController,
    AsyncValue<Paginated<Alert>>>((ref) {
  final greenhouseId = ref.watch(selectedGreenhouseIdProvider);
  final filters = ref.watch(alertFiltersProvider);
  return AlertListController(ref, greenhouseId ?? 0, filters);
});
