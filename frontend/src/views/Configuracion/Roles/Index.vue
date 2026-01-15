<script setup lang="ts">
import AuthenticatedLayout from '@/components/Layouts/AuthenticatedLayout.vue';
import { ref, reactive, onMounted } from 'vue';
import * as XLSX from 'xlsx';
import type { Rol, Permiso, ModuloPermiso } from '@/types/auth';
import AppModal from '@/components/Partial/AppModal.vue';
import VDataTableCard from '@/components/Table/VDataTableCard.vue';
import { useVuetifyTable } from '@/composables/useVuetifyTable';
import { useCrudModal } from '@/composables/useCrudModal';
import apiClient from '@/api/axios';
import { formatStatusChip } from '@/utils/vuetifyTableHelpers';

// Estructura de módulos de permisos
const modulosPermisos: ModuloPermiso[] = [
    {
        seccion: 'DASHBOARD',
        menus: [
            { menu: 'dashboard-general', nombre: 'Sistema General', tieneDelete: false },
        ],
    },
    {
        seccion: 'CONFIGURACIÓN',
        menus: [
            { menu: 'configuracion-empresa', nombre: 'Empresa', tieneDelete: false },
            { menu: 'configuracion-area', nombre: 'Areas' },
            { menu: 'configuracion-lugar', nombre: 'Lugares' },
            { menu: 'configuracion-tipos_incidencia', nombre: 'Tipos de incidencias' },
            { menu: 'configuracion-estado_monitoreo', nombre: 'Estados de Monitoreo' },
            { menu: 'configuracion-tipo_personal', nombre: 'Tipos de Personal' },
            { menu: 'configuracion-rol', nombre: 'Roles y Permisos' },
            { menu: 'configuracion-personal', nombre: 'Personales' },
            { menu: 'configuracion-proveedor', nombre: 'Proveedores' },
            { menu: 'configuracion-estudiante', nombre: 'Estudiante' },
            { menu: 'configuracion-usuario', nombre: 'Usuarios' },
            { menu: 'configuracion-grados_secciones', nombre: 'Grados y Secciones' },
        ],
    },
    {
        seccion: 'OPERACIÓN',
        menus: [
            { menu: 'operacion-nueva_incidencia', nombre: 'Nueva Incidencia', tieneDelete: false },
            { menu: 'operacion-incidencias', nombre: 'Incidencias', tieneNew: false },
            { menu: 'operacion-derivacion', nombre: 'Derivaciones', tieneNew: false, tieneDelete: false },
            { menu: 'operacion-gestion-incidencias', nombre: 'Gestión de incidencias', tieneNew: false, tieneDelete: false },
            { menu: 'operacion-nueva_asistencia-escolar', nombre: 'Nueva Asistencia escolar', tieneDelete: false },
            { menu: 'operacion-asistencia-escolar', nombre: 'Asistencia escolar', tieneNew: false, tieneEdit: false },
            { menu: 'operacion-reporte_incidencia', nombre: 'Reporte incidencias' },
        ],
    },
];

// Estado de permisos en el formulario
const permisosForm = ref<Record<string, { view: boolean; new: boolean; edit: boolean; delete: boolean }>>({});

// Form
const saveForm = reactive({
    nombre: '',
    fl_no_dashboard: false,
});

// Headers de la tabla
const headers = [
    {
        title: 'ACCIONES',
        key: 'actions',
        sortable: false,
        width: '150px',
    },
    {
        title: 'NOMBRE',
        key: 'nombre',
        sortable: true,
    },
    {
        title: 'ESTADO',
        key: 'estado',
        sortable: true,
        align: 'center' as const,
        width: '120px',
    },
];

// Composable de tabla
const table = useVuetifyTable<Rol>({
    apiURL: '/configuracion/rol',
    searchFields: ['nombre'],
    serverSidePagination: false,
    serverSideSorting: false,
    serverSideSearch: false,
});

// Inicializar menú de columnas
table.updateColumnMenu(headers);

