<script setup lang="ts">
import AuthenticatedLayout from '@/components/Layouts/AuthenticatedLayout.vue';
import { ref, reactive, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import apiClient from '@/api/axios';
// @ts-expect-error -- tabulator-tables no proporciona tipos ES module
import { TabulatorFull as Tabulator } from 'tabulator-tables';
import * as XLSX from 'xlsx';
import type { Rol, Permiso, ModuloPermiso } from '@/types/auth';
import AppModal from '@/components/Partial/AppModal.vue';
import TableCard from '@/components/Table/TableCard.vue';
import { notificacion } from '@/utils/notificacion';

const tableEl = ref<HTMLElement | null>(null);
const table = ref<any | null>(null);
const roles = ref<Rol[]>([]);
const loading = ref(false);
const searchQuery = ref('');
const columnMenu = ref<{ title: string; field: string; visible: boolean }[]>([]);
const recordSummary = ref('Mostrando 0 registros');

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

function closeAllActionDropdowns() {
    document.querySelectorAll('.tabulator .actions-menu__dropdown.show').forEach((menu) => {
        menu.classList.remove('show');
    });
}

function handleGlobalClick(e: MouseEvent) {
    const target = e.target as HTMLElement;
    if (!target.closest('.tabulator .btn-group')) {
        closeAllActionDropdowns();
    }
}

const saveForm = reactive({
    nombre: '',
    fl_no_dashboard: false,
});

const showSaveModal = ref(false);
const showDeleteModal = ref(false);
const editingId = ref<number | null>(null);
const deleteTarget = ref<Rol | null>(null);
const saving = ref(false);
const deleting = ref(false);

const saveModalTitle = computed(() => (editingId.value ? 'Editar rol' : 'Nuevo rol'));

const columns = [
    {
        title: 'ACCIONES',
        field: '_actions',
        width: 120,
        headerHozAlign: 'center',
        hozAlign: 'center',
        resizable: false,
        headerSort: false,
        formatter: () => `
            <div class="btn-group actions-menu" style="position: relative;">
                <button
                    class="btn btn-sm btn-primary"
                    type="button"
                    data-action="edit"
                >
                    Editar
                </button>
                <button
                    class="btn btn-sm btn-primary dropdown-toggle dropdown-toggle-split actions-menu__toggle"
                    type="button"
                    data-action="toggle-menu"
                >
                    <span class="visually-hidden">Toggle Dropdown</span>
                </button>
                <div class="dropdown-menu dropdown-menu-start actions-menu__dropdown" style="position: absolute; left: 0; top: 100%; margin-top: 0.125rem; min-width: 180px; z-index: 1000;">
                    <button type="button" class="dropdown-item text-danger" data-action="delete">
                        <i class="ti ti-trash me-2"></i>Eliminar
                    </button>
                </div>
            </div>
        `,
        cellClick: handleActionCellClick,
    },
    {
        title: 'NOMBRE',
        field: 'nombre',
        minWidth: 250,
        headerSort: true,
        formatter: 'plaintext',
    },
    {
        title: 'ESTADO',
        field: 'estado',
        width: 120,
        headerHozAlign: 'center',
        hozAlign: 'center',
        formatter: (cell: any) => {
            const value = Number(cell.getValue());
            const statusConfig: Record<number, { label: string; className: string }> = {
                1: {
                    label: 'ACTIVO',
                    className: 'badge rounded-pill px-3 bg-green-lt text-green fw-semibold',
                },
                0: {
                    label: 'INACTIVO',
                    className: 'badge rounded-pill px-3 bg-red-lt text-red fw-semibold',
                },
            };

            const { label, className } =
                statusConfig[value] ?? {
                    label: 'SIN ESTADO',
                    className: 'badge rounded-pill px-3 bg-gray-lt text-secondary fw-semibold',
                };

            return `<span class="${className}">${label}</span>`;
        },
    },
];

async function initializeTable() {
    await nextTick();
    if (!tableEl.value) return;

    table.value = new Tabulator(tableEl.value, {
        layout: 'fitColumns',
        reactiveData: false,
        placeholder: 'No se encontraron registros',
        columns,
        printHeader: '<h4 class="mb-3">Listado de roles</h4>',
        printFooter: '<small>Generado desde la intranet</small>',
        height: 'calc(100vh - 360px)',
        columnDefaults: {
            resizable: true,
        },
        ajaxURL: 'configuracion/rol',
        ajaxContentType: 'json',
        ajaxRequestFunc: async (url: string) => {
            const response = await apiClient.get(url);
            return response.data;
        },
        ajaxResponse: (_url: string, _params: any, response: any) => {
            const data: Rol[] = response?.data ?? [];
            roles.value = data;
            loading.value = false;
            updateRecordSummary();
            return data;
        },
    });

    table.value.on('dataLoading', () => {
        loading.value = true;
    });
    table.value.on('tableBuilt', prepareColumnMenu);
    table.value.on('dataLoaded', updateRecordSummary);
    table.value.on('dataFiltered', updateRecordSummary);
    table.value.on('columnVisibilityChanged', prepareColumnMenu);
}

function prepareColumnMenu() {
    if (!table.value) return;
    columnMenu.value = table.value.getColumns().map((column: any) => ({
        title: column.getDefinition().title ?? '',
        field: column.getField(),
        visible: column.isVisible(),
    }));
}

function handleActionCellClick(e: MouseEvent, cell: any) {
    const target = e.target as HTMLElement;
    const toggleButton = target.closest('button[data-action="toggle-menu"]');

    if (toggleButton) {
        e.stopPropagation();
        const group = toggleButton.closest('.actions-menu');
        const menu = group?.querySelector('.actions-menu__dropdown');
        const isOpen = menu?.classList.contains('show');
        closeAllActionDropdowns();
        if (!isOpen) {
            menu?.classList.add('show');
        }
        return;
    }

    const actionBtn = target.closest('button[data-action]');
    if (!actionBtn) return;

    e.stopPropagation();
    closeAllActionDropdowns();
    const action = actionBtn.getAttribute('data-action');
    const data = cell.getRow().getData() as Rol;

    if (action === 'edit') {
        openEditModal(data);
    }

    if (action === 'delete') {
        openDeleteModal(data);
    }
}

async function reloadTable() {
    if (!table.value) return;
    loading.value = true;
    await table.value.replaceData('configuracion/rol');
    applySearch(searchQuery.value);
    updateRecordSummary();
}

function applySearch(query: string) {
    const normalized = query.trim().toLowerCase();
    if (!table.value) return;

    if (!normalized) {
        table.value.clearFilter(true);
    } else {
        table.value.setFilter((rowData: Rol) => {
            const values = [rowData.nombre];
            return values.some((value) => value?.toLowerCase().includes(normalized));
        });
    }

    updateRecordSummary();
}

function updateRecordSummary() {
    if (!table.value) {
        recordSummary.value = 'Mostrando 0 registros';
        return;
    }

    const filteredRows = table.value.getRows(true).length;
    const totalRows = table.value.getData().length || roles.value.length;
    recordSummary.value = `Mostrando ${filteredRows} de ${totalRows} registros`;
}

function openCreateModal() {
    editingId.value = null;
    resetSaveForm();
    resetPermisos();
    showSaveModal.value = true;
}

async function openEditModal(rol: Rol) {
    editingId.value = rol.id;
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
        notificacion('Error al cargar los permisos del rol.', { type: 'danger', title: 'Error' });
        console.error('Error cargando permisos:', error);
    }

    showSaveModal.value = true;
}

