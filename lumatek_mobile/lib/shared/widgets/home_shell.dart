import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/theme/app_theme.dart';
import '../../features/alerts/presentation/alerts_screen.dart';
import '../../features/auth/presentation/auth_controller.dart';
import '../../features/dashboard/presentation/dashboard_screen.dart';
import '../../features/irrigation/presentation/irrigation_screen.dart';
import '../../features/settings/presentation/settings_screen.dart';

/// Contenedor principal con navegación inferior.
class HomeShell extends ConsumerStatefulWidget {
  const HomeShell({super.key});

  @override
  ConsumerState<HomeShell> createState() => _HomeShellState();
}

class _HomeShellState extends ConsumerState<HomeShell> {
  int _index = 0;

  static const _titles = [
    'Inicio',
    'Control',
    'Alertas',
    'Ajustes',
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        backgroundColor: Colors.white,
        foregroundColor: AppColors.slate,
        elevation: 0.5,
        centerTitle: true,
        // Se asegura de que NO haya botón de hamburguesa (drawer)
        leading: const SizedBox.shrink(),
        title: _index == 0
            ? Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(Icons.eco, color: AppColors.leaf),
                  const SizedBox(width: 8),
                  Text(
                    'LUMATEK',
                    style: TextStyle(
                      letterSpacing: 1.2,
                      fontWeight: FontWeight.w900,
                      color: AppColors.canopy,
                    ),
                  ),
                ],
              )
            : Text(
                _titles[_index],
                style: const TextStyle(fontWeight: FontWeight.w800),
              ),
        actions: [
          if (_index == 0 || _index == 2)
            IconButton(
              icon: const Icon(Icons.notifications_none),
              onPressed: () => setState(() => _index = 2),
            ),
          if (_index == 1 || _index == 3)
            IconButton(
              icon: const Icon(Icons.settings_outlined),
              onPressed: () => setState(() => _index = 3),
            ),
          const SizedBox(width: 8),
        ],
      ),
      body: IndexedStack(
        index: _index,
        children: const [
          DashboardScreen(),
          IrrigationScreen(),
          AlertsScreen(),
          SettingsScreen(),
        ],
      ),
      bottomNavigationBar: NavigationBar(
        backgroundColor: Colors.white,
        indicatorColor: AppColors.leaf.withOpacity(0.12),
        selectedIndex: _index,
        onDestinationSelected: (value) => setState(() => _index = value),
        destinations: const [
          NavigationDestination(
            icon: Icon(Icons.home_outlined),
            selectedIcon: Icon(Icons.home),
            label: 'Inicio',
          ),
          NavigationDestination(
            icon: Icon(Icons.water_drop_outlined),
            selectedIcon: Icon(Icons.water_drop),
            label: 'Control',
          ),
          NavigationDestination(
            icon: Icon(Icons.notifications_none),
            selectedIcon: Icon(Icons.notifications),
            label: 'Alertas',
          ),
          NavigationDestination(
            icon: Icon(Icons.settings_outlined),
            selectedIcon: Icon(Icons.settings),
            label: 'Ajustes',
          ),
        ],
      ),
    );
  }
}