// Funciones auxiliares
function resetPermisos() {
    permisosForm.value = {};
    modulosPermisos.forEach((modulo) => {
        modulo.menus.forEach((menu: { menu: string; nombre: string; tieneDelete?: boolean; tieneNew?: boolean; tieneEdit?: boolean }) => {
            permisosForm.value[menu.menu] = {
                view: false,
                new: false,
                edit: false,
                delete: false,
            };
        });
    });
}

function toggleAllPermisos(tipo: 'view' | 'new' | 'edit' | 'delete', checked: boolean | null) {
    if (checked === null) return;
    modulosPermisos.forEach((modulo) => {
        modulo.menus.forEach((menu: { menu: string; nombre: string; tieneDelete?: boolean; tieneNew?: boolean; tieneEdit?: boolean }) => {
            if (tipo === 'delete' && menu.tieneDelete === false) return;
            if (tipo === 'new' && menu.tieneNew === false) return;
            if (tipo === 'edit' && menu.tieneEdit === false) return;
            const permiso = permisosForm.value[menu.menu];
            if (permiso) {
                permiso[tipo] = checked;
            }
        });
    });
}

function getPermisosSeleccionados(): Permiso[] {
    const permisos: Permiso[] = [];
    Object.keys(permisosForm.value).forEach((menu) => {
        const permiso = permisosForm.value[menu];
        if (permiso && (permiso.view || permiso.new || permiso.edit || permiso.delete)) {
            permisos.push({
                menu,
                view: permiso.view,
                new: permiso.new,
                edit: permiso.edit,
                delete: permiso.delete,
            });
        }
    });
    return permisos;
}

// Composable de CRUD
const crudModal = useCrudModal<Rol>({
    entityName: 'rol',
    getPayload: (form) => ({
        nombre: form.nombre.trim(),
        fl_no_dashboard: form.fl_no_dashboard,
        permisos: getPermisosSeleccionados(),
    }),
    validateForm: (form) => {
        if (!form.nombre.trim()) {
            return 'El nombre del rol es obligatorio.';
        }
        const permisos = getPermisosSeleccionados();
        if (permisos.length === 0) {
            return 'Debe seleccionar al menos un permiso.';
        }
        return null;
    },
    onCreate: async (data: any) => {
        const response = await apiClient.post<Rol>('/configuracion/rol', data);
        return response.data;
    },
    onUpdate: async (id: number, data: any) => {
        const response = await apiClient.post<Rol>(`/configuracion/rol/${id}`, data);
        return response.data;
    },
    onDeleteCustom: async (id: number) => {
        await apiClient.delete(`/configuracion/rol/${id}`);
    },
    onEdit: async (rol: Rol) => {
        saveForm.nombre = rol.nombre;
        saveForm.fl_no_dashboard = rol.fl_no_dashboard;

        // Cargar permisos desde la API
        resetPermisos();
        try {
            const response = await apiClient.get(`/configuracion/rol/${rol.id}`);
            const rolData = response.data.data;
            
            if (rolData.permisos && Array.isArray(rolData.permisos)) {
                rolData.permisos.forEach((permiso: Permiso) => {
                    permisosForm.value[permiso.menu] = {
                        view: permiso.view,
                        new: permiso.new,
                        edit: permiso.edit,
                        delete: permiso.delete,
                    };
                });
            }
        } catch (error: any) {
            console.error('Error cargando permisos:', error);
        }
    },
    resetForm: () => {
        saveForm.nombre = '';
        saveForm.fl_no_dashboard = false;
        resetPermisos();
    },
});

// Funciones
const updateSearchValue = (value: string) => {
    table.searchQuery.value = value;
    table.applySearch(value);
};

const downloadExcel = () => {
    table.downloadExcel('roles.xlsx', 'Roles');
};

const toggleColumnVisibility = (key: string) => {
    table.toggleColumnVisibility(key);
};

// Lifecycle
onMounted(async () => {
    (window as any).XLSX = XLSX;
    resetPermisos();
    await table.loadItems({
        page: 1,
        itemsPerPage: 10,
    });
});
</script>

