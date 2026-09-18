import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../auth/presentation/auth_controller.dart';
import '../data/dashboard_repository.dart';
import '../domain/dashboard_snapshot.dart';

/// Panel del invernadero activo. Al cambiar de invernadero el provider se
/// reconstruye solo, porque depende de selectedGreenhouseIdProvider.
final dashboardProvider = FutureProvider.autoDispose<DashboardSnapshot>((ref) async {
  final greenhouseId = ref.watch(selectedGreenhouseIdProvider);
  if (greenhouseId == null) {
    throw StateError('No hay invernadero seleccionado.');
  }
  return ref.watch(dashboardRepositoryProvider).snapshot(greenhouseId);
});