function openDeleteModal(rol: Rol) {
    deleteTarget.value = rol;
    showDeleteModal.value = true;
}

function resetSaveForm() {
    saveForm.nombre = '';
    saveForm.fl_no_dashboard = false;
}

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

function toggleAllPermisos(tipo: 'view' | 'new' | 'edit' | 'delete', checked: boolean) {
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

async function handleSaveSubmit() {
    if (!saveForm.nombre.trim()) {
        notificacion('El nombre del rol es obligatorio.', { type: 'danger', title: 'Validación' });
        return;
    }

    const permisos = getPermisosSeleccionados();
    if (permisos.length === 0) {
        notificacion('Debe seleccionar al menos un permiso.', { type: 'danger', title: 'Validación' });
        return;
    }

    const payload = {
        nombre: saveForm.nombre.trim(),
        fl_no_dashboard: saveForm.fl_no_dashboard,
        permisos,
    };

    saving.value = true;

    try {
        if (editingId.value) {
            await apiClient.post(`/configuracion/rol/${editingId.value}`, payload);
            notificacion('Rol actualizado correctamente.', { type: 'success' });
        } else {
            await apiClient.post('/configuracion/rol', payload);
            notificacion('Rol registrado correctamente.', { type: 'success' });
        }

        showSaveModal.value = false;
        await reloadTable();
    } catch (error: any) {
        if (error.response?.data?.errors) {
            const errors = error.response.data.errors;
            const firstError = Object.values(errors)[0] as string[];
            const message = firstError?.[0] || 'Error de validación';
            notificacion(message, { type: 'danger', title: 'Validación' });
        } else {
            const message = error.response?.data?.message || 'Ocurrió un inconveniente al guardar el registro.';
            notificacion(message, { type: 'danger', title: 'Error' });
        }
    } finally {
        saving.value = false;
    }
}

async function handleDeleteConfirm() {
    if (!deleteTarget.value) return;

    deleting.value = true;

    try {
        await apiClient.delete(`/configuracion/rol/${deleteTarget.value.id}`);
        notificacion('Rol eliminado correctamente.', { type: 'success' });
        showDeleteModal.value = false;
        await reloadTable();
    } catch (error: any) {
        const message = error.response?.data?.message || 'No fue posible eliminar el registro.';
        notificacion(message, { type: 'danger', title: 'Error' });
    } finally {
        deleting.value = false;
    }
}

function downloadExcel() {
    if (!table.value) return;
    // @ts-ignore Assign XLSX global
    (window as any).XLSX = XLSX;
    table.value.download('xlsx', 'roles.xlsx', { sheetName: 'Roles' });
}

function printTable() {
    table.value?.print(false, true);
}

function toggleColumnVisibility(field: string) {
    if (!table.value) return;
    const column = table.value.getColumn(field);
    if (!column) return;

    column.toggle();
    columnMenu.value = columnMenu.value.map((item) =>
        item.field === field ? { ...item, visible: column.isVisible() } : item,
    );
}

function closeSaveModal() {
    if (saving.value) return;
    showSaveModal.value = false;
}

function closeDeleteModal() {
    if (deleting.value) return;
    showDeleteModal.value = false;
}

function updateSearchValue(value: string) {
    searchQuery.value = value;
}

onMounted(async () => {
    // @ts-ignore expose for Tabulator download module
    (window as any).XLSX = XLSX;
    await initializeTable();
    // No llamar reloadTable() aquí - la tabla ya carga datos automáticamente con ajaxURL
    document.addEventListener('click', handleGlobalClick);
});

watch(searchQuery, (value) => {
    applySearch(value);
});

onBeforeUnmount(() => {
    table.value?.destroy();
    document.removeEventListener('click', handleGlobalClick);
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="h2 mb-1">Configuración / Roles y Permisos</h1>
                    <p class="text-secondary mb-0">
                        Gestiona los roles y permisos del sistema; crea, edita o elimina según necesidad.
                    </p>
                </div>
                <div class="btn-group">
                    <button
                        type="button"
                        class="btn btn-primary d-flex align-items-center gap-2"
                        @click="openCreateModal"
                    >
                        <i class="ti ti-plus"></i>
                        Nuevo
                    </button>
                </div>
            </div>
        </template>

        <div class="roles-page">
            <TableCard
                :loading="loading"
                :column-menu="columnMenu"
                :search-value="searchQuery"
                search-placeholder="Buscar rol..."
                @print="printTable"
                @export="downloadExcel"
                @toggle-column="toggleColumnVisibility"
                @update:search="updateSearchValue"
            >
                <div ref="tableEl" class="tabulator-wrapper"></div>

                <template #footer-left>
                    <span>{{ recordSummary }}</span>
                </template>
                <template #footer-right>
                    <span>Actualizado automáticamente al guardar cambios.</span>
                </template>
            </TableCard>
        </div>

        <AppModal
            :open="showSaveModal"
            :title="saveModalTitle"
            size="xl"
            @close="closeSaveModal"
        >
            <template #body>
                <form class="space-y-3" @submit.prevent>
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label required" for="rol-nombre">Nombre</label>
                            <input
                                id="rol-nombre"
                                v-model="saveForm.nombre"
                                type="text"
                                class="form-control"
                                maxlength="200"
                                placeholder="Nombre del rol"
                                required
                            />
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check">
                                <input
                                    id="rol-fl-no-dashboard"
                                    v-model="saveForm.fl_no_dashboard"
                                    type="checkbox"
                                    class="form-check-input"
                                />
                                <label class="form-check-label" for="rol-fl-no-dashboard">
                                    Ocultar Dashboard
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label required">Permisos</label>
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-bordered table-sm">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="min-width: 250px;">SECCIONES</th>
                                        <th class="text-center" style="width: 80px;">
                                            <input
                                                type="checkbox"
                                                class="form-check-input"
                                                @change="(e: Event) => toggleAllPermisos('view', (e.target as HTMLInputElement).checked)"
                                            />
                                            VER
                                        </th>
                                        <th class="text-center" style="width: 80px;">
                                            <input
                                                type="checkbox"
                                                class="form-check-input"
                                                @change="(e: Event) => toggleAllPermisos('new', (e.target as HTMLInputElement).checked)"
                                            />
                                            CREAR
                                        </th>
                                        <th class="text-center" style="width: 80px;">
                                            <input
                                                type="checkbox"
                                                class="form-check-input"
                                                @change="(e: Event) => toggleAllPermisos('edit', (e.target as HTMLInputElement).checked)"
                                            />
                                            EDITAR
                                        </th>
                                        <th class="text-center" style="width: 80px;">
                                            <input
                                                type="checkbox"
                                                class="form-check-input"
                                                @change="(e: Event) => toggleAllPermisos('delete', (e.target as HTMLInputElement).checked)"
                                            />
                                            ELIMINAR
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-for="modulo in modulosPermisos" :key="modulo.seccion">
                                        <tr>
                                            <td class="fw-bold bg-light" colspan="5">
                                                {{ modulo.seccion }}
                                            </td>
                                        </tr>
                                        <tr
                                            v-for="menu in modulo.menus"
                                            :key="menu.menu"
                                            :data-menu="menu.menu"
                                        >
                                            <td>{{ menu.nombre }}</td>
                                            <td class="text-center">
                                                <input
                                                    type="checkbox"
                                                    class="form-check-input"
                                                    :checked="permisosForm[menu.menu]?.view || false"
                                                    @change="(e: Event) => {
                                                        const target = e.target as HTMLInputElement;
                                                        const permiso = permisosForm[menu.menu];
                                                        if (permiso) {
                                                            permiso.view = target.checked;
                                                        }
                                                    }"
                                                />
                                            </td>
                                            <td class="text-center">
                                                <input
                                                    v-if="menu.tieneNew !== false"
                                                    type="checkbox"
                                                    class="form-check-input"
                                                    :checked="permisosForm[menu.menu]?.new || false"
                                                    @change="(e: Event) => {
                                                        const target = e.target as HTMLInputElement;
                                                        const permiso = permisosForm[menu.menu];
                                                        if (permiso) {
                                                            permiso.new = target.checked;
                                                        }
                                                    }"
                                                />
                                            </td>
                                            <td class="text-center">
                                                <input
                                                    v-if="menu.tieneEdit !== false"
                                                    type="checkbox"
                                                    class="form-check-input"
                                                    :checked="permisosForm[menu.menu]?.edit || false"
                                                    @change="(e: Event) => {
                                                        const target = e.target as HTMLInputElement;
                                                        const permiso = permisosForm[menu.menu];
                                                        if (permiso) {
                                                            permiso.edit = target.checked;
                                                        }
                                                    }"
                                                />
                                            </td>
                                            <td class="text-center">
                                                <input
                                                    v-if="menu.tieneDelete !== false"
                                                    type="checkbox"
                                                    class="form-check-input"
                                                    :checked="permisosForm[menu.menu]?.delete || false"
                                                    @change="(e: Event) => {
                                                        const target = e.target as HTMLInputElement;
                                                        const permiso = permisosForm[menu.menu];
                                                        if (permiso) {
                                                            permiso.delete = target.checked;
                                                        }
                                                    }"
                                                />
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>
            </template>
            <template #footer>
                <div class="d-flex justify-content-between w-100">
                    <button type="button" class="btn btn-default btn-sm pull-left" @click="closeSaveModal">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                    <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        :disabled="saving"
                        @click="handleSaveSubmit"
                    >
                        <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
                        {{ editingId ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>
            </template>
        </AppModal>

        <AppModal
            :open="showDeleteModal"
            title="Eliminar rol"
            size="sm"
            @close="closeDeleteModal"
        >
            <template #body>
                <p class="mb-0">
                    ¿Seguro que deseas eliminar el rol
                    <strong>{{ deleteTarget?.nombre }}</strong>? Esta acción no se puede deshacer.
                </p>
            </template>
            <template #footer>
                <div class="d-flex justify-content-between w-100">
                    <button type="button" class="btn btn-default btn-sm pull-left" @click="closeDeleteModal">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                    <button
                        type="button"
                        class="btn btn-danger btn-sm"
                        :disabled="deleting"
                        @click="handleDeleteConfirm"
                    >
                        <span v-if="deleting" class="spinner-border spinner-border-sm me-2"></span>
                        Eliminar
                    </button>
                </div>
            </template>
        </AppModal>
    </AuthenticatedLayout>
</template>

<style scoped>
.table-responsive {
    border: 1px solid #dee2e6;
}

.table thead th {
    position: sticky;
    top: 0;
    background-color: #f8f9fa;
    z-index: 10;
}

.table tbody tr[data-menu] {
    transition: background-color 0.2s;
}

.table tbody tr[data-menu]:hover {
    background-color: #f8f9fa;
}

.table tbody tr td.fw-bold {
    background-color: #e9ecef !important;
    font-size: 0.9rem;
}
</style>
