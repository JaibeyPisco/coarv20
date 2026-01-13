<script setup lang="ts">
import AuthenticatedLayout from '@/components/Layouts/AuthenticatedLayout.vue';
import { ref, reactive, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import apiClient from '@/api/axios';
// @ts-expect-error -- tabulator-tables no proporciona tipos ES module
import { TabulatorFull as Tabulator } from 'tabulator-tables';
import * as XLSX from 'xlsx';
import type { TipoPersonal } from '@/types/configuracion';
import AppModal from '@/components/Partial/AppModal.vue';
import TableCard from '@/components/Table/TableCard.vue';
import { notificacion } from '@/utils/notificacion';

const tableEl = ref<HTMLElement | null>(null);
const table = ref<any | null>(null);
const tiposPersonal = ref<TipoPersonal[]>([]);
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
    descripcion: '',
});
const showSaveModal = ref(false);
const showDeleteModal = ref(false);
const editingId = ref<number | null>(null);
const deleteTarget = ref<TipoPersonal | null>(null);
const saving = ref(false);
const deleting = ref(false);

const saveModalTitle = computed(() =>
    editingId.value ? 'Editar tipo de personal' : 'Nuevo tipo de personal',
);

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
        title: 'DESCRIPCIÓN',
        field: 'descripcion',
        width: 400,
        minWidth: 320,
        maxWidth: 500,
        formatter: (cell: any) => {
            const descripcion = cell.getValue() || '';
            if (!descripcion) return '—';
            
            return `<div style="max-width: 400px; word-wrap: break-word; overflow-wrap: break-word; white-space: normal; line-height: 1.4;">${descripcion}</div>`;
        },
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
        printHeader: '<h4 class="mb-3">Listado de tipos de personal</h4>',
        printFooter: '<small>Generado desde la intranet</small>',
        height: 'calc(100vh - 360px)',
        columnDefaults: {
            resizable: true,
        },
        ajaxURL: 'configuracion/tipo-personal',
        ajaxContentType: 'json',
        ajaxRequestFunc: async (url: string) => {
            const response = await apiClient.get(url);
            return response.data;
        },
        ajaxResponse: (_url: string, _params: any, response: any) => {
            const data: TipoPersonal[] = response?.data ?? [];
            tiposPersonal.value = data;
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
    const data = cell.getRow().getData() as TipoPersonal;

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
    await table.value.replaceData('configuracion/tipo-personal');
    applySearch(searchQuery.value);
    updateRecordSummary();
}

function applySearch(query: string) {
    const normalized = query.trim().toLowerCase();
    if (!table.value) return;

    if (!normalized) {
        table.value.clearFilter(true);
    } else {
        table.value.setFilter((rowData: TipoPersonal) => {
            const values = [rowData.nombre, rowData.descripcion ?? ''];
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
    const totalRows = table.value.getData().length || tiposPersonal.value.length;
    recordSummary.value = `Mostrando ${filteredRows} de ${totalRows} registros`;
}

function openCreateModal() {
    editingId.value = null;
    resetSaveForm();
    showSaveModal.value = true;
}

function openEditModal(tipoPersonal: TipoPersonal) {
    editingId.value = tipoPersonal.id;
    saveForm.nombre = tipoPersonal.nombre;
    saveForm.descripcion = tipoPersonal.descripcion ?? '';
    showSaveModal.value = true;
}

function openDeleteModal(tipoPersonal: TipoPersonal) {
    deleteTarget.value = tipoPersonal;
    showDeleteModal.value = true;
}

async function handleSaveSubmit() {
    if (!saveForm.nombre.trim()) {
        notificacion('El nombre del tipo de personal es obligatorio.', { type: 'danger', title: 'Validación' });
        return;
    }

    const payload = {
        nombre: saveForm.nombre.trim(),
        descripcion: saveForm.descripcion.trim() || null,
    };

    saving.value = true;

    try {
        if (editingId.value) {
            await apiClient.post(`/configuracion/tipo-personal/${editingId.value}`, payload);
            notificacion('Tipo de personal actualizado correctamente.', { type: 'success' });
        } else {
            await apiClient.post('/configuracion/tipo-personal', payload);
            notificacion('Tipo de personal registrado correctamente.', { type: 'success' });
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
        await apiClient.delete(`/configuracion/tipo-personal/${deleteTarget.value.id}`);
        notificacion('Tipo de personal eliminado correctamente.', { type: 'success' });
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
    table.value.download('xlsx', 'tipos-personal.xlsx', { sheetName: 'Tipos de Personal' });
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
    saveForm.descripcion = '';
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
                    <h1 class="h2 mb-1">Configuración / Tipos de Personal</h1>
                    <p class="text-secondary mb-0">
                        Gestiona los tipos de personal; crea, edita o elimina según necesidad.
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

        <div class="tipo-personal-page">
            <TableCard
                :loading="loading"
                :column-menu="columnMenu"
                :search-value="searchQuery"
                search-placeholder="Buscar tipo de personal..."
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
                        <label class="form-label required" for="tipo-nombre">Nombre</label>
                        <input
                            id="tipo-nombre"
                            v-model="saveForm.nombre"
                            type="text"
                            class="form-control"
                            maxlength="255"
                            placeholder="Nombre del tipo de personal"
                            required
                        />
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="tipo-descripcion">Descripción</label>
                        <textarea
                            id="tipo-descripcion"
                            v-model="saveForm.descripcion"
                            class="form-control"
                            rows="3"
                            maxlength="555"
                            placeholder="Describe brevemente el tipo de personal"
                        ></textarea>
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
            title="Eliminar tipo de personal"
            size="sm"
            @close="closeDeleteModal"
        >
            <template #body>
                <p class="mb-0">
                    ¿Seguro que deseas eliminar el tipo de personal
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
