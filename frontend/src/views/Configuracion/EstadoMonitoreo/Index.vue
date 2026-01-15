<script setup lang="ts">
import AuthenticatedLayout from '@/components/Layouts/AuthenticatedLayout.vue';
import { ref, reactive, computed, onMounted } from 'vue';
import * as XLSX from 'xlsx';
import type { EstadoMonitoreo } from '@/types/configuracion';
import type { CreateEstadoMonitoreoDto } from '@/types/configuracion';
import AppModal from '@/components/Partial/AppModal.vue';
import VDataTableCard from '@/components/Table/VDataTableCard.vue';
import { useVuetifyTable } from '@/composables/useVuetifyTable';
import { useCrudModal } from '@/composables/useCrudModal';
import apiClient from '@/api/axios';
import {
    formatTipoEstadoMonitoreoChip,
    calculateTextColor,
} from '@/utils/vuetifyTableHelpers';

// Form
const saveForm = reactive({
    nombre: '',
    tipo: '',
    color_bg: '#000000',
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
        title: 'TIPO',
        key: 'tipo',
        sortable: true,
        align: 'center' as const,
        width: '150px',
    },
    {
        title: 'NOMBRE',
        key: 'nombre',
        sortable: true,
    },
];

// Composable de tabla
const table = useVuetifyTable<EstadoMonitoreo>({
    apiURL: '/configuracion/estado-monitoreo',
    searchFields: ['nombre', 'tipo'],
    serverSidePagination: false,
    serverSideSorting: false,
    serverSideSearch: false,
});

// Inicializar menú de columnas
table.updateColumnMenu(headers);

// Computed para el color del texto en el preview
const previewTextColor = computed(() => {
    return calculateTextColor(saveForm.color_bg);
});

// Composable de CRUD
const crudModal = useCrudModal<EstadoMonitoreo>({
    entityName: 'estado de monitoreo',
    getPayload: (form): CreateEstadoMonitoreoDto => ({
        nombre: form.nombre.trim(),
        tipo: form.tipo ? String(form.tipo) : '',
        color_bg: form.color_bg ? String(form.color_bg) : '#000000',
    }),
    validateForm: (form) => {
        if (!form.nombre.trim()) {
            return 'El nombre del estado de monitoreo es obligatorio.';
        }
        if (!form.tipo) {
            return 'El tipo es obligatorio.';
        }
        if (!form.color_bg) {
            return 'El color de fondo es obligatorio.';
        }
        return null;
    },
    onCreate: async (data: CreateEstadoMonitoreoDto) => {
        const response = await apiClient.post<EstadoMonitoreo>('/configuracion/estado-monitoreo', data);
        return response.data;
    },
    onUpdate: async (id: number, data: CreateEstadoMonitoreoDto) => {
        const response = await apiClient.post<EstadoMonitoreo>(`/configuracion/estado-monitoreo/${id}`, {
            ...data,
            id,
        });
        return response.data;
    },
    onDeleteCustom: async (id: number) => {
        await apiClient.delete(`/configuracion/estado-monitoreo/${id}`);
    },
    onEdit: (estado: EstadoMonitoreo) => {
        saveForm.nombre = estado.nombre;
        saveForm.tipo = estado.tipo;
        saveForm.color_bg = estado.color_bg || '#000000';
    },
    resetForm: () => {
        saveForm.nombre = '';
        saveForm.tipo = '';
        saveForm.color_bg = '#000000';
    },
});

// Funciones
const updateSearchValue = (value: string) => {
    table.searchQuery.value = value;
    table.applySearch(value);
};

const downloadExcel = () => {
    table.downloadExcel('estados_monitoreo.xlsx', 'Estados de Monitoreo');
};

const toggleColumnVisibility = (key: string) => {
    table.toggleColumnVisibility(key);
};

