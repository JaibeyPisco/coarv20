<script setup lang="ts">
import AuthenticatedLayout from '@/components/Layouts/AuthenticatedLayout.vue';
import { ref, reactive, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import apiClient from '@/api/axios';
// @ts-expect-error -- tabulator-tables no proporciona tipos ES module
import { TabulatorFull as Tabulator } from 'tabulator-tables';
import * as XLSX from 'xlsx';
import type { Lugar } from '@/types/configuracion';
import AppModal from '@/components/Partial/AppModal.vue';
import TableCard from '@/components/Table/TableCard.vue';
import { notificacion } from '@/utils/notificacion';

const tableEl = ref<HTMLElement | null>(null);
const table = ref<any | null>(null);
const lugares = ref<Lugar[]>([]);
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
    referencia: '',
});
const showSaveModal = ref(false);
const showDeleteModal = ref(false);
const editingId = ref<number | null>(null);
const deleteTarget = ref<Lugar | null>(null);
const saving = ref(false);
const deleting = ref(false);

const saveModalTitle = computed(() =>
    editingId.value ? 'Editar lugar' : 'Nuevo lugar',
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
        minWidth: 220,
        headerSort: true,
        formatter: 'plaintext',
    },
    {
        title: 'REFERENCIA',
        field: 'referencia',
        width: 400,
        minWidth: 320,
        maxWidth: 500,
        formatter: (cell: any) => {
            const referencia = cell.getValue() || '';
            if (!referencia) return '—';
            
            return `<div style="max-width: 400px; word-wrap: break-word; overflow-wrap: break-word; white-space: normal; line-height: 1.4;">${referencia}</div>`;
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
        printHeader: '<h4 class="mb-3">Listado de lugares</h4>',
        printFooter: '<small>Generado desde la intranet</small>',
        height: 'calc(100vh - 360px)',
        columnDefaults: {
            resizable: true,
        },
        ajaxURL: 'configuracion/lugares',
        ajaxContentType: 'json',
        ajaxRequestFunc: async (url: string) => {
            const response = await apiClient.get(url);
            return response.data;
        },
        ajaxResponse: (_url: string, _params: any, response: any) => {
            const data: Lugar[] = response?.data ?? [];
            lugares.value = data;
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
    const data = cell.getRow().getData() as Lugar;

    if (action === 'edit') {
        openEditModal(data);
    }

    if (action === 'delete') {
        openDeleteModal(data);
    }
}

function applySearch(query: string) {
    const normalized = query.trim().toLowerCase();
    if (!table.value) return;

    if (!normalized) {
        table.value.clearFilter(true);
    } else {
        table.value.setFilter((rowData: Lugar) => {
            const nombre = rowData.nombre?.toLowerCase() || '';
            const referencia = rowData.referencia?.toLowerCase() || '';
            return nombre.includes(normalized) || referencia.includes(normalized);
        });
    }

    updateRecordSummary();
}

async function reloadTable() {
    if (!table.value) return;
    loading.value = true;
    await table.value.replaceData('configuracion/lugares');
    updateRecordSummary();
}

function updateRecordSummary() {
    if (!table.value) {
        recordSummary.value = 'Mostrando 0 registros';
        return;
    }

    const filteredRows = table.value.getRows(true).length;
    const totalRows = table.value.getData().length || lugares.value.length;
    recordSummary.value = `Mostrando ${filteredRows} de ${totalRows} registros`;
}

function openCreateModal() {
    editingId.value = null;
    resetSaveForm();
    showSaveModal.value = true;
}

function openEditModal(lugar: Lugar) {
    editingId.value = lugar.id;
    saveForm.nombre = lugar.nombre;
    saveForm.referencia = lugar.referencia ?? '';
    showSaveModal.value = true;
}

function openDeleteModal(lugar: Lugar) {
    deleteTarget.value = lugar;
    showDeleteModal.value = true;
}

async function handleSaveSubmit() {
    if (!saveForm.nombre.trim()) {
        notificacion('El nombre del lugar es obligatorio.', { type: 'danger', title: 'Validación' });
        return;
    }

    const payload = {
        nombre: saveForm.nombre.trim(),
        referencia: saveForm.referencia.trim() || null,
    };

    saving.value = true;

    try {
        if (editingId.value) {
            await apiClient.post(`/configuracion/lugares/${editingId.value}`, payload);
            notificacion('Lugar actualizado correctamente.', { type: 'success' });
        } else {
            await apiClient.post('/configuracion/lugares', payload);
            notificacion('Lugar registrado correctamente.', { type: 'success' });
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
        await apiClient.delete(`/configuracion/lugares/${deleteTarget.value.id}`);
        notificacion('Lugar eliminado correctamente.', { type: 'success' });
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
    table.value.download('xlsx', 'lugares.xlsx', { sheetName: 'Lugares' });
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
    saveForm.referencia = '';
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
            <div class="d-flex align-center justify-space-between flex-wrap ga-3">
                <div>
                    <h1 class="text-h5 font-weight-bold mb-1">Configuración / Lugares</h1>
                    <p class="text-body-2 text-medium-emphasis mb-0">
                        Administra los lugares disponibles; crea, edita o elimina según necesidad.
                    </p>
                </div>
                <v-btn
                    color="primary"
                    prepend-icon="mdi-plus"
                    @click="openCreateModal"
                >
                    Nuevo
                </v-btn>
            </div>
        </template>

        <TableCard
            :loading="loading"
            :column-menu="columnMenu"
            :search-value="searchQuery"
            search-placeholder="Buscar lugar..."
            @print="printTable"
            @export="downloadExcel"
            @toggle-column="toggleColumnVisibility"
            @update:search="updateSearchValue"
        >
            <div ref="tableEl" class="tabulator-wrapper"></div>

            <template #footer-left>
                <span class="text-caption">{{ recordSummary }}</span>
            </template>
            <template #footer-right>
                <span class="text-caption text-medium-emphasis">
                    Actualizado automáticamente al guardar cambios.
                </span>
            </template>
        </TableCard>

        <AppModal
            :open="showSaveModal"
            :title="saveModalTitle"
            @update:open="showSaveModal = $event"
        >
            <template #body>
                <v-form>
                    <v-text-field
                        v-model="saveForm.nombre"
                        label="Nombre"
                        placeholder="Nombre del lugar"
                        :rules="[v => !!v || 'El nombre del lugar es obligatorio']"
                        maxlength="100"
                        counter
                        required
                        class="mb-3"
                    />
                    <v-textarea
                        v-model="saveForm.referencia"
                        label="Referencia"
                        placeholder="Describe brevemente el lugar"
                        rows="3"
                        maxlength="255"
                        counter
                        auto-grow
                    />
                </v-form>
            </template>
            <template #footer>
                <div class="d-flex justify-space-between w-100">
                    <v-btn
                        variant="text"
                        @click="closeSaveModal"
                    >
                        Cancelar
                    </v-btn>
                    <v-btn
                        color="primary"
                        :loading="saving"
                        :disabled="saving"
                        @click="handleSaveSubmit"
                    >
                        {{ editingId ? 'Actualizar' : 'Guardar' }}
                    </v-btn>
                </div>
            </template>
        </AppModal>

        <AppModal
            :open="showDeleteModal"
            title="Eliminar lugar"
            size="sm"
            @update:open="showDeleteModal = $event"
        >
            <template #body>
                <p class="mb-0">
                    ¿Seguro que deseas eliminar el lugar
                    <strong>{{ deleteTarget?.nombre }}</strong>? Esta acción no se puede deshacer.
                </p>
            </template>
            <template #footer>
                <div class="d-flex justify-space-between w-100">
                    <v-btn
                        variant="text"
                        @click="closeDeleteModal"
                    >
                        Cancelar
                    </v-btn>
                    <v-btn
                        color="error"
                        :loading="deleting"
                        :disabled="deleting"
                        @click="handleDeleteConfirm"
                    >
                        Eliminar
                    </v-btn>
                </div>
            </template>
        </AppModal>
    </AuthenticatedLayout>
</template>
