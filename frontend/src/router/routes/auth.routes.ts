import type { RouteRecordRaw } from 'vue-router';

/**
 * Rutas de autenticación
 */
export const authRoutes: RouteRecordRaw[] = [
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/Auth/Login.vue'),
    meta: { requiresGuest: true },
  },
];

