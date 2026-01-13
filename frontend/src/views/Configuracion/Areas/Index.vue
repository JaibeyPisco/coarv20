<script setup lang="ts">
import AuthenticatedLayout from '@/components/Layouts/AuthenticatedLayout.vue';
import { ref, reactive, onMounted, onBeforeUnmount, watch } from 'vue';
import * as XLSX from 'xlsx';
import type { Area } from '@/types/configuracion';
import type { CreateAreaDto } from '@/types/configuracion';
import AppModal from '@/components/Partial/AppModal.vue';
import TableCard from '@/components/Table/TableCard.vue';
import { useTabulatorTable } from '@/composables/useTabulatorTable';
import { useCrudModal } from '@/composables/useCrudModal';
import { useTableActions } from '@/composables/useTableActions';
import apiClient from '@/api/axios';
import type { UpdateAreaDto } from '@/types/configuracion';
import { createActionColumn } from '@/utils/tableHelpers';

// Refs
const tableEl = ref<HTMLElement | null>(null);
const searchQuery = ref('');

// Form
const saveForm = reactive({
    nombre: '',
    descripcion: '',
});

// Columnas de la tabla
const { handleActionCellClick, handleGlobalClick } = useTableActions();

const columns = [
    createActionColumn({}, (e, cell) => {
        handleActionCellClick(e, cell, {
            edit: (data: Area) => crudModal.openEditModal(data),
            delete: (data: Area) => crudModal.openDeleteModal(data),
        });
    }),
    {
        title: 'NOMBRE',
        field: 'nombre',
        minWidth: 220,
        headerSort: true,
        formatter: 'plaintext',
    },
    {
        title: 'DESCRIPCIÓN',
        field: 'descripcion',
        width: 400,
        minWidth: 320,
        maxWidth: 500,
        formatter: (cell: string) => {
            console.log(cell);

            const descripcion = cell.getValue() || '';
            if (!descripcion) return '—';
            return `<div style="max-width: 400px; word-wrap: break-word; overflow-wrap: break-word; white-space: normal; line-height: 1.4;">${descripcion}</div>`;
        },
    },
    {
        title: 'ESTADO',
        field: 'estado',
        width: 120,
        headerHozAlign: 'center' as const,
        hozAlign: 'center' as const,
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

            const { label, className } = statusConfig[value] ?? {
                label: 'SIN ESTADO',
                className: 'badge rounded-pill px-3 bg-gray-lt text-secondary fw-semibold',
            };

            return `<span class="${className}">${label}</span>`;
        },
    },
];

// Composable de tabla
const table = useTabulatorTable<Area>({
    tableEl,
    columns,
    ajaxURL: '/configuracion/areas',
    printHeader: '<h4 class="mb-3">Listado de áreas</h4>',
    printFooter: '<small>Generado desde la intranet</small>',
});

// Composable de CRUD
const crudModal = useCrudModal<Area>({
    entityName: 'área',
    getPayload: (form): CreateAreaDto => ({
        nombre: form.nombre.trim(),
        descripcion: form.descripcion.trim() || null,
    }),
    validateForm: (form) => {
        if (!form.nombre.trim()) {
            return 'El nombre del área es obligatorio.';
        }
        return null;
    },
    onCreate: async (data: CreateAreaDto) => {
        const response = await apiClient.post<Area>('/configuracion/areas', data);
        return response.data;
    },
    onUpdate: async (id: number, data: CreateAreaDto) => {
        const response = await apiClient.post<Area>(`/configuracion/areas/${id}`, {
            ...data,
            id,
        } as UpdateAreaDto);
        return response.data;
    },
    onDeleteCustom: async (id: number) => {
        await apiClient.delete(`/configuracion/areas/${id}`);
    },
    onEdit: (area: Area) => {
        saveForm.nombre = area.nombre;
        saveForm.descripcion = area.descripcion ?? '';
    },
    resetForm: () => {
        saveForm.nombre = '';
        saveForm.descripcion = '';
    },
});

// Funciones
const updateSearchValue = (value: string) => {
    searchQuery.value = value;
};

const downloadExcel = () => {
    table.downloadExcel('areas.xlsx', 'Áreas');
};

const toggleColumnVisibility = (field: string) => {
    table.toggleColumnVisibility(field);
};

// Watchers
watch(searchQuery, (value) => {
    table.applySearch(value, ['nombre', 'descripcion']);
});

// Lifecycle
onMounted(async () => {
    // @ts-ignore expose for Tabulator download module
    (window as any).XLSX = XLSX;
    await table.initializeTable();
    document.addEventListener('click', handleGlobalClick);
});

