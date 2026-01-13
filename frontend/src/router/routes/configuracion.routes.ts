import type { RouteRecordRaw } from 'vue-router';

/**
 * Rutas de configuración
 */
export const configuracionRoutes: RouteRecordRaw[] = [
  {
    path: '/configuracion/empresa',
    name: 'empresa.index',
    component: () => import('@/views/Configuracion/Empresa/Index.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/configuracion/areas',
    name: 'areas.index',
    component: () => import('@/views/Configuracion/Areas/Index.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/configuracion/lugares',
    name: 'lugares.index',
    component: () => import('@/views/Configuracion/Lugares/Index.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/configuracion/tipos-incidencia',
    name: 'tipos-incidencia.index',
    component: () => import('@/views/Configuracion/TiposIncidencia/Index.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/configuracion/estado-monitoreo',
    name: 'estado-monitoreo.index',
    component: () => import('@/views/Configuracion/EstadoMonitoreo/Index.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/configuracion/tipo-personal',
    name: 'tipo-personal.index',
    component: () => import('@/views/Configuracion/TipoPersonal/Index.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/configuracion/personal',
    name: 'personal.index',
    component: () => import('@/views/Configuracion/Personal/Index.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/configuracion/rol',
    name: 'rol.index',
    component: () => import('@/views/Configuracion/Roles/Index.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/configuracion/usuario',
    name: 'usuario.index',
    component: () => import('@/views/Configuracion/Usuario/Index.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/configuracion/estudiante',
    name: 'estudiante.index',
    component: () => import('@/views/Configuracion/Estudiante/Index.vue'),
    meta: { requiresAuth: true },
  },
];

