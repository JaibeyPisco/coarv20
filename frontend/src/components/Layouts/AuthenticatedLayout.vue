<script setup lang="ts">
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import ApplicationLogo from '../ApplicationLogo.vue';
import AppSidebar from './Partial/AppSidebar.vue';
import AppTopbar from './Partial/AppTopbar.vue';
import { useTablerAssets } from '../../composables/useTablerAssets';
import { useMenuPermissions } from '../../composables/useMenuPermissions';
import { useAuthReady } from '../../composables/useAuthReady';

const route = useRoute();
const authStore = useAuthStore();

// Esperar a que la autenticación esté lista antes de renderizar el contenido
const { isReady } = useAuthReady();

const { headLinks } = useTablerAssets({ cleanup: false });

const user = computed(() => authStore.user);
const { filtrarItemsMenu } = useMenuPermissions(user);

// Definir todos los items del menú con sus respectivos nombres de menú para permisos
const allSidebarItems = computed(() => [
    {
        label: 'Dashboard',
        href: '/dashboard',
        icon: 'ti ti-dashboard',
        active: route.name === 'dashboard',
        // Dashboard no requiere permiso específico
    },
    {
        label: 'Perfil',
        href: '/profile',
        icon: 'ti ti-user',
        active: route.name === 'profile',
        // Perfil no requiere permiso específico
    },
    {
        label: 'Configuración',
        icon: 'ti ti-settings',
        active: route.path.startsWith('/configuracion'),
        // No definir menu aquí, se mostrará si tiene al menos un child visible
        children: [
            {
                label: 'Empresa',
                href: '/configuracion/empresa',
                active: route.name === 'empresa.index',
                menu: 'configuracion-empresa',
            },
            {
                label: 'Áreas',
                href: '/configuracion/areas',
                active: route.name === 'areas.index',
                menu: 'configuracion-area',
            },
            {
                label: 'Lugares',
                href: '/configuracion/lugares',
                active: route.name === 'lugares.index',
                menu: 'configuracion-lugar',
            },
            {
                label: 'Tipos de Incidencias',
                href: '/configuracion/tipos-incidencia',
                active: route.name === 'tipos-incidencia.index',
                menu: 'configuracion-tipos_incidencia',
            },
            {
                label: 'Estados de Monitoreo',
                href: '/configuracion/estado-monitoreo',
                active: route.name === 'estado-monitoreo.index',
                menu: 'configuracion-estado_monitoreo',
            },
            {
                label: 'Tipos de Personal',
                href: '/configuracion/tipo-personal',
                active: route.name === 'tipo-personal.index',
                menu: 'configuracion-tipo_personal',
            },
            {
                label: 'Personal',
                href: '/configuracion/personal',
                active: route.name === 'personal.index',
                menu: 'configuracion-personal',
            },
            {
                label: 'Roles y Permisos',
                href: '/configuracion/rol',
                active: route.name === 'rol.index',
                menu: 'configuracion-rol',
            },
            {
                label: 'Usuarios',
                href: '/configuracion/usuario',
                active: route.name === 'usuario.index',
                menu: 'configuracion-usuario',
            },
            {
                label: 'Estudiantes',
                href: '/configuracion/estudiante',
                active: route.name === 'estudiante.index',
                menu: 'configuracion-estudiante',
            },
        ],
    },
    {
        label: 'Reportes',
        icon: 'ti ti-settings',
        active: route.path.startsWith('/reportes'),
        children: [
            {
                label: 'Movimiento de informacion',
                href: '/reportes/movimiento_informacion',
                active: route.name === 'movimiento_informacion',
                menu: 'reportes-movimiento_informacion',
            },
        ],
    },
    {
        label: 'Operaciones',
        icon: 'ti ti-settings',
        active: route.path.startsWith('/operacion'),
        children: [
            {
                label: 'Nueva incidencia',
                href: '/operacion/nuevaIncidencia',
                active: route.name === 'operacion.nuevaIncidencia',
                menu: 'operacion-nuevaIncidencia',
            },
        ],
    },
]);

// Filtrar items según permisos del usuario
const sidebarItems = computed(() => filtrarItemsMenu(allSidebarItems.value));

const topNavItems = computed(() => [
    {
        label: 'Dashboard',
        href: '/dashboard',
        icon: 'ti ti-home',
        active: route.name === 'dashboard',
    },
    {
        label: 'Configuración',
        href: '/configuracion/areas',
        icon: 'ti ti-settings',
        active: route.path.startsWith('/configuracion'),
    },
]);

const userMenu = computed(() => [
    {
        label: 'Perfil',
        href: '/profile',
    },
    {
        label: 'Cerrar sesión',
        href: '#',
    },
]);

const company = computed(
    () =>
        authStore.user?.empresa || {
            logo_url: null,
            nombre_comercial: 'COAR',
            razon_social: 'COAR',
            numero_documento: null,
            email: null,
        }
);

// Mantener el nombre completo, solo se reducirá el tamaño visual
const companyDisplayName = computed(() => {
    const empresa = company.value;
    if (!empresa) return 'COAR';

    // Preferir razón social, sino nombre comercial
    return empresa.razon_social || empresa.nombre_comercial || 'COAR';
});
</script>

<template>
    <div class="page">
        <template v-for="link in headLinks" :key="link.key">
            <link v-bind="link" />
        </template>

        <!-- Mostrar loader mientras se verifica la autenticación -->
        <div v-if="!isReady" class="page-body">
            <div class="container-xl">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="mt-3 mb-0 text-muted">Verificando autenticación...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido principal solo se muestra cuando la autenticación está lista -->
        <template v-else>
            <AppSidebar :items="sidebarItems">
                <template #brand>
                    <router-link
                        to="/dashboard"
                        class="navbar-brand d-flex align-items-center justify-content-center"
                    >
                        <div
                            class="navbar-brand-image d-flex align-items-center justify-content-center"
                        >
                            <img
                                v-if="company.logo_url"
                                :src="company.logo_url"
                                alt="Logo empresa"
                                class="img-fluid"
                                style="max-height: 40px"
                            />
                            <ApplicationLogo
                                v-else
                                class="icon"
                                style="width: 40px; height: 40px"
                            />
                        </div>
                    </router-link>
                </template>
            </AppSidebar>

            <div class="page-wrapper">
                <AppTopbar :nav="topNavItems" :user="(user as any) || {}" :user-menu="userMenu">
                    <template #brand>
                        <div class="d-flex align-items-center">
                            <div class="fw-semibold" style="font-size: 0.875rem">
                                {{ companyDisplayName }}
                            </div>
                        </div>
                    </template>
                </AppTopbar>

                <div v-if="$slots.header" class="page-header d-print-none">
                    <div class="container-xl">
                        <slot name="header" />
                    </div>
                </div>

                <main class="page-body">
                    <div class="container-xl">
                        <slot />
                    </div>
                </main>
            </div>
        </template>
    </div>
</template>
