<script setup lang="ts">
import AuthenticatedLayout from '@/components/Layouts/AuthenticatedLayout.vue';
import { ref, reactive, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import apiClient from '@/api/axios';
// @ts-expect-error -- tabulator-tables no proporciona tipos ES module
import { TabulatorFull as Tabulator } from 'tabulator-tables';
import * as XLSX from 'xlsx';
import type { EstadoMonitoreo } from '@/types/configuracion';
import AppModal from '@/components/Partial/AppModal.vue';
import TableCard from '@/components/Table/TableCard.vue';
import { notificacion } from '@/utils/notificacion';

const tableEl = ref<HTMLElement | null>(null);
const table = ref<any | null>(null);
const estadosMonitoreo = ref<EstadoMonitoreo[]>([]);
const loading = ref(false);
const searchQuery = ref('');
const columnMenu = ref<{ title: string; field: string; visible: boolean }[]>([]);
const recordSummary = ref('Mostrando 0 registros');

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
    tipo: '',
    color_bg: '#000000',
});
const showSaveModal = ref(false);
const showDeleteModal = ref(false);
const editingId = ref<number | null>(null);
const deleteTarget = ref<EstadoMonitoreo | null>(null);
const saving = ref(false);
const deleting = ref(false);

const saveModalTitle = computed(() =>
    editingId.value ? 'Editar estado de monitoreo' : 'Nuevo estado de monitoreo',
);

