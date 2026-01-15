<script setup lang="ts" generic="T extends { id: number }">
import { reactive, computed, onMounted, useSlots } from 'vue';
import AuthenticatedLayout from '@/components/Layouts/AuthenticatedLayout.vue';
import VDataTableCard from '@/components/Table/VDataTableCard.vue';
import AppModal from '@/components/Partial/AppModal.vue';
import { useVuetifyTable } from '@/composables/useVuetifyTable';
import { useCrudModal } from '@/composables/useCrudModal';
import { useTableColumns, type TableColumn } from '@/composables/useTableColumns';
import apiClient from '@/api/axios';

const slots = useSlots();

export interface CrudViewConfig<T extends { id: number }> {
    /** Nombre de la entidad (ej: "área", "usuario") */
    entityName: string;
    /** Título de la página */
    title: string;
    /** Descripción de la página */
    description?: string;
    /** Endpoint de la API (ej: "/configuracion/areas") */
    apiEndpoint: string;
    /** Columnas de la tabla */
    columns: TableColumn[];
    /** Campos en los que se puede buscar */
    searchFields?: (keyof T)[];
    /** Configuración del formulario */
    formConfig: {
        /** Campos del formulario con valores iniciales */
        initialValues: Record<string, unknown>;
        /** Función para transformar el formulario en payload */
        getPayload: (form: Record<string, unknown>) => unknown;
        /** Función de validación (retorna mensaje de error o null) */
        validate?: (form: Record<string, unknown>) => string | null;
        /** Función para poblar el formulario al editar */
        populateForm?: (item: T, form: Record<string, unknown>) => void;
        /** Función para resetear el formulario */
        resetForm: (form: Record<string, unknown>) => void;
    };
    /** Si es true, usa paginación server-side */
    serverSidePagination?: boolean;
    /** Si es true, usa ordenamiento server-side */
    serverSideSorting?: boolean;
    /** Si es true, usa búsqueda server-side */
    serverSideSearch?: boolean;
    /** Callback ejecutado después de guardar exitosamente */
    onSuccess?: () => void;
    /** Slot para contenido adicional en el header */
    headerActions?: () => unknown;
    /** Slot para el formulario del modal */
    formSlot?: (form: Record<string, unknown>) => unknown;
    /** Slot para acciones personalizadas en la tabla */
    actionsSlot?: (item: T) => unknown;
    /** Slot para contenido adicional en celdas */
    itemSlots?: Record<string, (item: T) => unknown>;
}

const props = defineProps<{
    config: CrudViewConfig<T>;
}>();

// Formulario reactivo - asegurar que todos los valores iniciales estén definidos
const form = reactive<Record<string, unknown>>({});
// Inicializar con valores por defecto de forma segura
if (props.config?.formConfig?.initialValues) {
    Object.keys(props.config.formConfig.initialValues).forEach(key => {
        const value = props.config.formConfig.initialValues[key];
        form[key] = value !== undefined && value !== null ? value : '';
    });
}

// Columnas de la tabla
const tableColumns = useTableColumns(props.config?.columns || []);

// Composable de tabla
const table = useVuetifyTable<T>({
    apiURL: props.config?.apiEndpoint || '',
    searchFields: props.config?.searchFields || ['nombre' as keyof T],
    serverSidePagination: props.config?.serverSidePagination ?? false,
    serverSideSorting: props.config?.serverSideSorting ?? false,
    serverSideSearch: props.config?.serverSideSearch ?? false,
});

// Inicializar menú de columnas
tableColumns.updateColumnMenu(tableColumns.columns.value);

// Composable de CRUD - wrapper seguro para getPayload y validate
const safeGetPayload = (form: Record<string, unknown>) => {
    if (!props.config?.formConfig?.getPayload) return form;
    try {
        return props.config.formConfig.getPayload(form);
    } catch (error) {
        console.error('Error in getPayload:', error);
        return form;
    }
};

const safeValidate = (form: Record<string, unknown>): string | null => {
    if (!props.config?.formConfig?.validate) return null;
    try {
        return props.config.formConfig.validate(form);
    } catch (error) {
        console.error('Error in validate:', error);
        return 'Error de validación';
    }
};

const crudModal = useCrudModal<T>({
    entityName: props.config?.entityName || 'registro',
    getPayload: safeGetPayload,
    validateForm: safeValidate,
    onCreate: async (data: unknown) => {
        // Detectar si es FormData y configurar headers apropiados
        const isFormData = data instanceof FormData;
        const requestConfig = isFormData ? { headers: { 'Content-Type': 'multipart/form-data' } } : {};
        const endpoint = props.config?.apiEndpoint || '';
        
        const response = await apiClient.post<T>(endpoint, data, requestConfig);
        return response.data;
    },
    onUpdate: async (id: number, data: unknown) => {
        // Detectar si es FormData y configurar headers apropiados
        const isFormData = data instanceof FormData;
        const requestConfig = isFormData ? { headers: { 'Content-Type': 'multipart/form-data' } } : {};
        const endpoint = props.config?.apiEndpoint || '';
        
        if (isFormData) {
            // Si es FormData, agregar el id directamente al FormData
            (data as FormData).append('id', String(id));
            const response = await apiClient.post<T>(`${endpoint}/${id}`, data, requestConfig);
            return response.data;
        } else {
            // Si es objeto JSON, agregar id al objeto
            const response = await apiClient.post<T>(`${endpoint}/${id}`, {
                ...data,
                id,
            }, requestConfig);
            return response.data;
        }
    },
    onDeleteCustom: async (id: number) => {
        const endpoint = props.config?.apiEndpoint || '';
        await apiClient.delete(`${endpoint}/${id}`);
    },
    onEdit: (item: T) => {
        // Resetear primero para asegurar valores iniciales
        if (props.config?.formConfig?.initialValues) {
            Object.keys(props.config.formConfig.initialValues).forEach(key => {
                const value = props.config.formConfig.initialValues[key];
                form[key] = value !== undefined && value !== null ? value : '';
            });
        }
        if (props.config?.formConfig?.resetForm) {
            props.config.formConfig.resetForm(form);
        }
        if (props.config?.formConfig?.populateForm) {
            props.config.formConfig.populateForm(item, form);
        }
    },
    resetForm: () => {
        // Resetear a valores iniciales asegurando que todos estén definidos
        if (props.config?.formConfig?.initialValues) {
            Object.keys(props.config.formConfig.initialValues).forEach(key => {
                const value = props.config.formConfig.initialValues[key];
                form[key] = value !== undefined && value !== null ? value : '';
            });
        }
        if (props.config?.formConfig?.resetForm) {
            props.config.formConfig.resetForm(form);
        }
    },
    onSuccess: props.config.onSuccess,
});

