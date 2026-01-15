<script setup lang="ts">
import AuthenticatedLayout from '@/components/Layouts/AuthenticatedLayout.vue';
import { ref, reactive, computed, onMounted, onActivated, watch } from 'vue';
import * as XLSX from 'xlsx';
import type { Personal } from '@/types/configuracion';
import AppModal from '@/components/Partial/AppModal.vue';
import VDataTableCard from '@/components/Table/VDataTableCard.vue';
import { notificacion } from '@/utils/notificacion';
import { useImageUpload } from '@/composables/useImageUpload';
import { useVuetifyTable } from '@/composables/useVuetifyTable';
import apiClient from '@/api/axios';
import {
    formatStatusChip,
    formatTipoContratacionChip,
} from '@/utils/vuetifyTableHelpers';

// Selects para dropdowns
const tiposPersonal = ref<{ id: number; text: string }[]>([]);
const tiposDocumento = ref<{ id: number; text: string }[]>([]);
const proveedores = ref<{ id: number; text: string }[]>([]);
const ubigeos = ref<{ id: string; text: string }[]>([]);

// Form
const saveForm = reactive({
    id_tipo_personal: '',
    id_tipo_documento: '',
    numero_documento: '',
    nombre: '',
    apellido: '',
    tipo_contratacion: 'DIRECTA',
    direccion: '',
    ubigeo: '',
    comentario: '',
    id_proveedor: '',
});

const imagenUpload = useImageUpload('/images/sin_imagen.jpg');
const firmaUpload = useImageUpload('/images/sin_imagen.jpg');
const showSaveModal = ref(false);
const showDeleteModal = ref(false);
const editingId = ref<number | null>(null);
const deleteTarget = ref<Personal | null>(null);
const saving = ref(false);
const deleting = ref(false);

const saveModalTitle = computed(() =>
    editingId.value ? 'Editar personal' : 'Nuevo personal',
);

const isProveedorEnabled = computed(() => saveForm.tipo_contratacion === 'TERCERO');