<template>
    <AuthenticatedLayout>
        <v-container fluid class="pa-4">
            <!-- Header Section -->
            <v-card class="mb-4" rounded="lg" elevation="1">
                <v-card-text class="pa-4">
                    <div class="d-flex flex-wrap align-center justify-space-between ga-4">
                        <div>
                            <h1 class="text-h5 font-weight-bold mb-2">Roles y Permisos</h1>
                            <p class="text-body-2 text-medium-emphasis mb-0">
                                Gestiona los roles y permisos del sistema; crea, edita o elimina según necesidad.
                            </p>
                        </div>
                        <v-btn
                            color="primary"
                            prepend-icon="mdi-plus"
                            variant="flat"
                            size="default"
                            @click="crudModal.openCreateModal"
                            aria-label="Crear nuevo rol"
                            class="text-none"
                        >
                            Nuevo Rol
                        </v-btn>
                    </div>
                </v-card-text>
            </v-card>

            <!-- Table Section -->
            <v-card rounded="lg" elevation="1">
                <VDataTableCard
                    :loading="table.loading.value"
                    :column-menu="table.columnMenu.value"
                    :search-value="table.searchQuery.value"
                    search-placeholder="Buscar rol..."
                    @print="table.printTable"
                    @export="downloadExcel"
                    @toggle-column="toggleColumnVisibility"
                    @update:search="updateSearchValue"
                >
                    <v-data-table-server
                        v-model:page="table.page.value"
                        v-model:items-per-page="table.itemsPerPage.value"
                        v-model:sort-by="table.sortBy.value"
                        :headers="headers.filter(h => table.columnMenu.value.find(c => c.key === h.key)?.visible !== false)"
                        :items="table.items.value"
                        :loading="table.loading.value"
                        :items-length="table.totalItems.value"
                        :density="'compact'"
                        :fixed-header="true"
                        height="450"
                        :items-per-page-options="[]"
                        hide-default-footer
                        @update:options="table.loadItems"
                        class="elevation-0"
                    >
                        <template #item.actions="{ item }">
                            <div class="d-flex align-center ga-1">
                                <v-btn
                                    icon="mdi-pencil"
                                    size="small"
                                    color="primary"
                                    variant="flat"
                                    @click="crudModal.openEditModal(item)"
                                />
                                <v-menu>
                                    <template #activator="{ props: menuProps }">
                                        <v-btn
                                            v-bind="menuProps"
                                            icon="mdi-dots-vertical"
                                            size="small"
                                            color="grey-darken-1"
                                            variant="text"
                                        />
                                    </template>
                                    <v-list density="compact">
                                        <v-list-item
                                            prepend-icon="mdi-delete"
                                            title="Eliminar"
                                            class="text-error"
                                            @click="crudModal.openDeleteModal(item)"
                                        />
                                    </v-list>
                                </v-menu>
                            </div>
                        </template>

                        <template #item.estado="{ value }">
                            <v-chip
                                :color="formatStatusChip(value).color"
                                size="small"
                                variant="flat"
                                class="text-uppercase font-weight-medium"
                            >
                                {{ formatStatusChip(value).label }}
                            </v-chip>
                        </template>
                    </v-data-table-server>

                    <template #footer-left>
                        <span class="text-body-2 text-medium-emphasis">{{ table.recordSummary.value }}</span>
                    </template>
                    <template #footer-right>
                        <span class="text-body-2 text-medium-emphasis">Actualizado automáticamente</span>
                    </template>
                </VDataTableCard>
            </v-card>

            <!-- Save/Edit Modal -->
            <AppModal
                v-model:open="crudModal.showSaveModal.value"
                :title="crudModal.saveModalTitle.value"
                size="xl"
            >
                <template #body>
                    <v-form @submit.prevent>
                        <v-container fluid class="pa-0">
                            <v-row class="mb-4">
                                <v-col cols="12" md="8">
                                    <v-text-field
                                        v-model="saveForm.nombre"
                                        label="Nombre del Rol"
                                        :rules="[(v) => !!v || 'El nombre es obligatorio']"
                                        counter="200"
                                        maxlength="200"
                                        placeholder="Ingrese el nombre del rol"
                                        required
                                        variant="outlined"
                                        density="compact"
                                        class="mb-2"
                                    />
                                </v-col>
                                <v-col cols="12" md="4" class="d-flex align-center">
                                    <v-checkbox
                                        v-model="saveForm.fl_no_dashboard"
                                        label="Ocultar Dashboard"
                                        hide-details
                                        density="compact"
                                    />
                                </v-col>
                            </v-row>

                            <v-divider class="mb-4" />

                            <div class="mb-2">
                                <h3 class="text-h6 font-weight-medium mb-3">Permisos del Sistema</h3>
                                <v-card variant="outlined" rounded="md">
                                    <v-card-text class="pa-0">
                                        <div style="max-height: 500px; overflow-y: auto;">
                                            <v-table density="compact" class="permissions-table">
                                                <thead>
                                                    <tr>
                                                        <th class="text-left pa-3" style="min-width: 280px;">
                                                            <span class="text-body-2 font-weight-medium">SECCIONES</span>
                                                        </th>
                                                        <th class="text-center pa-2" style="width: 90px;">
                                                            <v-checkbox
                                                                hide-details
                                                                density="compact"
                                                                color="primary"
                                                                @update:model-value="(val: unknown) => toggleAllPermisos('view', val as boolean | null)"
                                                            />
                                                            <span class="text-caption font-weight-medium d-block mt-1">VER</span>
                                                        </th>
                                                        <th class="text-center pa-2" style="width: 90px;">
                                                            <v-checkbox
                                                                hide-details
                                                                density="compact"
                                                                color="primary"
                                                                @update:model-value="(val: unknown) => toggleAllPermisos('new', val as boolean | null)"
                                                            />
                                                            <span class="text-caption font-weight-medium d-block mt-1">CREAR</span>
                                                        </th>
                                                        <th class="text-center pa-2" style="width: 90px;">
                                                            <v-checkbox
                                                                hide-details
                                                                density="compact"
                                                                color="primary"
                                                                @update:model-value="(val: unknown) => toggleAllPermisos('edit', val as boolean | null)"
                                                            />
                                                            <span class="text-caption font-weight-medium d-block mt-1">EDITAR</span>
                                                        </th>
                                                        <th class="text-center pa-2" style="width: 90px;">
                                                            <v-checkbox
                                                                hide-details
                                                                density="compact"
                                                                color="primary"
                                                                @update:model-value="(val: unknown) => toggleAllPermisos('delete', val as boolean | null)"
                                                            />
                                                            <span class="text-caption font-weight-medium d-block mt-1">ELIMINAR</span>
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <template v-for="modulo in modulosPermisos" :key="modulo.seccion">
                                                        <tr class="section-header">
                                                            <td class="pa-3 bg-grey-lighten-4" colspan="5">
                                                                <span class="text-body-2 font-weight-bold">{{ modulo.seccion }}</span>
                                                            </td>
                                                        </tr>
                                                        <tr
                                                            v-for="menu in modulo.menus"
                                                            :key="menu.menu"
                                                            class="permission-row"
                                                        >
                                                            <td class="pa-3">
                                                                <span class="text-body-2">{{ menu.nombre }}</span>
                                                            </td>
                                                            <td class="text-center pa-2">
                                                                <v-checkbox
                                                                    :model-value="permisosForm[menu.menu]?.view || false"
                                                                    hide-details
                                                                    density="compact"
                                                                    color="primary"
                                                                    @update:model-value="(val: boolean | null) => {
                                                                        const permiso = permisosForm[menu.menu];
                                                                        if (permiso && val !== null) permiso.view = val;
                                                                    }"
                                                                />
                                                            </td>
                                                            <td class="text-center pa-2">
                                                                <v-checkbox
                                                                    v-if="menu.tieneNew !== false"
                                                                    :model-value="permisosForm[menu.menu]?.new || false"
                                                                    hide-details
                                                                    density="compact"
                                                                    color="primary"
                                                                    @update:model-value="(val: boolean | null) => {
                                                                        const permiso = permisosForm[menu.menu];
                                                                        if (permiso && val !== null) permiso.new = val;
                                                                    }"
                                                                />
                                                                <span v-else class="text-grey-lighten-1">—</span>
                                                            </td>
                                                            <td class="text-center pa-2">
                                                                <v-checkbox
                                                                    v-if="menu.tieneEdit !== false"
                                                                    :model-value="permisosForm[menu.menu]?.edit || false"
                                                                    hide-details
                                                                    density="compact"
                                                                    color="primary"
                                                                    @update:model-value="(val: boolean | null) => {
                                                                        const permiso = permisosForm[menu.menu];
                                                                        if (permiso && val !== null) permiso.edit = val;
                                                                    }"
                                                                />
                                                                <span v-else class="text-grey-lighten-1">—</span>
                                                            </td>
                                                            <td class="text-center pa-2">
                                                                <v-checkbox
                                                                    v-if="menu.tieneDelete !== false"
                                                                    :model-value="permisosForm[menu.menu]?.delete || false"
                                                                    hide-details
                                                                    density="compact"
                                                                    color="primary"
                                                                    @update:model-value="(val: boolean | null) => {
                                                                        const permiso = permisosForm[menu.menu];
                                                                        if (permiso && val !== null) permiso.delete = val;
                                                                    }"
                                                                />
                                                                <span v-else class="text-grey-lighten-1">—</span>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </v-table>
                                        </div>
                                    </v-card-text>
                                </v-card>
                            </div>
                        </v-container>
                    </v-form>
                </template>
                <template #footer>
                    <div class="d-flex justify-end ga-2">
                        <v-btn
                            variant="outlined"
                            @click="crudModal.closeSaveModal"
                            :disabled="crudModal.saving.value"
                            class="text-none"
                        >
                            Cancelar
                        </v-btn>
                        <v-btn
                            color="primary"
                            variant="flat"
                            @click="() => crudModal.handleSaveSubmit(saveForm, table.reloadTable)"
                            :loading="crudModal.saving.value"
                            class="text-none"
                        >
                            {{ crudModal.editingId.value ? 'Actualizar' : 'Guardar' }}
                        </v-btn>
                    </div>
                </template>
            </AppModal>

            <!-- Delete Modal -->
            <AppModal
                v-model:open="crudModal.showDeleteModal.value"
                title="Eliminar Rol"
                size="sm"
            >
                <template #body>
                    <v-container fluid class="pa-4">
                        <div class="text-center mb-4">
                            <v-icon icon="mdi-alert-circle" size="64" color="error" />
                        </div>
                        <p class="text-body-1 text-center">
                            ¿Está seguro que desea eliminar el rol
                            <strong class="text-error">{{ crudModal.deleteTarget.value?.nombre }}</strong>?
                        </p>
                        <p class="text-body-2 text-medium-emphasis text-center mt-2">
                            Esta acción no se puede deshacer.
                        </p>
                    </v-container>
                </template>
                <template #footer>
                    <div class="d-flex justify-end ga-2">
                        <v-btn
                            variant="outlined"
                            @click="crudModal.closeDeleteModal"
                            :disabled="crudModal.deleting.value"
                            class="text-none"
                        >
                            Cancelar
                        </v-btn>
                        <v-btn
                            color="error"
                            variant="flat"
                            @click="() => crudModal.handleDeleteConfirm(table.reloadTable)"
                            :loading="crudModal.deleting.value"
                            class="text-none"
                        >
                            Eliminar
                        </v-btn>
                    </div>
                </template>
            </AppModal>
        </v-container>
    </AuthenticatedLayout>
</template>

<style scoped>
.permissions-table :deep(.v-table) {
    border-collapse: separate;
    border-spacing: 0;
}

.permissions-table :deep(thead th) {
    background-color: rgb(var(--v-theme-surface));
    border-bottom: 2px solid rgba(var(--v-border-opacity), var(--v-border-opacity));
    position: sticky;
    top: 0;
    z-index: 1;
}

.permissions-table :deep(.section-header td) {
    background-color: rgb(var(--v-theme-grey-lighten-4));
    border-top: 1px solid rgba(var(--v-border-opacity), var(--v-border-opacity));
    border-bottom: 1px solid rgba(var(--v-border-opacity), var(--v-border-opacity));
}

.permissions-table :deep(.permission-row:hover) {
    background-color: rgba(var(--v-theme-primary), 0.04);
}

.permissions-table :deep(.permission-row td) {
    border-bottom: 1px solid rgba(var(--v-border-opacity), var(--v-border-opacity));
}

</style>
