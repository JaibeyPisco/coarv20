import type { NavigationGuardNext, RouteLocationNormalized } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

/**
 * Guard para usuarios no autenticados
 * Redirige a usuarios autenticados que intentan acceder a rutas de invitado
 */
export async function guestGuard(
  _to: RouteLocationNormalized,
  _from: RouteLocationNormalized,
  next: NavigationGuardNext
): Promise<void> {
  const authStore = useAuthStore();

  // Si hay token, verificar si está autenticado
  if (authStore.token) {
    // Si ya está autenticado según el estado, redirigir
    if (authStore.isAuthenticated) {
      next({ name: 'dashboard' });
      return;
    }
    // Verificar con el servidor
    const isAuthenticated = await authStore.checkAuth();
    if (isAuthenticated) {
      next({ name: 'dashboard' });
      return;
    }
  }

  next();
}

