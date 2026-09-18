import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../features/auth/presentation/auth_controller.dart';
import '../../features/auth/presentation/login_screen.dart';
import '../../features/greenhouses/presentation/greenhouse_picker_screen.dart';
import '../../shared/widgets/home_shell.dart';
import '../theme/app_theme.dart';

/// Pantalla de arranque: se muestra mientras se valida el token guardado.
class SplashScreen extends StatelessWidget {
  const SplashScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return const Scaffold(
      backgroundColor: AppColors.canopy,
      body: Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.eco_outlined, color: Colors.white, size: 46),
            SizedBox(height: 18),
            SizedBox(
              width: 22,
              height: 22,
              child: CircularProgressIndicator(
                strokeWidth: 2.2,
                color: Colors.white70,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

/// Puente entre Riverpod y go_router: cada cambio de sesión vuelve a evaluar
/// la redirección.
class _AuthRefreshNotifier extends ChangeNotifier {
  _AuthRefreshNotifier(Ref ref) {
    ref.listen(authControllerProvider, (previous, next) {
      if (previous?.status != next.status ||
          previous?.selectedGreenhouseId != next.selectedGreenhouseId) {
        notifyListeners();
      }
    });
  }
}

final routerProvider = Provider<GoRouter>((ref) {
  final refresh = _AuthRefreshNotifier(ref);
  ref.onDispose(refresh.dispose);

  return GoRouter(
    initialLocation: '/',
    refreshListenable: refresh,
    routes: [
      GoRoute(path: '/', builder: (_, __) => const SplashScreen()),
      GoRoute(path: '/login', builder: (_, __) => const LoginScreen()),
      GoRoute(
        path: '/greenhouses',
        builder: (_, __) => const GreenhousePickerScreen(),
      ),
      GoRoute(path: '/home', builder: (_, __) => const HomeShell()),
    ],
    redirect: (context, state) {
      final auth = ref.read(authControllerProvider);
      final location = state.matchedLocation;

      // Todavía no se sabe si hay sesión: quedarse en el splash.
      if (auth.status == AuthStatus.unknown) {
        return location == '/' ? null : '/';
      }

      if (!auth.isAuthenticated) {
        return location == '/login' ? null : '/login';
      }

      // Con sesión pero sin invernadero elegido, el resto de la app no
      // tiene a qué apuntar.
      if (auth.needsGreenhouseSelection) {
        return location == '/greenhouses' ? null : '/greenhouses';
      }

      if (location == '/' || location == '/login') {
        return '/home';
      }

      return null;
    },
  );
});
