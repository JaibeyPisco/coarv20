<script setup lang="ts">
import { computed, ref } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import ApplicationLogo from '../ApplicationLogo.vue';
import AppSidebar from './Partial/AppSidebar.vue';
import AppTopbar from './Partial/AppTopbar.vue';
import { useMenuPermissions } from '../../composables/useMenuPermissions';
import { useAuthReady } from '../../composables/useAuthReady';

const route = useRoute();
const authStore = useAuthStore();

// Esperar a que la autenticación esté lista antes de renderizar el contenido
const { isReady } = useAuthReady();

const drawer = ref(false);
const user = computed(() => authStore.user);
const { filtrarItemsMenu } = useMenuPermissions(user);

// Definir todos los items del menú con sus respectivos nombres de menú para permisos
const allSidebarItems = computed(() => [
    {
        label: 'Dashboard',
        href: '/dashboard',
        icon: 'ti ti-dashboard',
        active: route.name === 'dashboard',
    },
    {
        label: 'Perfil',
        href: '/profile',
        icon: 'ti ti-user',
        active: route.name === 'profile',
    },
    {
        label: 'Configuración',
        icon: 'ti ti-settings',
        active: route.path.startsWith('/configuracion'),
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

const companyDisplayName = computed(() => {
    const empresa = company.value;
    if (!empresa) return 'COAR';
    return empresa.razon_social || empresa.nombre_comercial || 'COAR';
});
</script>

<template>
    <!-- Mostrar loader mientras se verifica la autenticación -->
    <v-container v-if="!isReady" fluid class="fill-height">
        <v-row align="center" justify="center">
            <v-col cols="12" sm="8" md="6" lg="4">
                <v-card>
                    <v-card-text class="text-center py-8">
                        <v-progress-circular
                            indeterminate
                            color="primary"
                            size="64"
                        />
                        <p class="mt-4 mb-0 text-body-2 text-medium-emphasis">
                            Verificando autenticación...
                        </p>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </v-container>

    <!-- Contenido principal solo se muestra cuando la autenticación está lista -->
    <template v-else>
        <AppSidebar :items="sidebarItems" v-model:drawer="drawer">
            <template #brand>
                <router-link
                    to="/dashboard"
                    class="text-decoration-none d-flex align-center justify-center pa-2"
                >
                    <v-avatar v-if="company.logo_url" size="40">
                        <v-img :src="company.logo_url" alt="Logo empresa" />
                    </v-avatar>
                    <ApplicationLogo
                        v-else
                        style="width: 40px; height: 40px"
                    />
                </router-link>
            </template>
        </AppSidebar>

        <AppTopbar
            :nav="topNavItems"
            :user="(user as any) || {}"
            :user-menu="userMenu"
            :drawer="drawer"
            @update:drawer="drawer = $event"
        >
            <template #brand>
                <div class="d-flex align-center">
                    <span class="text-body-2 font-weight-semibold">
                        {{ companyDisplayName }}
                    </span>
                </div>
            </template>
        </AppTopbar>

        <v-main>
            <v-container v-if="$slots.header" fluid class="pa-4">
                <slot name="header" />
            </v-container>

            <v-container fluid>
                <slot />
            </v-container>
        </v-main>
    </template>
</template>
