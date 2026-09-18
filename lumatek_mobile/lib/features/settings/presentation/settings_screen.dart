import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/app_card.dart';
import '../../auth/presentation/auth_controller.dart';
import '../../greenhouses/presentation/greenhouse_picker_screen.dart';

class SettingsScreen extends ConsumerWidget {
  const SettingsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final user = ref.watch(currentUserProvider);
    final auth = ref.watch(authControllerProvider);
    final greenhouse = auth.selectedGreenhouse;

    return ListView(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 20),
      children: [
        // Perfil de Usuario
        AppCard(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                CircleAvatar(
                  radius: 30,
                  backgroundColor: AppColors.leaf,
                  child: Text(
                    user?.initials ?? '?',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 22,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        user?.name ?? 'Usuario',
                        style: const TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                      Text(
                        user?.email ?? '',
                        style: TextStyle(
                          fontSize: 14,
                          color: AppColors.slate.withOpacity(0.6),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 20),

        // Invernadero Activo
        const Text(
          'Invernadero activo',
          style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600),
        ),
        const SizedBox(height: 8),
        AppCard(
          child: ListTile(
            title: Text(greenhouse?.name ?? 'Ninguno seleccionado'),
            trailing: const Icon(Icons.chevron_right),
            onTap: () {
              Navigator.of(context).push(
                MaterialPageRoute(
                  builder: (_) => const GreenhousePickerScreen(canGoBack: true),
                ),
              );
            },
          ),
        ),
        const SizedBox(height: 24),

        // Configuración
        const Text(
          'Configuración',
          style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600),
        ),
        const SizedBox(height: 8),
        AppCard(
          child: Column(
            children: [
              _SettingTile(
                icon: Icons.sensors,
                label: 'Sensores',
                onTap: () {},
              ),
              const Divider(height: 1, indent: 50),
              _SettingTile(
                icon: Icons.notifications_none,
                label: 'Notificaciones',
                onTap: () {},
              ),
              const Divider(height: 1, indent: 50),
              _SettingTile(
                icon: Icons.credit_card,
                label: 'Suscripción',
                trailing: const Text(
                  'Premium',
                  style: TextStyle(
                    color: AppColors.leaf,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                onTap: () {},
              ),
            ],
          ),
        ),
        const SizedBox(height: 24),

        // Soporte
        const Text(
          'Soporte',
          style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600),
        ),
        const SizedBox(height: 8),
        AppCard(
          child: _SettingTile(
            icon: Icons.info_outline,
            label: 'Acerca de Lumatek',
            onTap: () {},
          ),
        ),
        const SizedBox(height: 32),

        // Cerrar Sesión
        TextButton.icon(
          onPressed: () => ref.read(authControllerProvider.notifier).logout(),
          icon: const Icon(Icons.logout, color: AppColors.ember),
          label: const Text(
            'Cerrar sesión',
            style: TextStyle(color: AppColors.ember, fontWeight: FontWeight.w600),
          ),
        ),
      ],
    );
  }
}

class _SettingTile extends StatelessWidget {
  const _SettingTile({
    required this.icon,
    required this.label,
    required this.onTap,
    this.trailing,
  });

  final IconData icon;
  final String label;
  final VoidCallback onTap;
  final Widget? trailing;

  @override
  Widget build(BuildContext context) {
    return ListTile(
      leading: Icon(icon, color: AppColors.slate.withOpacity(0.7)),
      title: Text(label),
      trailing: trailing ?? const Icon(Icons.chevron_right, size: 20),
      onTap: onTap,
    );
  }
}
