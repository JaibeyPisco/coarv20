<script setup lang="ts">
import AuthenticatedLayout from '@/components/Layouts/AuthenticatedLayout.vue';
import { ref, reactive, computed, onMounted, onBeforeUnmount, onActivated, watch, nextTick } from 'vue';
import apiClient from '@/api/axios';
// @ts-expect-error -- tabulator-tables no proporciona tipos ES module
import { TabulatorFull as Tabulator } from 'tabulator-tables';
import * as XLSX from 'xlsx';
import type { Personal } from '@/types/configuracion';
import AppModal from '@/components/Partial/AppModal.vue';
import TableCard from '@/components/Table/TableCard.vue';
import { notificacion } from '@/utils/notificacion';

const tableEl = ref<HTMLElement | null>(null);
const table = ref<any | null>(null);
const personal = ref<Personal[]>([]);
const loading = ref(false);
const searchQuery = ref('');
const columnMenu = ref<{ title: string; field: string; visible: boolean }[]>([]);
const recordSummary = ref('Mostrando 0 registros');

// Selects para dropdowns
const tiposPersonal = ref<{ id: number; text: string }[]>([]);
const tiposDocumento = ref<{ id: number; text: string }[]>([]);
const proveedores = ref<{ id: number; text: string }[]>([]);
const ubigeos = ref<{ id: string; text: string }[]>([]);

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
const imagenFile = ref<File | null>(null);
const firmaFile = ref<File | null>(null);
const imagenPreview = ref<string>('');
const firmaPreview = ref<string>('');
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
        title: 'TIPO PERSONAL',
        field: 'nombre_tipo_personal',
        width: 180,
        headerSort: true,
        formatter: 'plaintext',
    },
    {
        title: 'NOMBRE',
        field: 'nombre',
        minWidth: 150,
        headerSort: true,
        formatter: 'plaintext',
    },
    {
        title: 'APELLIDO',
        field: 'apellido',
        minWidth: 150,
        headerSort: true,
        formatter: 'plaintext',
    },
    {
        title: 'DIRECCIÓN',
        field: 'direccion',
        width: 250,
        formatter: (cell: any) => {
            const direccion = cell.getValue() || '';
            if (!direccion) return '—';
            return `<div style="max-width: 250px; word-wrap: break-word; overflow-wrap: break-word; white-space: normal; line-height: 1.4;">${direccion}</div>`;
        },
    },
    {
        title: 'TIPO CONTRATACIÓN',
        field: 'tipo_contratacion',
        width: 150,
        headerHozAlign: 'center',
        hozAlign: 'center',
        formatter: (cell: any) => {
            const value = cell.getValue();
            const config: Record<string, { label: string; className: string }> = {
                DIRECTA: {
                    label: 'DIRECTA',
                    className: 'badge rounded-pill px-3 bg-blue-lt text-blue fw-semibold',
                },
                TERCERO: {
                    label: 'TERCERO',
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

async function initializeTable() {
    await nextTick();
    if (!tableEl.value) return;

    table.value = new Tabulator(tableEl.value, {
        layout: 'fitColumns',
        reactiveData: false,
        placeholder: 'No se encontraron registros',
        columns,
        printHeader: '<h4 class="mb-3">Listado de personal</h4>',
        printFooter: '<small>Generado desde la intranet</small>',
        height: 'calc(100vh - 360px)',
        columnDefaults: {
            resizable: true,
        },
        ajaxURL: 'configuracion/personal',
        ajaxContentType: 'json',
        ajaxRequestFunc: async (url: string) => {
            const response = await apiClient.get(url);
            return response.data;
        },
        ajaxResponse: (_url: string, _params: any, response: any) => {
            const data: Personal[] = response?.data ?? [];
            personal.value = data;
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
    const data = cell.getRow().getData() as Personal;

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
    await table.value.replaceData('configuracion/personal');
    applySearch(searchQuery.value);
    updateRecordSummary();
}

function applySearch(query: string) {
    const normalized = query.trim().toLowerCase();
    if (!table.value) return;

    if (!normalized) {
        table.value.clearFilter(true);
    } else {
        table.value.setFilter((rowData: Personal) => {
            const values = [rowData.nombre, rowData.apellido, rowData.nombre_tipo_personal];
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
    const totalRows = table.value.getData().length || personal.value.length;
    recordSummary.value = `Mostrando ${filteredRows} de ${totalRows} registros`;
}

async function openCreateModal() {
    editingId.value = null;
    resetSaveForm();
    imagenPreview.value = '';
    firmaPreview.value = '';
    imagenFile.value = null;
    firmaFile.value = null;
    // Recargar selects antes de abrir el modal
    await loadSelects();
    showSaveModal.value = true;
}

async function openEditModal(personalData: Personal) {
    editingId.value = personalData.id;
    // Recargar selects antes de abrir el modal
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

    imagenFile.value = null;
    firmaFile.value = null;

    if (personalData.imagen) {
        imagenPreview.value = `/storage/personales/${personalData.imagen}`;
    } else {
        imagenPreview.value = '/images/sin_imagen.jpg';
    }
    if (personalData.firma) {
        firmaPreview.value = `/storage/personales/${personalData.firma}`;
    } else {
        firmaPreview.value = '/images/sin_imagen.jpg';
    }

    showSaveModal.value = true;
}

function openDeleteModal(personalData: Personal) {
    deleteTarget.value = personalData;
    showDeleteModal.value = true;
}

function handleImageChange(event: Event, type: 'imagen' | 'firma') {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (!file) return;

    if (type === 'imagen') {
        imagenFile.value = file;
    } else {
        firmaFile.value = file;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
        const result = e.target?.result as string;
        if (type === 'imagen') {
            imagenPreview.value = result;
        } else {
            firmaPreview.value = result;
        }
    };
    reader.readAsDataURL(file);
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

    if (imagenFile.value) {
        formData.append('imagen', imagenFile.value);
    }

    if (firmaFile.value) {
        formData.append('imagen_firma', firmaFile.value);
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
        await apiClient.delete(`/configuracion/personal/${deleteTarget.value.id}`);
        notificacion('Personal eliminado correctamente.', { type: 'success' });
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
    table.value.download('xlsx', 'personal.xlsx', { sheetName: 'Personal' });
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
    await loadSelects();
    await initializeTable();
    // No llamar reloadTable() aquí - la tabla ya carga datos automáticamente con ajaxURL
    document.addEventListener('click', handleGlobalClick);
});

// Recargar selects cuando el componente se activa (al volver a la página)
onActivated(async () => {
    await loadSelects();
});

watch(searchQuery, (value) => {
    applySearch(value);
});

watch(() => saveForm.tipo_contratacion, (value) => {
    if (value !== 'TERCERO') {
        saveForm.id_proveedor = '';
    }
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
                    <h1 class="h2 mb-1">Configuración / Personal</h1>
                    <p class="text-secondary mb-0">
                        Gestiona el personal; crea, edita o elimina según necesidad.
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

        <div class="personal-page">
            <TableCard
                :loading="loading"
                :column-menu="columnMenu"
                :search-value="searchQuery"
                search-placeholder="Buscar personal..."
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
                    <div class="row">
                        <div class="col-md-3">
                            <div class="row">
                                <div class="col-12 mb-3" align="center">
                                    <div class="mb-2">
                                        <img
                                            :key="`imagen-${editingId || 'new'}-${imagenPreview}`"
                                            :src="imagenPreview || '/images/sin_imagen.jpg'"
                                            alt="Imagen"
                                            class="img-fluid rounded"
                                            style="max-width: 100%; max-height: 200px; object-fit: cover; border: 1px solid #dee2e6;"
                                            @error="(e: Event) => { 
                                                const img = e.target as HTMLImageElement;
                                                if (img.src && !img.src.includes('data:') && img.src !== 'data:image/svg+xml') {
                                                    img.src = 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'200\' height=\'200\'%3E%3Crect fill=\'%23ddd\' width=\'200\' height=\'200\'/%3E%3Ctext fill=\'%23999\' font-family=\'sans-serif\' font-size=\'14\' dy=\'10.5\' font-weight=\'bold\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\'%3ESin Imagen%3C/text%3E%3C/svg%3E';
                                                }
                                            }"
                                        />
                                    </div>
                                    <label class="btn btn-default btn-sm w-100">
                                        <i class="ti ti-upload me-1"></i> Examinar
                                        <input
                                            type="file"
                                            accept="image/*"
                                            style="display: none;"
                                            @change="(e) => handleImageChange(e, 'imagen')"
                                        />
                                    </label>
                                </div>
                                <div class="col-12" align="center">
                                    <div class="mb-2">
                                        <img
                                            :key="`firma-${editingId || 'new'}-${firmaPreview}`"
                                            :src="firmaPreview || '/images/sin_imagen.jpg'"
                                            alt="Firma"
                                            class="img-fluid rounded"
                                            style="max-width: 100%; max-height: 150px; object-fit: cover; border: 1px solid #dee2e6;"
                                            @error="(e: Event) => { 
                                                const img = e.target as HTMLImageElement;
                                                if (img.src && !img.src.includes('data:') && img.src !== 'data:image/svg+xml') {
                                                    img.src = 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'200\' height=\'200\'%3E%3Crect fill=\'%23ddd\' width=\'200\' height=\'200\'/%3E%3Ctext fill=\'%23999\' font-family=\'sans-serif\' font-size=\'14\' dy=\'10.5\' font-weight=\'bold\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\'%3ESin Imagen%3C/text%3E%3C/svg%3E';
                                                }
                                            }"
                                        />
                                    </div>
                                    <label class="btn btn-default btn-sm w-100">
                                        <i class="ti ti-upload me-1"></i> Firma Virtual
                                        <input
                                            type="file"
                                            accept="image/*"
                                            style="display: none;"
                                            @change="(e) => handleImageChange(e, 'firma')"
                                        />
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label required" for="personal-tipo">Tipo de Personal</label>
                                    <select
                                        id="personal-tipo"
                                        v-model="saveForm.id_tipo_personal"
                                        class="form-select"
                                        required
                                    >
                                        <option value="">Seleccionar...</option>
                                        <option
                                            v-for="tipo in tiposPersonal"
                                            :key="tipo.id"
                                            :value="String(tipo.id)"
                                        >
                                            {{ tipo.text }}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label required" for="personal-documento">Documento</label>
                                    <select
                                        id="personal-documento"
                                        v-model="saveForm.id_tipo_documento"
                                        class="form-select"
                                        required
                                    >
                                        <option value="">Seleccionar...</option>
                                        <option
                                            v-for="doc in tiposDocumento"
                                            :key="doc.id"
                                            :value="String(doc.id)"
                                        >
                                            {{ doc.text }}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label required" for="personal-numero-doc">Número Doc.</label>
                                    <input
                                        id="personal-numero-doc"
                                        v-model="saveForm.numero_documento"
                                        type="text"
                                        class="form-control"
                                        maxlength="20"
                                        placeholder="Número de documento"
                                        required
                                    />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required" for="personal-nombre">Nombres</label>
                                    <input
                                        id="personal-nombre"
                                        v-model="saveForm.nombre"
                                        type="text"
                                        class="form-control"
                                        maxlength="200"
                                        placeholder="Nombres"
                                        required
                                    />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required" for="personal-apellido">Apellidos</label>
                                    <input
                                        id="personal-apellido"
                                        v-model="saveForm.apellido"
                                        type="text"
                                        class="form-control"
                                        maxlength="200"
                                        placeholder="Apellidos"
                                        required
                                    />
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label required" for="personal-contratacion">Tipo Contratación</label>
                                    <select
                                        id="personal-contratacion"
                                        v-model="saveForm.tipo_contratacion"
                                        class="form-select"
                                        required
                                    >
                                        <option value="DIRECTA">DIRECTA</option>
                                        <option value="TERCERO">TERCERO</option>
                                    </select>
                                </div>
                                <div class="col-md-9 mb-3">
                                    <label class="form-label" for="personal-direccion">Dirección</label>
                                    <input
                                        id="personal-direccion"
                                        v-model="saveForm.direccion"
                                        type="text"
                                        class="form-control"
                                        maxlength="100"
                                        placeholder="Dirección"
                                    />
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label" :class="{ required: isProveedorEnabled }" for="personal-proveedor">Proveedor</label>
                                    <select
                                        id="personal-proveedor"
                                        v-model="saveForm.id_proveedor"
                                        class="form-select"
                                        :disabled="!isProveedorEnabled"
                                        :required="isProveedorEnabled"
                                    >
                                        <option value="">Seleccionar...</option>
                                        <option
                                            v-for="prov in proveedores"
                                            :key="prov.id"
                                            :value="String(prov.id)"
                                        >
                                            {{ prov.text }}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label" for="personal-ubigeo">UBIGEO - (Departamento - Provincia - Distrito)</label>
                                    <select
                                        id="personal-ubigeo"
                                        v-model="saveForm.ubigeo"
                                        class="form-select"
                                    >
                                        <option value="">Seleccionar...</option>
                                        <option
                                            v-for="ubigeo in ubigeos"
                                            :key="ubigeo.id"
                                            :value="ubigeo.id"
                                        >
                                            {{ ubigeo.text }}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-12 mb-0">
                                    <label class="form-label" for="personal-comentario">Comentario</label>
                                    <input
                                        id="personal-comentario"
                                        v-model="saveForm.comentario"
                                        type="text"
                                        class="form-control"
                                        maxlength="100"
                                        placeholder="Comentario"
                                    />
                                </div>
                            </div>
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
            title="Eliminar personal"
            size="sm"
            @close="closeDeleteModal"
        >
            <template #body>
                <p class="mb-0">
                    ¿Seguro que deseas eliminar el personal
                    <strong>{{ deleteTarget?.nombre }} {{ deleteTarget?.apellido }}</strong>? Esta acción no se puede deshacer.
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