onBeforeUnmount(() => {
    table.destroy();
    document.removeEventListener('click', handleGlobalClick);
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="h2 mb-1">Configuración / Áreas</h1>
                    <p class="text-secondary mb-0">
                        Gestiona las áreas de la organización; activa, edita o elimina según
                        necesidad.
                    </p>
                </div>
                <div class="btn-group">
                    <button
                        type="button"
                        class="btn btn-primary d-flex align-items-center gap-2 btn-md"
                        @click="crudModal.openCreateModal"
                        aria-label="Crear nueva área"
                    >
                        <i class="ti ti-plus" aria-hidden="true"></i>
                        Nuevo
                    </button>
                </div>
            </div>
        </template>

        <div class="">
            <TableCard
                :loading="table.loading.value"
                :column-menu="table.columnMenu.value"
                :search-value="searchQuery"
                search-placeholder="Buscar área..."
                @print="table.printTable"
                @export="downloadExcel"
                @toggle-column="toggleColumnVisibility"
                @update:search="updateSearchValue"
            >
                <div ref="tableEl" class="tabulator-wrapper"></div>

                <template #footer-left>
                    <span>{{ table.recordSummary.value }}</span>
                </template>
                <template #footer-right>
                    <span>Actualizado automáticamente al guardar cambios.</span>
                </template>
            </TableCard>
        </div>

        <AppModal
            :open="crudModal.showSaveModal.value"
            :title="crudModal.saveModalTitle.value"
            @close="crudModal.closeSaveModal"
        >
            <template #body>
                <form class="space-y-3" @submit.prevent role="form" aria-label="Formulario de área">
                    <div class="mb-3">
                        <label class="form-label required" for="area-nombre">
                            Nombre
                            <span class="visually-hidden">(campo obligatorio)</span>
                        </label>
                        <input
                            id="area-nombre"
                            v-model="saveForm.nombre"
                            type="text"
                            class="form-control"
                            maxlength="100"
                            placeholder="Nombre del área"
                            aria-required="true"
                            aria-describedby="area-nombre-help"
                        />
                        <small id="area-nombre-help" class="form-text text-muted visually-hidden">
                            Ingrese el nombre del área (máximo 100 caracteres)
                        </small>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="area-descripcion">Descripción</label>
                        <textarea
                            id="area-descripcion"
                            v-model="saveForm.descripcion"
                            class="form-control"
                            rows="3"
                            maxlength="255"
                            placeholder="Describe brevemente el área"
                            aria-describedby="area-descripcion-help"
                        ></textarea>
                        <small
                            id="area-descripcion-help"
                            class="form-text text-muted visually-hidden"
                        >
                            Descripción opcional del área (máximo 255 caracteres)
                        </small>
                    </div>
                </form>
            </template>
            <template #footer>
                <div class="d-flex justify-content-between w-100">
                    <button
                        type="button"
                        class="btn btn-default btn-sm pull-left"
                        @click="crudModal.closeSaveModal"
                        aria-label="Cancelar y cerrar modal"
                    >
                        <i class="fa fa-times" aria-hidden="true"></i> Cancelar
                    </button>
                    <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        :disabled="crudModal.saving.value"
                        :aria-label="
                            crudModal.editingId.value ? 'Actualizar área' : 'Guardar nueva área'
                        "
                        @click="() => crudModal.handleSaveSubmit(saveForm, table.reloadTable)"
                    >
                        <span
                            v-if="crudModal.saving.value"
                            class="spinner-border spinner-border-sm me-2"
                            role="status"
                            aria-hidden="true"
                        ></span>
                        {{ crudModal.editingId.value ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>
            </template>
        </AppModal>

        <AppModal
            :open="crudModal.showDeleteModal.value"
            title="Eliminar área"
            size="sm"
            @close="crudModal.closeDeleteModal"
        >
            <template #body>
                <p class="mb-0">
                    ¿Seguro que deseas eliminar el área
                    <strong>{{ crudModal.deleteTarget.value?.nombre }}</strong
                    >? Esta acción no se puede deshacer.
                </p>
            </template>
            <template #footer>
                <div class="d-flex justify-content-between w-100">
                    <button
                        type="button"
                        class="btn btn-default btn-sm pull-left"
                        @click="crudModal.closeDeleteModal"
                        aria-label="Cancelar eliminación"
                    >
                        <i class="fa fa-times" aria-hidden="true"></i> Cancelar
                    </button>
                    <button
                        type="button"
                        class="btn btn-danger btn-sm"
                        :disabled="crudModal.deleting.value"
                        aria-label="Confirmar eliminación del área"
                        @click="() => crudModal.handleDeleteConfirm(table.reloadTable)"
                    >
                        <span
                            v-if="crudModal.deleting.value"
                            class="spinner-border spinner-border-sm me-2"
                            role="status"
                            aria-hidden="true"
                        ></span>
                        Eliminar
                    </button>
                </div>
            </template>
        </AppModal>
    </AuthenticatedLayout>
</template>
