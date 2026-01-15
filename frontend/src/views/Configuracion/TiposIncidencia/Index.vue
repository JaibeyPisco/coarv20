<script setup lang="ts">
import AuthenticatedLayout from '@/components/Layouts/AuthenticatedLayout.vue';
import { ref, reactive, onMounted } from 'vue';
import * as XLSX from 'xlsx';
import type { TiposIncidencia } from '@/types/configuracion';
import type { CreateTiposIncidenciaDto } from '@/types/configuracion';
import AppModal from '@/components/Partial/AppModal.vue';
import VDataTableCard from '@/components/Table/VDataTableCard.vue';
import { useVuetifyTable } from '@/composables/useVuetifyTable';
import { useCrudModal } from '@/composables/useCrudModal';
import apiClient from '@/api/axios';
import {
    formatStatusChip,
    formatNivelIncidenciaChip,
    formatNivelSeveridadChip,
    formatDerivacionInmediataChip,
} from '@/utils/vuetifyTableHelpers';

// Form
const saveForm = reactive({
    nombre: '',
    nivel_incidencia: '',
    nivel_severidad: '',
    derivacion_inmediata: 'NO',
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
        title: 'NIVEL DE INCIDENCIA',
        key: 'nivel_incidencia',
        sortable: true,
        align: 'center' as const,
        width: '180px',
    },
    {
        title: 'NIVEL DE SEVERIDAD',
        key: 'nivel_severidad',
        sortable: true,
        align: 'center' as const,
        width: '180px',
    },
    {
        title: 'DERIVACIÓN INMEDIATA',
        key: 'derivacion_inmediata',
        sortable: true,
        align: 'center' as const,
        width: '200px',
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
const table = useVuetifyTable<TiposIncidencia>({
    apiURL: '/configuracion/tipos-incidencia',
    searchFields: ['nombre', 'nivel_incidencia', 'nivel_severidad'],
    serverSidePagination: false,
    serverSideSorting: false,
    serverSideSearch: false,
});

// Inicializar menú de columnas
table.updateColumnMenu(headers);

// Composable de CRUD
const crudModal = useCrudModal<TiposIncidencia>({
    entityName: 'tipo de incidencia',
    getPayload: (form): CreateTiposIncidenciaDto => ({
        nombre: form.nombre.trim(),
        nivel_incidencia: form.nivel_incidencia ? String(form.nivel_incidencia) : '',
        nivel_severidad: form.nivel_severidad ? String(form.nivel_severidad) : '',
        derivacion_inmediata: form.derivacion_inmediata ? String(form.derivacion_inmediata) : 'NO',
    }),
    validateForm: (form) => {
        if (!form.nombre.trim()) {
            return 'El nombre del tipo de incidencia es obligatorio.';
        }
        if (!form.nivel_incidencia) {
            return 'El nivel de incidencia es obligatorio.';
        }
        if (!form.nivel_severidad) {
            return 'El nivel de severidad es obligatorio.';
        }
        if (!form.derivacion_inmediata) {
            return 'La derivación inmediata es obligatoria.';
        }
        return null;
    },
    onCreate: async (data: CreateTiposIncidenciaDto) => {
        const response = await apiClient.post<TiposIncidencia>('/configuracion/tipos-incidencia', data);
        return response.data;
    },
    onUpdate: async (id: number, data: CreateTiposIncidenciaDto) => {
        const response = await apiClient.post<TiposIncidencia>(`/configuracion/tipos-incidencia/${id}`, {
            ...data,
            id,
        });
        return response.data;
    },
    onDeleteCustom: async (id: number) => {
        await apiClient.delete(`/configuracion/tipos-incidencia/${id}`);
    },
    onEdit: (tipoIncidencia: TiposIncidencia) => {
        saveForm.nombre = tipoIncidencia.nombre;
        saveForm.nivel_incidencia = tipoIncidencia.nivel_incidencia;
        saveForm.nivel_severidad = tipoIncidencia.nivel_severidad;
        saveForm.derivacion_inmediata = tipoIncidencia.derivacion_inmediata;
    },
    resetForm: () => {
        saveForm.nombre = '';
        saveForm.nivel_incidencia = '';
        saveForm.nivel_severidad = '';
        saveForm.derivacion_inmediata = 'NO';
    },
});

// Funciones
const updateSearchValue = (value: string) => {
    table.searchQuery.value = value;
    table.applySearch(value);
};

const downloadExcel = () => {
    table.downloadExcel('tipos_incidencias.xlsx', 'Tipos de Incidencias');
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
                            <h1 class="text-h5 font-weight-bold mb-2">Tipos de Incidencias</h1>
                            <p class="text-body-2 text-medium-emphasis mb-0">
                                Gestiona los tipos de incidencias; crea, edita o elimina según necesidad.
                            </p>
                        </div>
                        <v-btn
                            color="primary"
                            prepend-icon="mdi-plus"
                            variant="flat"
                            size="default"
                            @click="crudModal.openCreateModal"
                            aria-label="Crear nuevo tipo de incidencia"
                            class="text-none"
                        >
                            Nuevo Tipo
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
                    search-placeholder="Buscar tipo de incidencia..."
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

                        <template #item.nivel_incidencia="{ value }">
                            <v-chip
                                :color="formatNivelIncidenciaChip(value).color"
                                size="small"
                                variant="flat"
                                class="text-uppercase font-weight-medium"
                            >
                                {{ formatNivelIncidenciaChip(value).label }}
                            </v-chip>
                        </template>

                        <template #item.nivel_severidad="{ value }">
                            <v-chip
                                :color="formatNivelSeveridadChip(value).color"
                                size="small"
                                variant="flat"
                                class="text-uppercase font-weight-medium"
                            >
                                {{ formatNivelSeveridadChip(value).label }}
                            </v-chip>
                        </template>

                        <template #item.derivacion_inmediata="{ value }">
                            <v-chip
                                :color="formatDerivacionInmediataChip(value).color"
                                size="small"
                                variant="flat"
                                class="text-uppercase font-weight-medium"
                            >
                                {{ formatDerivacionInmediataChip(value).label }}
                            </v-chip>
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
                        <v-container fluid class="pa-4">
                            <v-text-field
                                v-model="saveForm.nombre"
                                label="Nombre del Tipo"
                                :rules="[(v) => !!v || 'El nombre es obligatorio']"
                                counter="50"
                                maxlength="50"
                                placeholder="Ingrese el nombre del tipo de incidencia"
                                required
                                variant="outlined"
                                density="compact"
                                class="mb-4"
                            />
                            <v-select
                                v-model="saveForm.nivel_incidencia"
                                label="Nivel de Incidencia"
                                :items="[
                                    { title: 'NEGATIVO', value: 'NEGATIVO' },
                                    { title: 'POSITIVA', value: 'POSITIVA' },
                                    { title: 'NEUTRA', value: 'NEUTRA' },
                                ]"
                                :rules="[(v) => !!v || 'El nivel de incidencia es obligatorio']"
                                required
                                variant="outlined"
                                density="compact"
                                class="mb-4"
                            />
                            <v-select
                                v-model="saveForm.nivel_severidad"
                                label="Nivel de Severidad"
                                :items="[
                                    { title: 'BAJA', value: 'BAJA' },
                                    { title: 'MEDIA', value: 'MEDIA' },
                                    { title: 'ALTA', value: 'ALTA' },
                                ]"
                                :rules="[(v) => !!v || 'El nivel de severidad es obligatorio']"
                                required
                                variant="outlined"
                                density="compact"
                                class="mb-4"
                            />
                            <v-select
                                v-model="saveForm.derivacion_inmediata"
                                label="¿Requiere derivación inmediata?"
                                :items="[
                                    { title: 'NO', value: 'NO' },
                                    { title: 'SÍ', value: 'SI' },
                                ]"
                                :rules="[(v) => !!v || 'La derivación inmediata es obligatoria']"
                                required
                                variant="outlined"
                                density="compact"
                            />
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
                title="Eliminar tipo de incidencia"
                size="sm"
            >
                <template #body>
                    <v-container fluid class="pa-4">
                        <div class="text-center mb-4">
                            <v-icon icon="mdi-alert-circle" size="64" color="error" />
                        </div>
                        <p class="text-body-1 text-center">
                            ¿Está seguro que desea eliminar el tipo de incidencia
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
