import type { RouteRecordRaw } from "vue-router";

/*
* Rutasd del modulo reporte 
*/

export const reporteRoutes: RouteRecordRaw[] =[
    {
        path: '/reportes/movimiento_informacion',
        name: 'movimiento_informacion',
        component: () => import('@/views/Reporte/movimientoInformacion.vue'),
        meta: {requireAuth:true}
    }
];