// Funciones
const updateSearchValue = (value: string) => {
    table.searchQuery.value = value;
    table.applySearch(value);
};

const downloadExcel = () => {
    const entityName = props.config?.entityName || 'registro';
    const title = props.config?.title || 'Listado';
    const safeEntityName = typeof entityName === 'string' ? entityName : 'registro';
    const filename = `${safeEntityName.toLowerCase().replace(/\s+/g, '_')}.xlsx`;
    table.downloadExcel(filename, title);
};

const toggleColumnVisibility = (key: string) => {
    tableColumns.toggleColumnVisibility(key);
};

// Computed para el placeholder de búsqueda
const searchPlaceholder = computed(() => {
    const entityName = props.config?.entityName || 'registro';
    return `Buscar ${entityName}...`;
});

// Computed para el título del modal de eliminación
const deleteModalTitle = computed(() => {
    const entityName = props.config?.entityName || 'registro';
    return `Eliminar ${entityName}`;
});

// Computed para slots de items personalizados
const itemSlots = computed(() => {
    if (!slots) return [];
    const slotKeys = Object.keys(slots);
    return slotKeys
        .filter(name => name && name.startsWith('item-'))
        .map(name => ({
            originalName: name,
            tableSlotName: `item.${name.replace('item-', '')}`,
        }));
});

// Lifecycle
onMounted(async () => {
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
                            <h1 class="text-h5 font-weight-bold mb-2">{{ props.config?.title || '' }}</h1>
                            <p v-if="props.config?.description" class="text-body-2 text-medium-emphasis mb-0">
                                {{ props.config.description }}
                            </p>
                        </div>
                        <div class="d-flex ga-2">
                            <slot name="header-actions">
                                <v-btn
                                    color="primary"
                                    prepend-icon="mdi-plus"
                                    variant="flat"
                                    size="default"
                                    @click="crudModal.openCreateModal"
                                    aria-label="Crear nuevo registro"
                                    class="text-none"
                                >
                                    Nuevo {{ props.config?.entityName || 'registro' }}
                                </v-btn>
                        </slot>
                        </div>
                    </div>
                </v-card-text>
            </v-card>

            <!-- Table Section -->
            <v-card rounded="lg" elevation="1">
                <VDataTableCard
                    :loading="table.loading.value"
                    :column-menu="tableColumns.columnMenu.value"
                    :search-value="table.searchQuery.value"
                    :search-placeholder="searchPlaceholder"
                    @print="table.printTable"
                    @export="downloadExcel"
                    @toggle-column="toggleColumnVisibility"
                    @update:search="updateSearchValue"
                >
                    <v-data-table-server
                        v-model:page="table.page.value"
                        v-model:items-per-page="table.itemsPerPage.value"
                        v-model:sort-by="table.sortBy.value"
                        :headers="tableColumns.headers.value"
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
                        <!-- Slot de acciones - siempre visible -->
                        <template #item.actions="{ item }">
                            <slot name="actions" :item="item">
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
                            </slot>
                        </template>

                        <!-- Slots personalizados para items -->
                        <template
                            v-for="slotInfo in itemSlots"
                            :key="slotInfo.originalName"
                        >
                            <template v-slot:[slotInfo.tableSlotName]="slotProps">
                                <slot :name="slotInfo.originalName" v-bind="slotProps" />
                            </template>
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
            >
                <template #body>
                    <slot name="form" :form="form">
                        <v-container fluid class="pa-4">
                            <v-form @submit.prevent>
                                <!-- Formulario básico - debe ser sobrescrito con slot -->
                                <v-text-field
                                    v-if="form.nombre !== undefined"
                                    v-model="form.nombre"
                                    label="Nombre"
                                    variant="outlined"
                                    density="compact"
                                    required
                                />
                            </v-form>
                        </v-container>
                    </slot>
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
                            @click="() => crudModal.handleSaveSubmit(form, table.reloadTable)"
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
                :title="`Eliminar ${props.config?.entityName || 'registro'}`"
                size="sm"
            >
                <template #body>
                    <v-container fluid class="pa-4">
                        <div class="text-center mb-4">
                            <v-icon icon="mdi-alert-circle" size="64" color="error" />
                        </div>
                        <p class="text-body-1 text-center">
                            ¿Está seguro que desea eliminar este registro?
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
