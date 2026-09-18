import 'package:flutter/material.dart';

import '../theme/app_theme.dart';

/// Etiqueta de estado. Se usa con los tres estados de telemetría y con las
/// severidades de alerta, siempre con la misma forma para que el operador
/// lea el color igual en todas las pantallas.
class StatusPill extends StatelessWidget {
  const StatusPill({
    super.key,
    required this.label,
    required this.color,
    this.icon,
    this.compact = false,
  });

  StatusPill.forStatus(String? status, {super.key, this.compact = false})
      : label = AppLabels.status(status),
        color = AppColors.forStatus(status),
        icon = switch (status) {
          'critical' => Icons.error_outline,
          'warning' => Icons.warning_amber_rounded,
          'normal' => Icons.check_circle_outline,
          _ => Icons.help_outline,
        };

  StatusPill.forSeverity(String? severity, {super.key, this.compact = false})
      : label = AppLabels.severity(severity),
        color = AppColors.forSeverity(severity),
        icon = switch (severity) {
          'critical' => Icons.error_outline,
          'warning' => Icons.warning_amber_rounded,
          _ => Icons.info_outline,
        };

  final String label;
  final Color color;
  final IconData? icon;
  final bool compact;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.symmetric(
        horizontal: compact ? 8 : 10,
        vertical: compact ? 3 : 5,
      ),
      decoration: BoxDecoration(
        color: color.withOpacity(0.12),
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: color.withOpacity(0.35)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (icon != null) ...[
            Icon(icon, size: compact ? 12 : 14, color: color),
            const SizedBox(width: 5),
          ],
          Text(
            label,
            style: TextStyle(
              color: color,
              fontSize: compact ? 11 : 12.5,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }
}
