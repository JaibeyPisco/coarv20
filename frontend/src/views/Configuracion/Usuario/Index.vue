<script setup lang="ts">
import AuthenticatedLayout from '@/components/Layouts/AuthenticatedLayout.vue';
import { ref, reactive, computed, onMounted, onBeforeUnmount, onActivated, watch, nextTick } from 'vue';
import apiClient from '@/api/axios';
// @ts-expect-error -- tabulator-tables no proporciona tipos ES module
import { TabulatorFull as Tabulator } from 'tabulator-tables';
import * as XLSX from 'xlsx';
import type { Usuario } from '@/types/configuracion';
import AppModal from '@/components/Partial/AppModal.vue';
import TableCard from '@/components/Table/TableCard.vue';
import { notificacion } from '@/utils/notificacion';
import { useImageUpload } from '@/composables/useImageUpload';

const tableEl = ref<HTMLElement | null>(null);
const table = ref<any | null>(null);
const usuarios = ref<Usuario[]>([]);
const loading = ref(false);
const searchQuery = ref('');
const columnMenu = ref<{ title: string; field: string; visible: boolean }[]>([]);
const recordSummary = ref('Mostrando 0 registros');

// Selects para dropdowns
const personal = ref<{ id: number; text: string }[]>([]);
const roles = ref<{ id: number; text: string }[]>([]);

// Para búsqueda de estudiantes (similar a Select2 AJAX)
const estudianteSearchQuery = ref('');
const estudiantesOptions = ref<{ id: number; text: string }[]>([]);
const estudianteSearchLoading = ref(false);
const estudianteDropdownOpen = ref(false);
const estudianteSearchTimeout = ref<ReturnType<typeof setTimeout> | null>(null);

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
    tipo_persona: 'STANDARD',
    id_personal: '',
    id_estudiante: '',
    nombre: '',
    apellido: '',
    email: '',
    usuario: '',
    password: '',
    id_rol: '',
    fl_ver_informacion_privada: false,
});

const imagenUpload = useImageUpload();
const showSaveModal = ref(false);
const showDeleteModal = ref(false);
const showChangePasswordModal = ref(false);
const showSuspendModal = ref(false);
const editingId = ref<number | null>(null);
const deleteTarget = ref<Usuario | null>(null);
const changePasswordTarget = ref<Usuario | null>(null);
const suspendTarget = ref<Usuario | null>(null);
const saving = ref(false);
const deleting = ref(false);
const changingPassword = ref(false);
const suspending = ref(false);

const passwordForm = reactive({
    password: '',
});

const saveModalTitle = computed(() => (editingId.value ? 'Editar usuario' : 'Nuevo usuario'));

const showPersonalSelect = computed(() => saveForm.tipo_persona === 'DOCENTE');
const showEstudianteSelect = computed(() => saveForm.tipo_persona === 'ESTUDIANTE');
const showNombreApellido = computed(() => saveForm.tipo_persona === 'STANDARD' || !saveForm.tipo_persona);
const showRolSelect = computed(() => saveForm.tipo_persona !== 'ESTUDIANTE');