// Headers de la tabla
const headers = [
    {
        title: 'ACCIONES',
        key: 'actions',
        sortable: false,
        width: '150px',
    },
    {
        title: 'TIPO PERSONAL',
        key: 'nombre_tipo_personal',
        sortable: true,
        width: '180px',
    },
    {
        title: 'DOCUMENTO',
        key: 'numero_documento',
        sortable: true,
        width: '150px',
    },
    {
        title: 'NOMBRES',
        key: 'nombre',
        sortable: true,
    },
    {
        title: 'APELLIDOS',
        key: 'apellido',
        sortable: true,
    },
    {
        title: 'TIPO CONTRATACIÓN',
        key: 'tipo_contratacion',
        sortable: true,
        align: 'center' as const,
        width: '180px',
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
const table = useVuetifyTable<Personal>({
    apiURL: '/configuracion/personal',
    searchFields: ['nombre', 'apellido', 'nombre_tipo_personal'],
    serverSidePagination: false,
    serverSideSorting: false,
    serverSideSearch: false,
});

// Inicializar menú de columnas
table.updateColumnMenu(headers);

// Funciones para selects
async function loadSelects() {
    try {
        const [tiposPersonalRes, tiposDocumentoRes, proveedoresRes, ubigeosRes] = await Promise.all([
            apiClient.get('/configuracion/selects/tipos-personal'),
            apiClient.get('/configuracion/selects/tipos-documento'),
            apiClient.get('/configuracion/selects/proveedores'),
            apiClient.get('/configuracion/selects/ubigeos'),
        ]);

        tiposPersonal.value = tiposPersonalRes.data;
        tiposDocumento.value = tiposDocumentoRes.data;
        proveedores.value = proveedoresRes.data;
        ubigeos.value = ubigeosRes.data;
    } catch (error) {
        console.error('Error cargando selects:', error);
    }
}

// Funciones de modales
async function openCreateModal() {
    editingId.value = null;
    resetSaveForm();
    imagenUpload.reset();
    firmaUpload.reset();
    await loadSelects();
    showSaveModal.value = true;
}

async function openEditModal(personalData: Personal) {
    editingId.value = personalData.id;
    await loadSelects();
    
    saveForm.id_tipo_personal = personalData.id_tipo_personal ? String(personalData.id_tipo_personal) : '';
    saveForm.id_tipo_documento = personalData.id_tipo_documento ? String(personalData.id_tipo_documento) : '';
    saveForm.numero_documento = String(personalData.numero_documento || '');
    saveForm.nombre = personalData.nombre || '';
    saveForm.apellido = personalData.apellido || '';
    saveForm.tipo_contratacion = personalData.tipo_contratacion || 'DIRECTA';
    saveForm.direccion = personalData.direccion || '';
    saveForm.ubigeo = personalData.ubigeo ? String(personalData.ubigeo) : '';
    saveForm.comentario = personalData.comentario || '';
    saveForm.id_proveedor = personalData.id_proveedor ? String(personalData.id_proveedor) : '';

    imagenUpload.file.value = null;
    firmaUpload.file.value = null;

    if (personalData.imagen) {
        imagenUpload.setPreview(`/storage/personales/${personalData.imagen}`);
    } else {
        imagenUpload.setPreview('/images/sin_imagen.jpg');
    }
    
    if (personalData.firma) {
        firmaUpload.setPreview(`/storage/personales/${personalData.firma}`);
    } else {
        firmaUpload.setPreview('/images/sin_imagen.jpg');
    }

    showSaveModal.value = true;
}

function openDeleteModal(personalData: Personal) {
    deleteTarget.value = personalData;
    showDeleteModal.value = true;
}

function resetSaveForm() {
    saveForm.id_tipo_personal = '';
    saveForm.id_tipo_documento = '';
    saveForm.numero_documento = '';
    saveForm.nombre = '';
    saveForm.apellido = '';
    saveForm.tipo_contratacion = 'DIRECTA';
    saveForm.direccion = '';
    saveForm.ubigeo = '';
    saveForm.comentario = '';
    saveForm.id_proveedor = '';
}

async function handleSaveSubmit() {
    if (!saveForm.id_tipo_personal) {
        notificacion('El tipo de personal es obligatorio.', { type: 'danger', title: 'Validación' });
        return;
    }

    if (!saveForm.id_tipo_documento) {
        notificacion('El tipo de documento es obligatorio.', { type: 'danger', title: 'Validación' });
        return;
    }

    if (!saveForm.numero_documento.trim()) {
        notificacion('El número de documento es obligatorio.', { type: 'danger', title: 'Validación' });
        return;
    }

    if (!saveForm.nombre.trim()) {
        notificacion('El nombre es obligatorio.', { type: 'danger', title: 'Validación' });
        return;
    }

    if (!saveForm.apellido.trim()) {
        notificacion('El apellido es obligatorio.', { type: 'danger', title: 'Validación' });
        return;
    }

    if (!saveForm.tipo_contratacion) {
        notificacion('El tipo de contratación es obligatorio.', { type: 'danger', title: 'Validación' });
        return;
    }

    if (saveForm.tipo_contratacion === 'TERCERO' && !saveForm.id_proveedor) {
        notificacion('El proveedor es obligatorio cuando el tipo de contratación es TERCERO.', { type: 'danger', title: 'Validación' });
        return;
    }

    const formData = new FormData();
    const idTipoPersonal = Number(saveForm.id_tipo_personal);
    const idTipoDocumento = Number(saveForm.id_tipo_documento);
    
    if (isNaN(idTipoPersonal) || idTipoPersonal <= 0) {
        notificacion('El tipo de personal es obligatorio.', { type: 'danger', title: 'Validación' });
        return;
    }
    
    if (isNaN(idTipoDocumento) || idTipoDocumento <= 0) {
        notificacion('El tipo de documento es obligatorio.', { type: 'danger', title: 'Validación' });
        return;
    }
    
    formData.append('id_tipo_personal', String(idTipoPersonal));
    formData.append('id_tipo_documento', String(idTipoDocumento));
    formData.append('numero_documento', saveForm.numero_documento.trim());
    formData.append('nombre', saveForm.nombre.trim());
    formData.append('apellido', saveForm.apellido.trim());
    formData.append('tipo_contratacion', saveForm.tipo_contratacion);
    
    if (saveForm.direccion.trim()) {
        formData.append('direccion', saveForm.direccion.trim());
    }
    
    if (saveForm.ubigeo) {
        formData.append('ubigeo', String(saveForm.ubigeo));
    }
    
    if (saveForm.comentario.trim()) {
        formData.append('comentario', saveForm.comentario.trim());
    }

    if (saveForm.tipo_contratacion === 'TERCERO' && saveForm.id_proveedor) {
        formData.append('id_proveedor', String(Number(saveForm.id_proveedor)));
    }

    if (imagenUpload.file.value) {
        formData.append('imagen', imagenUpload.file.value);
    }

    if (firmaUpload.file.value) {
        formData.append('imagen_firma', firmaUpload.file.value);
    }

    saving.value = true;

    try {
        if (editingId.value) {
            await apiClient.post(`/configuracion/personal/${editingId.value}`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            notificacion('Personal actualizado correctamente.', { type: 'success' });
        } else {
            await apiClient.post('/configuracion/personal', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            notificacion('Personal registrado correctamente.', { type: 'success' });
        }

        showSaveModal.value = false;
        await table.reloadTable();
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
        await apiClient.delete(`/configuracion/personal/${deleteTarget.value.id}`);
        notificacion('Personal eliminado correctamente.', { type: 'success' });
        showDeleteModal.value = false;
        await table.reloadTable();
    } catch (error: any) {
        const message = error.response?.data?.message || 'No fue posible eliminar el registro.';
        notificacion(message, { type: 'danger', title: 'Error' });
    } finally {
        deleting.value = false;
    }
}

// Funciones
const updateSearchValue = (value: string) => {
    table.searchQuery.value = value;
    table.applySearch(value);
};

const downloadExcel = () => {
    table.downloadExcel('personal.xlsx', 'Personal');
};

const toggleColumnVisibility = (key: string) => {
    table.toggleColumnVisibility(key);
};

// Watchers
watch(() => saveForm.tipo_contratacion, (value) => {
    if (value !== 'TERCERO') {
        saveForm.id_proveedor = '';
    }
});

// Lifecycle
onMounted(async () => {
    (window as any).XLSX = XLSX;
    await loadSelects();
    await table.loadItems({
        page: 1,
        itemsPerPage: 10,
    });
});

onActivated(async () => {
    await loadSelects();
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
                            <h1 class="text-h5 font-weight-bold mb-2">Personal</h1>
                            <p class="text-body-2 text-medium-emphasis mb-0">
                                Gestiona el personal; crea, edita o elimina según necesidad.
                            </p>
                        </div>
                        <v-btn
                            color="primary"
                            prepend-icon="mdi-plus"
                            variant="flat"
                            size="default"
                            @click="openCreateModal"
                            aria-label="Crear nuevo personal"
                            class="text-none"
                        >
                            Nuevo Personal
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
                    search-placeholder="Buscar personal..."
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
                                    @click="openEditModal(item)"
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
                                            @click="openDeleteModal(item)"
                                        />
                                    </v-list>
                                </v-menu>
                            </div>
                        </template>

                        <template #item.tipo_contratacion="{ value }">
                            <v-chip
                                :color="formatTipoContratacionChip(value).color"
                                size="small"
                                variant="flat"
                                class="text-uppercase font-weight-medium"
                            >
                                {{ formatTipoContratacionChip(value).label }}
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
                v-model:open="showSaveModal"
                :title="saveModalTitle"
                size="xl"
            >
                <template #body>
                    <v-container fluid class="pa-4">
                        <v-form @submit.prevent>
                            <v-row>
                                <v-col cols="12" md="3">
                                    <v-card variant="outlined" class="pa-3" rounded="md">
                                        <div class="text-center mb-3">
                                            <label class="text-body-2 font-weight-medium mb-2 d-block">Foto del Personal</label>
                                            <v-img
                                                :src="imagenUpload.preview.value || '/images/sin_imagen.jpg'"
                                                alt="Imagen"
                                                class="mx-auto"
                                                rounded="md"
                                                max-width="200"
                                                max-height="200"
                                                contain
                                                style="border: 1px solid rgba(0,0,0,0.12);"
                                            />
                                        </div>
                                        <v-file-input
                                            label="Examinar Foto"
                                            prepend-icon="mdi-camera"
                                            variant="outlined"
                                            density="compact"
                                            accept="image/*"
                                            @change="imagenUpload.handleChange"
                                            hide-details
                                        />
                                    </v-card>
                                    <v-card variant="outlined" class="pa-3 mt-3" rounded="md">
                                        <div class="text-center mb-3">
                                            <label class="text-body-2 font-weight-medium mb-2 d-block">Firma</label>
                                            <v-img
                                                :src="firmaUpload.preview.value || '/images/sin_imagen.jpg'"
                                                alt="Firma"
                                                class="mx-auto"
                                                rounded="md"
                                                max-width="200"
                                                max-height="200"
                                                contain
                                                style="border: 1px solid rgba(0,0,0,0.12);"
                                            />
                                        </div>
                                        <v-file-input
                                            label="Examinar Firma"
                                            prepend-icon="mdi-camera"
                                            variant="outlined"
                                            density="compact"
                                            accept="image/*"
                                            @change="firmaUpload.handleChange"
                                            hide-details
                                        />
                                    </v-card>
                                </v-col>
                                <v-col cols="12" md="9">
                                    <v-row>
                                        <v-col cols="12" md="4">
                                            <v-select
                                                v-model="saveForm.id_tipo_personal"
                                                label="Tipo de Personal"
                                                :items="tiposPersonal.map(t => ({ title: t.text, value: String(t.id) }))"
                                                :rules="[(v) => !!v || 'El tipo de personal es obligatorio']"
                                                required
                                                clearable
                                                variant="outlined"
                                                density="compact"
                                            />
                                        </v-col>
                                        <v-col cols="12" md="4">
                                            <v-select
                                                v-model="saveForm.id_tipo_documento"
                                                label="Tipo de Documento"
                                                :items="tiposDocumento.map(d => ({ title: d.text, value: String(d.id) }))"
                                                :rules="[(v) => !!v || 'El tipo de documento es obligatorio']"
                                                required
                                                clearable
                                                variant="outlined"
                                                density="compact"
                                            />
                                        </v-col>
                                        <v-col cols="12" md="4">
                                            <v-text-field
                                                v-model="saveForm.numero_documento"
                                                label="Número de Documento"
                                                :rules="[(v) => !!v || 'El número de documento es obligatorio']"
                                                maxlength="20"
                                                required
                                                counter
                                                variant="outlined"
                                                density="compact"
                                            />
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                v-model="saveForm.nombre"
                                                label="Nombres"
                                                :rules="[(v) => !!v || 'El nombre es obligatorio']"
                                                maxlength="200"
                                                required
                                                counter
                                                variant="outlined"
                                                density="compact"
                                            />
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                v-model="saveForm.apellido"
                                                label="Apellidos"
                                                :rules="[(v) => !!v || 'El apellido es obligatorio']"
                                                maxlength="200"
                                                required
                                                counter
                                                variant="outlined"
                                                density="compact"
                                            />
                                        </v-col>
                                        <v-col cols="12" md="3">
                                            <v-select
                                                v-model="saveForm.tipo_contratacion"
                                                label="Tipo de Contratación"
                                                :items="[
                                                    { title: 'DIRECTA', value: 'DIRECTA' },
                                                    { title: 'TERCERO', value: 'TERCERO' },
                                                ]"
                                                :rules="[(v) => !!v || 'El tipo de contratación es obligatorio']"
                                                required
                                                variant="outlined"
                                                density="compact"
                                            />
                                        </v-col>
                                        <v-col cols="12" md="9">
                                            <v-text-field
                                                v-model="saveForm.direccion"
                                                label="Dirección"
                                                maxlength="100"
                                                counter
                                                variant="outlined"
                                                density="compact"
                                            />
                                        </v-col>
                                        <v-col cols="12">
                                            <v-select
                                                v-model="saveForm.id_proveedor"
                                                label="Proveedor"
                                                :items="proveedores.map(p => ({ title: p.text, value: String(p.id) }))"
                                                :required="isProveedorEnabled"
                                                :disabled="!isProveedorEnabled"
                                                clearable
                                                variant="outlined"
                                                density="compact"
                                                :hint="isProveedorEnabled ? 'Obligatorio cuando el tipo de contratación es TERCERO' : 'Solo disponible para tipo TERCERO'"
                                                persistent-hint
                                            />
                                        </v-col>
                                        <v-col cols="12">
                                            <v-select
                                                v-model="saveForm.ubigeo"
                                                label="UBIGEO (Departamento - Provincia - Distrito)"
                                                :items="ubigeos.map(u => ({ title: u.text, value: u.id }))"
                                                clearable
                                                variant="outlined"
                                                density="compact"
                                            />
                                        </v-col>
                                        <v-col cols="12">
                                            <v-textarea
                                                v-model="saveForm.comentario"
                                                label="Comentario"
                                                maxlength="100"
                                                counter
                                                rows="2"
                                                variant="outlined"
                                                density="compact"
                                            />
                                        </v-col>
                                    </v-row>
                                </v-col>
                            </v-row>
                        </v-form>
                    </v-container>
                </template>
                <template #footer>
                    <div class="d-flex justify-end ga-2">
                        <v-btn
                            variant="outlined"
                            @click="showSaveModal = false"
                            :disabled="saving"
                            class="text-none"
                        >
                            Cancelar
                        </v-btn>
                        <v-btn
                            color="primary"
                            variant="flat"
                            @click="handleSaveSubmit"
                            :loading="saving"
                            class="text-none"
                        >
                            {{ editingId ? 'Actualizar' : 'Guardar' }}
                        </v-btn>
                    </div>
                </template>
            </AppModal>

            <!-- Delete Modal -->
            <AppModal
                v-model:open="showDeleteModal"
                title="Eliminar Personal"
                size="sm"
            >
                <template #body>
                    <v-container fluid class="pa-4">
                        <div class="text-center mb-4">
                            <v-icon icon="mdi-alert-circle" size="64" color="error" />
                        </div>
                        <p class="text-body-1 text-center">
                            ¿Está seguro que desea eliminar el personal
                            <strong class="text-error">{{ deleteTarget?.nombre }} {{ deleteTarget?.apellido }}</strong>?
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
                            @click="showDeleteModal = false"
                            :disabled="deleting"
                            class="text-none"
                        >
                            Cancelar
                        </v-btn>
                        <v-btn
                            color="error"
                            variant="flat"
                            @click="handleDeleteConfirm"
                            :loading="deleting"
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