// Función para calcular el color del texto basado en el color de fondo
function calculateTextColor(hexColor: string): string {
    const hex = hexColor.replace('#', '');
    const r = parseInt(hex.substring(0, 2), 16);
    const g = parseInt(hex.substring(2, 4), 16);
    const b = parseInt(hex.substring(4, 6), 16);
    const brightness = (r * 299 + g * 587 + b * 114) / 1000;
    return brightness < 128 ? '#ffffff' : '#000000';
}

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
        title: 'TIPO',
        field: 'tipo',
        width: 150,
        headerHozAlign: 'center',
        hozAlign: 'center',
        formatter: (cell: any) => {
            const value = cell.getValue();
            const config: Record<string, { label: string; className: string }> = {
                INCIDENCIA: {
                    label: 'INCIDENCIA',
                    className: 'badge rounded-pill px-3 bg-blue-lt text-blue fw-semibold',
                },
                DERIVACION: {
                    label: 'DERIVACIÓN',
                    className: 'badge rounded-pill px-3 bg-orange-lt text-orange fw-semibold',
                },
            };

            const { label, className } = config[value] ?? {
                label: value || '—',
                className: 'badge rounded-pill px-3 bg-gray-lt text-secondary fw-semibold',
            };

            return `<span class="${className}">${label}</span>`;
        },
    },
    {
        title: 'NOMBRE',
        field: 'nombre',
        minWidth: 250,
        headerSort: true,
        formatter: (cell: any) => {
            const data = cell.getRow().getData() as EstadoMonitoreo;
            const nombre = data.nombre || '';
            const colorBg = data.color_bg || '#000000';
            const colorText = data.color_text || calculateTextColor(colorBg);

            if (!nombre) return '—';

            return `<span class="badge rounded-pill px-3 fw-semibold" style="background-color: ${colorBg}; color: ${colorText};">${nombre}</span>`;
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
        printHeader: '<h4 class="mb-3">Listado de estados de monitoreo</h4>',
        printFooter: '<small>Generado desde la intranet</small>',
        height: 'calc(100vh - 360px)',
        columnDefaults: {
            resizable: true,
        },
        ajaxURL: 'configuracion/estado-monitoreo',
        ajaxContentType: 'json',
        ajaxRequestFunc: async (url: string) => {
            const response = await apiClient.get(url);
            return response.data;
        },
        ajaxResponse: (_url: string, _params: any, response: any) => {
            const data: EstadoMonitoreo[] = response?.data ?? [];
            estadosMonitoreo.value = data;
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
    const data = cell.getRow().getData() as EstadoMonitoreo;

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
    await table.value.replaceData('configuracion/estado-monitoreo');
    applySearch(searchQuery.value);
    updateRecordSummary();
}

function applySearch(query: string) {
    const normalized = query.trim().toLowerCase();
    if (!table.value) return;

    if (!normalized) {
        table.value.clearFilter(true);
    } else {
        table.value.setFilter((rowData: EstadoMonitoreo) => {
            const values = [rowData.nombre, rowData.tipo];
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
    const totalRows = table.value.getData().length || estadosMonitoreo.value.length;
    recordSummary.value = `Mostrando ${filteredRows} de ${totalRows} registros`;
}

function openCreateModal() {
    editingId.value = null;
    resetSaveForm();
    showSaveModal.value = true;
}

function openEditModal(estadoMonitoreo: EstadoMonitoreo) {
    editingId.value = estadoMonitoreo.id;
    saveForm.nombre = estadoMonitoreo.nombre;
    saveForm.tipo = estadoMonitoreo.tipo;
    saveForm.color_bg = estadoMonitoreo.color_bg || '#000000';
    showSaveModal.value = true;
}

function openDeleteModal(estadoMonitoreo: EstadoMonitoreo) {
    deleteTarget.value = estadoMonitoreo;
    showDeleteModal.value = true;
}

async function handleSaveSubmit() {
    if (!saveForm.nombre.trim()) {
        notificacion('El nombre del estado de monitoreo es obligatorio.', { type: 'danger', title: 'Validación' });
        return;
    }

    if (!saveForm.tipo) {
        notificacion('El tipo es obligatorio.', { type: 'danger', title: 'Validación' });
        return;
    }

    if (!saveForm.color_bg) {
        notificacion('El color de fondo es obligatorio.', { type: 'danger', title: 'Validación' });
        return;
    }

    const payload = {
        nombre: saveForm.nombre.trim(),
        tipo: saveForm.tipo,
        color_bg: saveForm.color_bg,
    };

    saving.value = true;

    try {
        if (editingId.value) {
            await apiClient.post(`/configuracion/estado-monitoreo/${editingId.value}`, payload);
            notificacion('Estado de monitoreo actualizado correctamente.', { type: 'success' });
        } else {
            await apiClient.post('/configuracion/estado-monitoreo', payload);
            notificacion('Estado de monitoreo registrado correctamente.', { type: 'success' });
        }

        showSaveModal.value = false;
        await reloadTable();
    } catch (error: any) {
        const message = error.response?.data?.message || 'Ocurrió un inconveniente al guardar el registro.';
        notificacion(message, { type: 'danger', title: 'Error' });
    } finally {
        saving.value = false;
    }
}

async function handleDeleteConfirm() {
    if (!deleteTarget.value) return;

    deleting.value = true;

    try {
        await apiClient.delete(`/configuracion/estado-monitoreo/${deleteTarget.value.id}`);
        notificacion('Estado de monitoreo eliminado correctamente.', { type: 'success' });
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
    table.value.download('xlsx', 'estados-monitoreo.xlsx', { sheetName: 'Estados de Monitoreo' });
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

function resetSaveForm() {
    saveForm.nombre = '';
    saveForm.tipo = '';
    saveForm.color_bg = '#000000';
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

const previewTextColor = computed(() => {
    return calculateTextColor(saveForm.color_bg);
});

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
                    <h1 class="h2 mb-1">Configuración / Estados de Monitoreo</h1>
                    <p class="text-secondary mb-0">
                        Gestiona los estados de monitoreo; crea, edita o elimina según necesidad.
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

        <div class="estado-monitoreo-page">
            <TableCard
                :loading="loading"
                :column-menu="columnMenu"
                :search-value="searchQuery"
                search-placeholder="Buscar estado de monitoreo..."
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
            @close="closeSaveModal"
        >
            <template #body>
                <form class="space-y-3" @submit.prevent>
                    <div class="mb-3">
                        <label class="form-label required" for="estado-tipo">Tipo</label>
                        <select
                            id="estado-tipo"
                            v-model="saveForm.tipo"
                            class="form-select"
                            required
                        >
                            <option value="">Seleccionar...</option>
                            <option value="INCIDENCIA">INCIDENCIA</option>
                            <option value="DERIVACION">DERIVACIÓN</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required" for="estado-nombre">Nombre</label>
                        <input
                            id="estado-nombre"
                            v-model="saveForm.nombre"
                            type="text"
                            class="form-control"
                            maxlength="100"
                            placeholder="Nombre del estado de monitoreo"
                            required
                        />
                    </div>
                    <div class="mb-0">
                        <label class="form-label required" for="estado-color">Color de fondo</label>
                        <div class="d-flex align-items-center gap-3">
                            <input
                                id="estado-color"
                                v-model="saveForm.color_bg"
                                type="color"
                                class="form-control form-control-color"
                                style="width: 80px; height: 40px; cursor: pointer;"
                                required
                            />
                            <div class="flex-grow-1">
                                <input
                                    v-model="saveForm.color_bg"
                                    type="text"
                                    class="form-control"
                                    placeholder="#000000"
                                    pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$"
                                    required
                                />
                                <small class="text-muted">Ingresa un color hexadecimal o usa el selector</small>
                            </div>
                        </div>
                        <div class="mt-2">
                            <span
                                class="badge rounded-pill px-3 fw-semibold"
                                :style="`background-color: ${saveForm.color_bg}; color: ${previewTextColor};`"
                            >
                                Vista previa: {{ saveForm.nombre || 'Nombre' }}
                            </span>
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
            title="Eliminar estado de monitoreo"
            size="sm"
            @close="closeDeleteModal"
        >
            <template #body>
                <p class="mb-0">
                    ¿Seguro que deseas eliminar el estado de monitoreo
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