const columns = [
    {
        title: 'ACCIONES',
        field: '_actions',
        width: 150,
        headerHozAlign: 'center',
        hozAlign: 'center',
        resizable: false,
        headerSort: false,
        formatter: (cell: any) => {
            const row = cell.getRow().getData() as Usuario;
            const suspendAction = row.fl_suspendido
                ? '<button type="button" class="dropdown-item text-success" data-action="activate"><i class="ti ti-play me-2"></i> Activar</button>'
                : '<button type="button" class="dropdown-item text-warning" data-action="suspend"><i class="ti ti-pause me-2"></i> Suspender</button>';
            return `
                <div class="btn-group actions-menu" style="position: relative;">
                    <button class="btn btn-sm btn-primary" type="button" data-action="edit">Editar</button>
                    <button class="btn btn-sm btn-primary dropdown-toggle dropdown-toggle-split actions-menu__toggle" type="button" data-action="toggle-menu">
                        <span class="visually-hidden">Toggle Dropdown</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-start actions-menu__dropdown" style="position: absolute; left: 0; top: 100%; margin-top: 0.125rem; min-width: 180px; z-index: 1000;">
                        <button type="button" class="dropdown-item" data-action="change-password"><i class="ti ti-lock me-2"></i> Cambiar Contraseña</button>
                        ${suspendAction}
                        <button type="button" class="dropdown-item text-danger" data-action="delete"><i class="ti ti-trash me-2"></i> Eliminar</button>
                    </div>
                </div>
            `;
        },
        cellClick: handleActionCellClick,
    },
    {
        title: 'ESTADO',
        field: 'fl_suspendido',
        width: 120,
        headerHozAlign: 'center',
        hozAlign: 'center',
        formatter: (cell: any) => {
            const value = cell.getValue();
            if (value) {
                return '<span class="badge rounded-pill px-3 bg-warning text-dark fw-semibold">SUSPENDIDO</span>';
            }
            return '<span class="badge rounded-pill px-3 bg-green-lt text-green fw-semibold">ACTIVO</span>';
        },
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
        title: 'USUARIO',
        field: 'usuario',
        minWidth: 120,
        headerSort: true,
        formatter: 'plaintext',
    },
    {
        title: 'EMAIL',
        field: 'email',
        minWidth: 200,
        headerSort: true,
        formatter: 'plaintext',
    },
    {
        title: 'TIPO PERSONA',
        field: 'tipo_persona',
        width: 120,
        headerHozAlign: 'center',
        hozAlign: 'center',
        formatter: (cell: any) => {
            const value = cell.getValue() || 'STANDARD';
            return `<span class="badge rounded-pill px-3 bg-blue-lt text-blue fw-semibold">${value}</span>`;
        },
    },
    {
        title: 'ROL',
        field: 'rol',
        width: 180,
        headerSort: true,
        formatter: 'plaintext',
    },
];

async function loadSelects() {
    try {
        const [personalRes, rolesRes] = await Promise.all([
            apiClient.get('/configuracion/selects/personal'),
            apiClient.get('/configuracion/selects/roles'),
        ]);

        personal.value = Array.isArray(personalRes.data) ? personalRes.data : [];
        
        const rolesData = Array.isArray(rolesRes.data) ? rolesRes.data : [];
        
        const hasSuperAdmin = rolesData.some((r: any) => r.id === 0 || r.text === 'SUPER ADMINISTRADOR');
        if (!hasSuperAdmin) {
            roles.value = [{ id: 0, text: 'SUPER ADMINISTRADOR' }, ...rolesData];
        } else {
            roles.value = rolesData;
        }
    } catch (error: any) {
        console.error('Error cargando selects:', error);
        notificacion('Error al cargar los datos de personal y roles.', { type: 'danger', title: 'Error' });
        personal.value = [];
        roles.value = [];
    }
}

// Función para buscar estudiantes (similar a Select2 AJAX)
async function searchEstudiantes(searchTerm: string) {
    if (searchTerm.length < 3) {
        estudiantesOptions.value = [];
        estudianteDropdownOpen.value = false;
        return;
    }

    estudianteSearchLoading.value = true;
    estudianteDropdownOpen.value = true;

    try {
        const response = await apiClient.get('/configuracion/estudiante/getSelect', {
            params: { buscar: searchTerm },
        });

        // La respuesta puede venir en diferentes formatos
        const data = Array.isArray(response.data) 
            ? response.data 
            : (Array.isArray(response.data?.data) ? response.data.data : []);

        estudiantesOptions.value = data.map((item: any) => ({
            id: item.id ?? item.value,
            text: item.text ?? item.label ?? `${item.nombre ?? ''} ${item.apellido ?? ''} ${item.dni ?? ''}`.trim(),
        }));
    } catch (error: any) {
        console.error('Error buscando estudiantes:', error);
        estudiantesOptions.value = [];
        notificacion('Error al buscar estudiantes.', { type: 'danger', title: 'Error' });
    } finally {
        estudianteSearchLoading.value = false;
    }
}

function selectEstudiante(estudiante: { id: number; text: string }) {
    saveForm.id_estudiante = String(estudiante.id);
    estudianteSearchQuery.value = estudiante.text;
    estudiantesOptions.value = [];
    estudianteDropdownOpen.value = false;
}

function clearEstudiante() {
    saveForm.id_estudiante = '';
    estudianteSearchQuery.value = '';
    estudiantesOptions.value = [];
    estudianteDropdownOpen.value = false;
}