// Lifecycle
onMounted(async () => {
    (window as any).XLSX = XLSX;
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
                            <h1 class="text-h5 font-weight-bold mb-2">Estados de Monitoreo</h1>
                            <p class="text-body-2 text-medium-emphasis mb-0">
                                Gestiona los estados de monitoreo; crea, edita o elimina según necesidad.
                            </p>
                        </div>
                        <v-btn
                            color="primary"
                            prepend-icon="mdi-plus"
                            variant="flat"
                            size="default"
                            @click="crudModal.openCreateModal"
                            aria-label="Crear nuevo estado de monitoreo"
                            class="text-none"
                        >
                            Nuevo Estado
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
                    search-placeholder="Buscar estado de monitoreo..."
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
                        density="compact"
                        fixed-header
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

                        <template #item.tipo="{ value }">
                            <v-chip
                                :color="formatTipoEstadoMonitoreoChip(value).color"
                                size="small"
                                variant="flat"
                                class="text-uppercase font-weight-medium"
                            >
                                {{ formatTipoEstadoMonitoreoChip(value).label }}
                            </v-chip>
                        </template>

                        <template #item.nombre="{ item }">
                            <v-chip
                                :style="`background-color: ${item.color_bg || '#000000'}; color: ${item.color_text || calculateTextColor(item.color_bg || '#000000')};`"
                                size="small"
                                variant="flat"
                                class="text-uppercase font-weight-medium"
                            >
                                {{ item.nombre || '—' }}
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
                        <v-row>
                            <!-- Campo Tipo -->
                            <v-col cols="12">
                                <v-select
                                    v-model="saveForm.tipo"
                                    label="Tipo"
                                    :items="[
                                        { title: 'INCIDENCIA', value: 'INCIDENCIA' },
                                        { title: 'DERIVACIÓN', value: 'DERIVACION' },
                                    ]"
                                    :rules="[(v) => !!v || 'El tipo es obligatorio']"
                                    required
                                    variant="outlined"
                                    density="compact"
                                />
                            </v-col>

                            <!-- Campo Nombre -->
                            <v-col cols="12">
                                <v-text-field
                                    v-model="saveForm.nombre"
                                    label="Nombre del Estado"
                                    :rules="[(v) => !!v || 'El nombre es obligatorio']"
                                    counter="100"
                                    maxlength="100"
                                    placeholder="Ingrese el nombre del estado de monitoreo"
                                    required
                                    variant="outlined"
                                    density="compact"
                                />
                            </v-col>

                            <!-- Sección Color de Fondo -->
                            <v-col cols="12">
                                <label class="text-body-2 font-weight-medium mb-2 d-block">Color de Fondo</label>
                                <v-sheet
                                    variant="outlined"
                                    class="pa-4"
                                    rounded="md"
                                    color="surface"
                                >
                                    <v-row>
                                        <!-- Selector de Color y Campo Hexadecimal -->
                                        <v-col cols="12">
                                            <div class="d-flex align-center ga-3">
                                                <div class="d-flex align-center">
                                                    <input
                                                        v-model="saveForm.color_bg"
                                                        type="color"
                                                        class="color-picker-input"
                                                        required
                                                        aria-label="Selector de color"
                                                    />
                                                </div>
                                                <v-text-field
                                                    v-model="saveForm.color_bg"
                                                    label="Código Hexadecimal"
                                                    placeholder="#000000"
                                                    :rules="[
                                                        (v) => !!v || 'El color es obligatorio',
                                                        (v) => /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(v) || 'Formato hexadecimal inválido',
                                                    ]"
                                                    density="compact"
                                                    hide-details="auto"
                                                    variant="outlined"
                                                    required
                                                    class="flex-grow-1"
                                                />
                                            </div>
                                        </v-col>

                                        <!-- Texto de Ayuda -->
                                        <v-col cols="12">
                                            <p class="text-caption text-medium-emphasis mb-0">
                                                Ingresa un color hexadecimal o usa el selector de color
                                            </p>
                                        </v-col>

                                        <!-- Vista Previa -->
                                        <v-col cols="12">
                                            <v-sheet
                                                variant="outlined"
                                                class="pa-3"
                                                rounded="md"
                                                color="surface-variant"
                                            >
                                                <label class="text-caption text-medium-emphasis mb-2 d-block">Vista Previa</label>
                                                <div class="d-flex justify-center">
                                                    <v-chip
                                                        :style="`background-color: ${saveForm.color_bg}; color: ${previewTextColor};`"
                                                        class="text-uppercase font-weight-medium px-6 py-3"
                                                        size="default"
                                                        variant="flat"
                                                    >
                                                        {{ saveForm.nombre || 'Nombre del Estado' }}
                                                    </v-chip>
                                                </div>
                                            </v-sheet>
                                        </v-col>
                                    </v-row>
                                </v-sheet>
                            </v-col>
                        </v-row>
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
                title="Eliminar estado de monitoreo"
                size="sm"
            >
                <template #body>
                    <v-container fluid class="pa-4">
                        <div class="text-center mb-4">
                            <v-icon icon="mdi-alert-circle" size="64" color="error" />
                        </div>
                        <p class="text-body-1 text-center">
                            ¿Está seguro que desea eliminar el estado de monitoreo
                            <strong>{{ crudModal.deleteTarget.value?.nombre }}</strong>?
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
.color-picker-input {
    width: 80px;
    height: 40px;
    cursor: pointer;
    border: 1px solid rgba(0, 0, 0, 0.12);
    border-radius: 8px;
    background: none;
    padding: 0;
}

.color-picker-input::-webkit-color-swatch-wrapper {
    padding: 0;
    border-radius: 8px;
}

.color-picker-input::-webkit-color-swatch {
    border: none;
    border-radius: 8px;
}

.color-picker-input::-moz-color-swatch {
    border: none;
    border-radius: 8px;
}
</style>
