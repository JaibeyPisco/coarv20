import type { NavigationGuardNext, RouteLocationNormalized } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

/**
 * Guard de autenticación
 * Verifica que el usuario esté autenticado antes de acceder a la ruta
 */
export async function authGuard(
  to: RouteLocationNormalized,
  _from: RouteLocationNormalized,
  next: NavigationGuardNext
): Promise<void> {
  const authStore = useAuthStore();

  // Si no hay token, redirigir inmediatamente sin hacer petición
  if (!authStore.token) {
    next({ name: 'login', query: { redirect: to.fullPath } });
    return;
  }

  // Si hay token pero no hay usuario, verificar autenticación (esto obtendrá el usuario)
  if (!authStore.user && authStore.token) {
    const isAuthenticated = await authStore.checkAuth();
    if (!isAuthenticated) {
      next({ name: 'login', query: { redirect: to.fullPath } });
      return;
    }
  }

  next();
}


