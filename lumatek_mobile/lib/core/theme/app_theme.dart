import 'package:flutter/material.dart';

/// Paleta tomada del propio dominio: invernadero, suelo y agua.
/// Los tres estados que devuelve TelemetryStatusService (normal / warning /
/// critical) tienen color propio y se usan igual en todas las pantallas, para
/// que el operador aprenda una sola convención.
class AppColors {
  const AppColors._();

  static const canopy = Color(0xFF17472F); // verde profundo de follaje
  static const leaf = Color(0xFF3E8E5A); // verde de hoja joven
  static const soil = Color(0xFF6B4F2A); // tierra húmeda
  static const water = Color(0xFF2B7FA8); // azul de riego
  static const sun = Color(0xFFD9A441); // luz / advertencia
  static const ember = Color(0xFFB3402B); // crítico
  static const mist = Color(0xFFF2F4F0); // fondo claro
  static const slate = Color(0xFF1C211D); // texto principal

  /// Color asociado a un estado devuelto por el backend.
  static Color forStatus(String? status) => switch (status) {
        'normal' => leaf,
        'warning' => sun,
        'critical' => ember,
        _ => Colors.grey,
      };

  /// Color asociado a la severidad de una alerta.
  static Color forSeverity(String? severity) => switch (severity) {
        'critical' => ember,
        'warning' => sun,
        'info' => water,
        _ => Colors.grey,
      };
}

/// Etiquetas en español para los valores que el backend envía en inglés.
class AppLabels {
  const AppLabels._();

  static String status(String? value) => switch (value) {
        'normal' => 'Normal',
        'warning' => 'Atención',
        'critical' => 'Crítico',
        'unknown' => 'Sin datos',
        _ => '—',
      };

  static String severity(String? value) => switch (value) {
        'critical' => 'Crítica',
        'warning' => 'Advertencia',
        'info' => 'Informativa',
        _ => '—',
      };

  static String alertStatus(String? value) => switch (value) {
        'new' => 'Nueva',
        'viewed' => 'Vista',
        'resolved' => 'Resuelta',
        _ => '—',
      };

  static String metric(String key) => switch (key) {
        'temperature' => 'Temperatura',
        'soil_humidity' => 'Humedad del suelo',
        'ambient_humidity' => 'Humedad ambiental',
        'luminosity' => 'Luminosidad',
        'water_level' => 'Nivel de agua',
        'overall' => 'Estado general',
        _ => key,
      };

  static String day(String key) => switch (key) {
        'monday' => 'Lunes',
        'tuesday' => 'Martes',
        'wednesday' => 'Miércoles',
        'thursday' => 'Jueves',
        'friday' => 'Viernes',
        'saturday' => 'Sábado',
        'sunday' => 'Domingo',
        _ => key,
      };

  static String dayShort(String key) => switch (key) {
        'monday' => 'Lun',
        'tuesday' => 'Mar',
        'wednesday' => 'Mié',
        'thursday' => 'Jue',
        'friday' => 'Vie',
        'saturday' => 'Sáb',
        'sunday' => 'Dom',
        _ => key,
      };

  static const weekDays = [
    'monday',
    'tuesday',
    'wednesday',
    'thursday',
    'friday',
    'saturday',
    'sunday',
  ];

  static String deviceStatus(String? value) => switch (value) {
        'connected' => 'Conectado',
        'disconnected' => 'Desconectado',
        'error' => 'Con fallo',
        _ => '—',
      };

  static String irrigationEventStatus(String? value) => switch (value) {
        'running' => 'En curso',
        'completed' => 'Completado',
        'cancelled' => 'Cancelado',
        _ => value ?? '—',
      };

  static String irrigationType(String? value) => switch (value) {
        'manual' => 'Manual',
        'automatic' => 'Automático',
        'scheduled' => 'Programado',
        _ => value ?? '—',
      };

  static String greenhouseStatus(String? value) => switch (value) {
        'active' => 'Activo',
        'inactive' => 'Inactivo',
        'maintenance' => 'Mantenimiento',
        _ => value ?? '—',
      };
}

class AppTheme {
  const AppTheme._();

  /// Se configuran solo colorScheme y fondo. Los temas por componente
  /// (CardTheme, AppBarTheme, InputDecorationTheme...) cambiaron de tipo
  /// entre versiones de Flutter, así que el estilo de cada componente vive
  /// en el widget que lo usa: AppCard, los AppBar y los campos del login.
  static ThemeData light() {
    return ThemeData(
      useMaterial3: true,
      colorScheme: ColorScheme.fromSeed(
        seedColor: AppColors.canopy,
        primary: AppColors.canopy,
        secondary: AppColors.leaf,
        surface: Colors.white,
        error: AppColors.ember,
      ),
      scaffoldBackgroundColor: AppColors.mist,
    );
  }
}

/// Estilos reutilizables que antes vivían en los temas por componente.
class AppStyles {
  const AppStyles._();

  static InputDecoration field({
    required String label,
    IconData? prefixIcon,
    Widget? suffixIcon,
    String? errorText,
  }) {
    final border = OutlineInputBorder(
      borderRadius: BorderRadius.circular(12),
      borderSide: BorderSide(color: Colors.black.withOpacity(0.12)),
    );

    return InputDecoration(
      labelText: label,
      errorText: errorText,
      prefixIcon: prefixIcon == null ? null : Icon(prefixIcon),
      suffixIcon: suffixIcon,
      filled: true,
      fillColor: Colors.white,
      border: border,
      enabledBorder: border,
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: AppColors.canopy, width: 1.6),
      ),
    );
  }

  static ButtonStyle primaryButton({Color? background}) {
    return FilledButton.styleFrom(
      backgroundColor: background,
      minimumSize: const Size.fromHeight(50),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      textStyle: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
    );
  }
}
