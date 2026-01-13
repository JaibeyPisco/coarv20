import type { RouteRecordRaw } from 'vue-router';

const prefix = '/operacion';
const routePrefix = '/' + prefix;

const folderPrefix = '@/views/Operacion';

export const operacionRoutes: RouteRecordRaw[] = [
    {
        path: '/operacion/nuevaIncidencia',
        name: 'operacion.nuevaIncidencia',
        component: () => import('@/views/Operacion/NuevaIncidencia.vue'),
        meta: { requiresAuth: true },
    },
];
