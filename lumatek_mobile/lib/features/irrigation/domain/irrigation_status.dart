import '../../../shared/models/json_utils.dart';
import 'irrigation_event.dart';

/// Respuesta de GET /mobile/greenhouses/{id}/irrigation/status
class IrrigationStatus {
  const IrrigationStatus({
    required this.active,
    required this.automaticIrrigation,
    this.event,
  });

  final bool active;
  final bool automaticIrrigation;
  final IrrigationEvent? event;

  factory IrrigationStatus.fromJson(Map<String, dynamic> json) {
    final rawEvent = json['event'];
    return IrrigationStatus(
      active: asBool(json['active']),
      automaticIrrigation: asBool(json['automatic_irrigation']),
      event: rawEvent is Map ? IrrigationEvent.fromJson(asMap(rawEvent)) : null,
    );
  }
}
