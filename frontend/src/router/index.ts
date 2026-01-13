import { createRouter, createWebHashHistory } from 'vue-router';

import { authRoutes, dashboardRoutes, configuracionRoutes, reporteRoutes } from './routes';

import { authGuard } from './guards/auth.guard';

import { guestGuard } from './guards/guest.guard';
import { operacionRoutes } from './routes/operacion.routes';

/**
 * Configuración del router
 * Centraliza todas las rutas y guards de navegación
 */

const router = createRouter({
    history: createWebHashHistory(),
    routes: [
        ...dashboardRoutes,
        ...authRoutes,
        ...configuracionRoutes,
        ...operacionRoutes,
        ...reporteRoutes,
        {
            path: '/:pathMatch(.*)*',
            name: 'not-found',
            component: () => import('@/views/NotFound.vue'),
        },
    ],
});

/**
 * Guards de navegación
 * Aplican validaciones antes de permitir acceso a rutas
 */
router.beforeEach(async (to, from, next) => {
    if (to.meta.requiresAuth) {
        await authGuard(to, from, next);
    } else if (to.meta.requiresGuest) {
        await guestGuard(to, from, next);
    } else {
        next();
    }
});

export default router;
