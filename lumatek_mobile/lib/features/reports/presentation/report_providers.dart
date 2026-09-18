import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../auth/presentation/auth_controller.dart';
import '../data/report_repository.dart';
import '../domain/report_summary.dart';

/// Ventana de días del reporte. La web usa filtros equivalentes.
final reportDaysProvider = StateProvider<int>((ref) => 7);

final reportSummaryProvider =
    FutureProvider.autoDispose<ReportSummary>((ref) async {
  final greenhouseId = ref.watch(selectedGreenhouseIdProvider);
  if (greenhouseId == null) {
    throw StateError('No hay invernadero seleccionado.');
  }
  final days = ref.watch(reportDaysProvider);
  return ref.watch(reportRepositoryProvider).summary(greenhouseId, days: days);
});