function handleEstudianteBlur() {
    setTimeout(() => {
        estudianteDropdownOpen.value = false;
    }, 200);
}

async function initializeTable() {
    await nextTick();
    if (!tableEl.value) return;

    table.value = new Tabulator(tableEl.value, {
        layout: 'fitColumns',
        reactiveData: false,
        placeholder: 'No se encontraron registros',
        columns,
        printHeader: '<h4 class="mb-3">Listado de usuarios</h4>',
        printFooter: '<small>Generado desde la intranet</small>',
        height: 'calc(100vh - 360px)',
        columnDefaults: {
            resizable: true,
        },
        ajaxURL: 'configuracion/usuario',
        ajaxContentType: 'json',
        ajaxRequestFunc: async (url: string) => {
            const response = await apiClient.get(url);
            return response.data;
        },
        ajaxResponse: (_url: string, _params: any, response: any) => {
            const data: Usuario[] = response?.data ?? [];
            usuarios.value = data;
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
    const data = cell.getRow().getData() as Usuario;

    if (action === 'edit') {
        openEditModal(data);
    } else if (action === 'delete') {
        openDeleteModal(data);
    } else if (action === 'change-password') {
        openChangePasswordModal(data);
    } else if (action === 'suspend') {
        openSuspendModal(data);
    } else if (action === 'activate') {
        handleActivate(data);
    }
}

async function reloadTable() {
    if (!table.value) return;
    loading.value = true;
    await table.value.replaceData('configuracion/usuario');
    applySearch(searchQuery.value);
    updateRecordSummary();
}

function applySearch(query: string) {
    const normalized = query.trim().toLowerCase();
    if (!table.value) return;

    if (!normalized) {
        table.value.clearFilter(true);
    } else {
        table.value.setFilter((rowData: Usuario) => {
            const values = [rowData.nombre, rowData.apellido, rowData.usuario, rowData.email];
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
    const totalRows = table.value.getData().length || usuarios.value.length;
    recordSummary.value = `Mostrando ${filteredRows} de ${totalRows} registros`;
}

async function openCreateModal() {
    editingId.value = null;
    resetSaveForm();
    imagenUpload.reset();
    clearEstudiante();
    // Recargar selects antes de abrir el modal
    await loadSelects();
    showSaveModal.value = true;
}

async function openEditModal(usuarioData: Usuario) {
    editingId.value = usuarioData.id;
    // Recargar selects antes de abrir el modal
    await loadSelects();
    
    // Primero establecer el tipo de persona para que los computed properties se actualicen
    saveForm.tipo_persona = usuarioData.tipo_persona || 'STANDARD';
    
    // Esperar un tick para que Vue actualice los computed properties
    await nextTick();
    
    // Luego establecer los demás valores
    saveForm.id_personal = usuarioData.id_personal ? String(usuarioData.id_personal) : '';
    saveForm.id_estudiante = usuarioData.id_estudiante ? String(usuarioData.id_estudiante) : '';
    saveForm.nombre = usuarioData.nombre || '';
    saveForm.apellido = usuarioData.apellido || '';
    saveForm.email = usuarioData.email || '';
    saveForm.usuario = usuarioData.usuario || '';
    saveForm.password = '';
    saveForm.id_rol = usuarioData.id_rol ? String(usuarioData.id_rol) : '';
    saveForm.fl_ver_informacion_privada = usuarioData.fl_ver_informacion_privada || false;

    imagenUpload.file.value = null;

    if (usuarioData.imagen) {
        imagenUpload.setPreview(usuarioData.imagen_url || `/storage/usuarios/${usuarioData.imagen}`);
    } else {
        imagenUpload.setPreview('/images/sin_imagen.jpg');
    }
    
    // Si es estudiante, establecer el texto de búsqueda
    if (usuarioData.tipo_persona === 'ESTUDIANTE' && usuarioData.id_estudiante) {
        estudianteSearchQuery.value = usuarioData.estudiante_nombre || `${usuarioData.nombre || ''} ${usuarioData.apellido || ''}`.trim() || '';
    }

    showSaveModal.value = true;
}

function openDeleteModal(usuarioData: Usuario) {
    deleteTarget.value = usuarioData;
    showDeleteModal.value = true;
}

function openChangePasswordModal(usuarioData: Usuario) {
    changePasswordTarget.value = usuarioData;
    passwordForm.password = '';
    showChangePasswordModal.value = true;
}

function openSuspendModal(usuarioData: Usuario) {
    suspendTarget.value = usuarioData;
    showSuspendModal.value = true;
}


async function handleSaveSubmit() {
    if (!saveForm.usuario.trim()) {
        notificacion('El usuario es obligatorio.', { type: 'danger', title: 'Validación' });
        return;
    }

    if (!saveForm.email.trim()) {
        notificacion('El email es obligatorio.', { type: 'danger', title: 'Validación' });
        return;
    }

    if (!editingId.value && !saveForm.password.trim()) {
        notificacion('La contraseña es obligatoria para nuevos usuarios.', { type: 'danger', title: 'Validación' });
        return;
    }

    if (saveForm.tipo_persona === 'DOCENTE' && !saveForm.id_personal) {
        notificacion('El personal es obligatorio para tipo DOCENTE.', { type: 'danger', title: 'Validación' });
        return;
    }

    if (saveForm.tipo_persona === 'STANDARD' && (!saveForm.nombre.trim() || !saveForm.apellido.trim())) {
        notificacion('El nombre y apellido son obligatorios para tipo STANDARD.', { type: 'danger', title: 'Validación' });
        return;
    }

    const formData = new FormData();
    formData.append('usuario', saveForm.usuario.trim());
    formData.append('email', saveForm.email.trim());
    if (saveForm.password.trim()) {
        formData.append('password', saveForm.password.trim());
    }
    formData.append('tipo_persona', saveForm.tipo_persona);
    
    if (saveForm.tipo_persona === 'DOCENTE' && saveForm.id_personal) {
        formData.append('id_personal', String(Number(saveForm.id_personal)));
    }
    
    if (saveForm.tipo_persona === 'ESTUDIANTE' && saveForm.id_estudiante) {
        formData.append('id_estudiante', String(Number(saveForm.id_estudiante)));
    }
    
    if (saveForm.tipo_persona === 'STANDARD') {
        formData.append('nombre', saveForm.nombre.trim());
        formData.append('apellido', saveForm.apellido.trim());
    }

    if (saveForm.id_rol && saveForm.id_rol !== '' && saveForm.id_rol !== '0') {
        formData.append('id_rol', String(Number(saveForm.id_rol)));
    } else if (saveForm.id_rol === '0') {
        formData.append('id_rol', '');
    }

    formData.append('fl_ver_informacion_privada', saveForm.fl_ver_informacion_privada ? '1' : '0');

    if (imagenUpload.file.value) {
        formData.append('imagen', imagenUpload.file.value);
    }

    if (editingId.value) {
        const usuarioActual = usuarios.value.find((u: Usuario) => u.id === editingId.value);
        if (usuarioActual?.imagen && !imagenUpload.file.value) {
            formData.append('imagen_anterior', usuarioActual.imagen);
        }
    }

    saving.value = true;

    try {
        if (editingId.value) {
            await apiClient.post(`/configuracion/usuario/${editingId.value}`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            notificacion('Usuario actualizado correctamente.', { type: 'success' });
        } else {
            await apiClient.post('/configuracion/usuario', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            notificacion('Usuario registrado correctamente.', { type: 'success' });
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
        await apiClient.delete(`/configuracion/usuario/${deleteTarget.value.id}`);
        notificacion('Usuario eliminado correctamente.', { type: 'success' });
        showDeleteModal.value = false;
        await reloadTable();
    } catch (error: any) {
        const message = error.response?.data?.message || 'No fue posible eliminar el registro.';
        notificacion(message, { type: 'danger', title: 'Error' });
    } finally {
        deleting.value = false;
    }
}

async function handleChangePasswordSubmit() {
    if (!changePasswordTarget.value) return;

    if (!passwordForm.password.trim()) {
        notificacion('La nueva contraseña es obligatoria.', { type: 'danger', title: 'Validación' });
        return;
    }

    changingPassword.value = true;

    try {
        await apiClient.post(`/configuracion/usuario/${changePasswordTarget.value.id}/change-password`, {
            password: passwordForm.password.trim(),
        });
        notificacion('Contraseña actualizada correctamente.', { type: 'success' });
        showChangePasswordModal.value = false;
        await reloadTable();
    } catch (error: any) {
        const message = error.response?.data?.message || 'No fue posible cambiar la contraseña.';
        notificacion(message, { type: 'danger', title: 'Error' });
    } finally {
        changingPassword.value = false;
    }
}

async function handleSuspendConfirm() {
    if (!suspendTarget.value) return;

    suspending.value = true;

    try {
        await apiClient.post(`/configuracion/usuario/${suspendTarget.value.id}/suspend`);
        notificacion('Usuario suspendido correctamente.', { type: 'success' });
        showSuspendModal.value = false;
        await reloadTable();
    } catch (error: any) {
        const message = error.response?.data?.message || 'No fue posible suspender el usuario.';
        notificacion(message, { type: 'danger', title: 'Error' });
    } finally {
        suspending.value = false;
    }
}

async function handleActivate(usuario: Usuario) {
    try {
        await apiClient.post(`/configuracion/usuario/${usuario.id}/activate`);
        notificacion('Usuario activado correctamente.', { type: 'success' });
        await reloadTable();
    } catch (error: any) {
        const message = error.response?.data?.message || 'No fue posible activar el usuario.';
        notificacion(message, { type: 'danger', title: 'Error' });
    }
}

function downloadExcel() {
    if (!table.value) return;
    // @ts-ignore Assign XLSX global
    (window as any).XLSX = XLSX;
    table.value.download('xlsx', 'usuarios.xlsx', { sheetName: 'Usuarios' });
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
    saveForm.tipo_persona = 'STANDARD';
    saveForm.id_personal = '';
    saveForm.id_estudiante = '';
    saveForm.nombre = '';
    saveForm.apellido = '';
    saveForm.email = '';
    saveForm.usuario = '';
    saveForm.password = '';
    saveForm.id_rol = '';
    saveForm.fl_ver_informacion_privada = false;
}

function closeSaveModal() {
    if (saving.value) return;
    showSaveModal.value = false;
}

function closeDeleteModal() {
    if (deleting.value) return;
    showDeleteModal.value = false;
}

function closeChangePasswordModal() {
    if (changingPassword.value) return;
    showChangePasswordModal.value = false;
}

function closeSuspendModal() {
    if (suspending.value) return;
    showSuspendModal.value = false;
}

function updateSearchValue(value: string) {
    searchQuery.value = value;
}

watch(() => saveForm.tipo_persona, (newValue) => {
    // Limpiar campos cuando cambia el tipo de persona
    saveForm.id_personal = '';
    saveForm.id_estudiante = '';
    saveForm.nombre = '';
    saveForm.apellido = '';
    clearEstudiante();
    
    // Si cambia a ESTUDIANTE, limpiar el rol
    if (newValue === 'ESTUDIANTE') {
        saveForm.id_rol = '';
    }
});

// Watcher para búsqueda de estudiantes con debounce (similar a Select2)
watch(estudianteSearchQuery, (newValue) => {
    if (estudianteSearchTimeout.value) {
        clearTimeout(estudianteSearchTimeout.value);
    }

    if (saveForm.tipo_persona === 'ESTUDIANTE') {
        estudianteSearchTimeout.value = setTimeout(() => {
            searchEstudiantes(newValue);
        }, 250); // Delay similar a Select2
    } else {
        estudiantesOptions.value = [];
        estudianteDropdownOpen.value = false;
    }
});

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
                    <h1 class="h2 mb-1">Configuración / Usuarios</h1>
                    <p class="text-secondary mb-0">
                        Gestiona los usuarios del sistema; crea, edita, suspende o elimina según necesidad.
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

        <div class="usuario-page">
            <TableCard
                :loading="loading"
                :column-menu="columnMenu"
                :search-value="searchQuery"
                search-placeholder="Buscar usuarios..."
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

        <!-- Modal Save/Edit -->
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
                            <div class="mb-3" align="center">
                                <div class="mb-2">
                                    <img
                                        :key="`imagen-${editingId || 'new'}-${imagenUpload.preview}`"
                                        :src="imagenUpload.preview || '/images/sin_imagen.jpg'"
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
                                    <i class="ti ti-upload me-1"></i> Imagen de Perfil
                                    <input
                                        type="file"
                                        accept="image/*"
                                        style="display: none;"
                                        @change="imagenUpload.handleChange"
                                    />
                                </label>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label required">Tipo Persona</label>
                                    <select v-model="saveForm.tipo_persona" class="form-select" required>
                                        <option value="STANDARD">USUARIO STANDARD</option>
                                        <option value="DOCENTE">DOCENTE</option>
                                        <option value="ESTUDIANTE">ESTUDIANTE</option>
                                    </select>
                                </div>
                                <div v-if="showPersonalSelect" class="col-md-12 mb-3">
                                    <label class="form-label required">Docente</label>
                                    <select v-model="saveForm.id_personal" class="form-select" :required="showPersonalSelect">
                                        <option value="">Seleccionar...</option>
                                        <option v-for="p in personal" :key="p.id" :value="String(p.id)">
                                            {{ p.text }}
                                        </option>
                                    </select>
                                </div>
                                <div v-if="showEstudianteSelect" class="col-md-12 mb-3">
                                    <label class="form-label required">Estudiante</label>
                                    <div class="position-relative">
                                        <input
                                            v-model="estudianteSearchQuery"
                                            type="text"
                                            class="form-control"
                                            :class="{ 'is-invalid': showEstudianteSelect && !saveForm.id_estudiante && estudianteSearchQuery.length >= 3 && !estudianteSearchLoading }"
                                            placeholder="NOMBRES Y APELLIDOS || DNI (mínimo 3 caracteres)"
                                            :required="showEstudianteSelect"
                                            autocomplete="off"
                                            @focus="estudianteDropdownOpen = estudianteSearchQuery.length >= 3 && estudiantesOptions.length > 0"
                                            @blur="handleEstudianteBlur"
                                        />
                                        <div v-if="estudianteSearchLoading" class="position-absolute top-50 end-0 translate-middle-y me-2">
                                            <span class="spinner-border spinner-border-sm text-primary"></span>
                                        </div>
                                        <button
                                            v-if="saveForm.id_estudiante && !estudianteSearchLoading"
                                            type="button"
                                            class="btn btn-sm btn-link position-absolute top-50 end-0 translate-middle-y me-2"
                                            style="padding: 0; line-height: 1;"
                                            @click="clearEstudiante"
                                        >
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                    <div v-if="estudianteSearchQuery.length > 0 && estudianteSearchQuery.length < 3" class="form-text text-muted">
                                        Digite mínimo 3 caracteres
                                    </div>
                                    <div
                                        v-if="estudianteDropdownOpen && estudiantesOptions.length > 0"
                                        class="dropdown-menu show position-absolute w-100"
                                        style="max-height: 200px; overflow-y: auto; z-index: 1050;"
                                    >
                                        <button
                                            v-for="est in estudiantesOptions"
                                            :key="est.id"
                                            type="button"
                                            class="dropdown-item"
                                            @click="selectEstudiante(est)"
                                        >
                                            {{ est.text }}
                                        </button>
                                    </div>
                                    <div v-if="estudianteSearchQuery.length >= 3 && !estudianteSearchLoading && estudiantesOptions.length === 0 && estudianteDropdownOpen" class="form-text text-danger">
                                        No se encontraron estudiantes
                                    </div>
                                    <input
                                        v-model="saveForm.id_estudiante"
                                        type="hidden"
                                        :required="showEstudianteSelect"
                                    />
                                </div>
                                <div v-if="showNombreApellido" class="col-md-6 mb-3">
                                    <label class="form-label required">Nombre</label>
                                    <input
                                        v-model="saveForm.nombre"
                                        type="text"
                                        class="form-control"
                                        maxlength="200"
                                        :required="showNombreApellido"
                                    />
                                </div>
                                <div v-if="showNombreApellido" class="col-md-6 mb-3">
                                    <label class="form-label required">Apellidos</label>
                                    <input
                                        v-model="saveForm.apellido"
                                        type="text"
                                        class="form-control"
                                        maxlength="200"
                                        :required="showNombreApellido"
                                    />
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label required">Correo Electrónico</label>
                                    <input
                                        v-model="saveForm.email"
                                        type="email"
                                        class="form-control"
                                        maxlength="100"
                                        required
                                    />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Usuario</label>
                                    <input
                                        v-model="saveForm.usuario"
                                        type="text"
                                        class="form-control"
                                        maxlength="50"
                                        required
                                    />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" :class="{ required: !editingId }">Contraseña</label>
                                    <input
                                        v-model="saveForm.password"
                                        type="password"
                                        class="form-control"
                                        maxlength="255"
                                        :required="!editingId"
                                        :placeholder="editingId ? 'Dejar vacío para mantener la contraseña actual' : 'Contraseña'"
                                    />
                                    <small v-if="editingId" class="text-muted">Dejar vacío para mantener la contraseña actual</small>
                                </div>
                                <div v-if="showRolSelect" class="col-md-12 mb-3">
                                    <label class="form-label">Rol y Permisos</label>
                                    <select v-model="saveForm.id_rol" class="form-select">
                                        <option value="">Seleccionar...</option>
                                        <option v-for="rol in roles" :key="rol.id" :value="String(rol.id)">
                                            {{ rol.text }}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-12 mb-0">
                                    <div class="form-check">
                                        <input
                                            v-model="saveForm.fl_ver_informacion_privada"
                                            class="form-check-input"
                                            type="checkbox"
                                            id="fl_ver_informacion_privada"
                                        />
                                        <label class="form-check-label" for="fl_ver_informacion_privada">
                                            Ver información privada
                                        </label>
                                    </div>
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

        <!-- Modal Delete -->
        <AppModal
            :open="showDeleteModal"
            title="Eliminar usuario"
            size="sm"
            @close="closeDeleteModal"
        >
            <template #body>
                <p class="mb-0">
                    ¿Seguro que deseas eliminar el usuario
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

        <!-- Modal Change Password -->
        <AppModal
            :open="showChangePasswordModal"
            title="Cambiar contraseña"
            size="sm"
            @close="closeChangePasswordModal"
        >
            <template #body>
                <div class="text-center mb-3">
                    <i class="ti ti-lock" style="font-size: 3rem;"></i>
                </div>
                <div class="mb-3">
                    <label class="form-label">Usuario</label>
                    <input
                        :value="changePasswordTarget?.usuario"
                        type="text"
                        class="form-control"
                        disabled
                    />
                </div>
                <div class="mb-3">
                    <label class="form-label">Correo electrónico</label>
                    <input
                        :value="changePasswordTarget?.email"
                        type="email"
                        class="form-control"
                        disabled
                    />
                </div>
                <div class="mb-0">
                    <label class="form-label required">Nueva Contraseña</label>
                    <input
                        v-model="passwordForm.password"
                        type="password"
                        class="form-control"
                        maxlength="255"
                        required
                    />
                </div>
            </template>
            <template #footer>
                <div class="d-flex justify-content-between w-100">
                    <button type="button" class="btn btn-default btn-sm pull-left" @click="closeChangePasswordModal">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                    <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        :disabled="changingPassword"
                        @click="handleChangePasswordSubmit"
                    >
                        <span v-if="changingPassword" class="spinner-border spinner-border-sm me-2"></span>
                        Guardar
                    </button>
                </div>
            </template>
        </AppModal>

        <!-- Modal Suspend -->
        <AppModal
            :open="showSuspendModal"
            title="Suspender usuario"
            size="sm"
            @close="closeSuspendModal"
        >
            <template #body>
                <div class="text-center mb-3">
                    <i class="ti ti-pause" style="font-size: 3rem;"></i>
                </div>
                <div class="mb-3">
                    <label class="form-label">Usuario</label>
                    <input
                        :value="suspendTarget?.usuario"
                        type="text"
                        class="form-control"
                        disabled
                    />
                </div>
                <div class="mb-0">
                    <label class="form-label">Correo electrónico</label>
                    <input
                        :value="suspendTarget?.email"
                        type="email"
                        class="form-control"
                        disabled
                    />
                </div>
            </template>
            <template #footer>
                <div class="d-flex justify-content-between w-100">
                    <button type="button" class="btn btn-default btn-sm pull-left" @click="closeSuspendModal">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                    <button
                        type="button"
                        class="btn btn-warning btn-sm"
                        :disabled="suspending"
                        @click="handleSuspendConfirm"
                    >
                        <span v-if="suspending" class="spinner-border spinner-border-sm me-2"></span>
                        Suspender Ahora!
                    </button>
                </div>
            </template>
        </AppModal>
    </AuthenticatedLayout>
</template>
