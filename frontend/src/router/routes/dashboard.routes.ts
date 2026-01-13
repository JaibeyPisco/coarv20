import type { RouteRecordRaw } from 'vue-router';

/**
 * Rutas del dashboard y perfil
 */
export const dashboardRoutes: RouteRecordRaw[] = [
  {
    path: '/',
    redirect: '/dashboard',
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('@/views/Dashboard.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/profile',
    name: 'profile',
    component: () => import('@/views/Profile/Edit.vue'),
    meta: { requiresAuth: true },
  },
];

