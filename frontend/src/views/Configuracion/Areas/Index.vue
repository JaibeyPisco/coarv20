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
            <div class="d-flex align-center justify-space-between flex-wrap ga-3">
                <div>
                    <h1 class="text-h5 font-weight-bold mb-1">Configuración / Áreas</h1>
                    <p class="text-body-2 text-medium-emphasis mb-0">
                        Gestiona las áreas de la organización; activa, edita o elimina según
                        necesidad.
                    </p>
                </div>
                <v-btn
                    color="primary"
                    prepend-icon="mdi-plus"
                    @click="crudModal.openCreateModal"
                >
                    Nuevo
                </v-btn>
            </div>
        </template>

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
                <span class="text-caption">{{ table.recordSummary.value }}</span>
            </template>
            <template #footer-right>
                <span class="text-caption text-medium-emphasis">
                    Actualizado automáticamente al guardar cambios.
                </span>
            </template>
        </TableCard>

        <AppModal
            :open="crudModal.showSaveModal.value"
            :title="crudModal.saveModalTitle.value"
            @update:open="crudModal.showSaveModal.value = $event"
        >
            <template #body>
                <v-form>
                    <v-text-field
                        v-model="saveForm.nombre"
                        label="Nombre"
                        placeholder="Nombre del área"
                        :rules="[v => !!v || 'El nombre del área es obligatorio']"
                        maxlength="100"
                        counter
                        required
                        class="mb-3"
                    />
                    <v-textarea
                        v-model="saveForm.descripcion"
                        label="Descripción"
                        placeholder="Describe brevemente el área"
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
                        @click="crudModal.closeSaveModal"
                    >
                        Cancelar
                    </v-btn>
                    <v-btn
                        color="primary"
                        :loading="crudModal.saving.value"
                        :disabled="crudModal.saving.value"
                        @click="() => crudModal.handleSaveSubmit(saveForm, table.reloadTable)"
                    >
                        {{ crudModal.editingId.value ? 'Actualizar' : 'Guardar' }}
                    </v-btn>
                </div>
            </template>
        </AppModal>

        <AppModal
            :open="crudModal.showDeleteModal.value"
            title="Eliminar área"
            size="sm"
            @update:open="crudModal.showDeleteModal.value = $event"
        >
            <template #body>
                <p class="mb-0">
                    ¿Seguro que deseas eliminar el área
                    <strong>{{ crudModal.deleteTarget.value?.nombre }}</strong
                    >? Esta acción no se puede deshacer.
                </p>
            </template>
            <template #footer>
                <div class="d-flex justify-space-between w-100">
                    <v-btn
                        variant="text"
                        @click="crudModal.closeDeleteModal"
                    >
                        Cancelar
                    </v-btn>
                    <v-btn
                        color="error"
                        :loading="crudModal.deleting.value"
                        :disabled="crudModal.deleting.value"
                        @click="() => crudModal.handleDeleteConfirm(table.reloadTable)"
                    >
                        Eliminar
                    </v-btn>
                </div>
            </template>
        </AppModal>
    </AuthenticatedLayout>
</template>
