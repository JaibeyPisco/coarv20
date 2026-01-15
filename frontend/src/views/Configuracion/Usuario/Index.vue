<script setup lang="ts">
import AuthenticatedLayout from '@/components/Layouts/AuthenticatedLayout.vue';
import { ref, reactive, computed, onMounted, onActivated, watch, nextTick } from 'vue';
import * as XLSX from 'xlsx';
import type { Usuario } from '@/types/configuracion';
import AppModal from '@/components/Partial/AppModal.vue';
import VDataTableCard from '@/components/Table/VDataTableCard.vue';
import { notificacion } from '@/utils/notificacion';
import { useImageUpload } from '@/composables/useImageUpload';
import { useVuetifyTable } from '@/composables/useVuetifyTable';
import apiClient from '@/api/axios';

// Selects para dropdowns
const personal = ref<{ id: number; text: string }[]>([]);
const roles = ref<{ id: number; text: string }[]>([]);

// Para búsqueda de estudiantes (v-autocomplete)
const estudianteSearchQuery = ref('');
const estudiantesOptions = ref<{ id: number; text: string }[]>([]);
const estudianteSearchLoading = ref(false);
const estudianteSelected = ref<{ id: number; text: string } | null>(null);

// Form
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

const imagenUpload = useImageUpload('/images/sin_imagen.jpg');
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

// Headers de la tabla
const headers = [
    {
        title: 'ACCIONES',
        key: 'actions',
        sortable: false,
        width: '150px',
    },
    {
        title: 'ESTADO',
        key: 'fl_suspendido',
        sortable: true,
        align: 'center' as const,
        width: '120px',
    },
    {
        title: 'NOMBRE',
        key: 'nombre',
        sortable: true,
    },
    {
        title: 'APELLIDO',
        key: 'apellido',
        sortable: true,
    },
    {
        title: 'USUARIO',
        key: 'usuario',
        sortable: true,
        width: '150px',
    },
    {
        title: 'EMAIL',
        key: 'email',
        sortable: true,
    },
    {
        title: 'TIPO PERSONA',
        key: 'tipo_persona',
        sortable: true,
        align: 'center' as const,
        width: '150px',
    },
    {
        title: 'ROL',
        key: 'rol',
        sortable: true,
        width: '180px',
    },
];

// Composable de tabla
const table = useVuetifyTable<Usuario>({
    apiURL: '/configuracion/usuario',
    searchFields: ['nombre', 'apellido', 'usuario', 'email'],
    serverSidePagination: false,
    serverSideSorting: false,
    serverSideSearch: false,
});

// Inicializar menú de columnas
table.updateColumnMenu(headers);

// Funciones para selects
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

// Función para buscar estudiantes (v-autocomplete)
async function searchEstudiantes(searchTerm: string) {
    if (!searchTerm || searchTerm.length < 3) {
        estudiantesOptions.value = [];
        return [];
    }

    estudianteSearchLoading.value = true;

    try {
        const response = await apiClient.get('/configuracion/estudiante/getSelect', {
            params: { buscar: searchTerm },
        });

        const data = Array.isArray(response.data) 
            ? response.data 
            : (Array.isArray(response.data?.data) ? response.data.data : []);

        estudiantesOptions.value = data.map((item: any) => ({
            id: item.id ?? item.value,
            text: item.text ?? item.label ?? `${item.nombre ?? ''} ${item.apellido ?? ''} ${item.dni ?? ''}`.trim(),
        }));

        return estudiantesOptions.value;
    } catch (error: any) {
        console.error('Error buscando estudiantes:', error);
        estudiantesOptions.value = [];
        return [];
    } finally {
        estudianteSearchLoading.value = false;
    }
}

function clearEstudiante() {
    saveForm.id_estudiante = '';
    estudianteSelected.value = null;
    estudianteSearchQuery.value = '';
    estudiantesOptions.value = [];
}

// Funciones de modales
async function openCreateModal() {
    editingId.value = null;
    resetSaveForm();
    imagenUpload.reset();
    clearEstudiante();
    await loadSelects();
    showSaveModal.value = true;
}

async function openEditModal(usuarioData: Usuario) {
    editingId.value = usuarioData.id;
    await loadSelects();
    
    saveForm.tipo_persona = usuarioData.tipo_persona || 'STANDARD';
    
    await nextTick();
    
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
    
    if (usuarioData.tipo_persona === 'ESTUDIANTE' && usuarioData.id_estudiante) {
        estudianteSearchQuery.value = usuarioData.estudiante_nombre || `${usuarioData.nombre || ''} ${usuarioData.apellido || ''}`.trim() || '';
        estudianteSelected.value = { id: usuarioData.id_estudiante, text: estudianteSearchQuery.value };
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

    if (saveForm.tipo_persona === 'ESTUDIANTE' && !saveForm.id_estudiante) {
        notificacion('El estudiante es obligatorio para tipo ESTUDIANTE.', { type: 'danger', title: 'Validación' });
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
        const usuarioActual = table.items.value.find((u: Usuario) => u.id === editingId.value);
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
        await apiClient.delete(`/configuracion/usuario/${deleteTarget.value.id}`);
        notificacion('Usuario eliminado correctamente.', { type: 'success' });
        showDeleteModal.value = false;
        await table.reloadTable();
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
        await table.reloadTable();
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
        await table.reloadTable();
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
        await table.reloadTable();
    } catch (error: any) {
        const message = error.response?.data?.message || 'No fue posible activar el usuario.';
        notificacion(message, { type: 'danger', title: 'Error' });
    }
}

// Funciones
const updateSearchValue = (value: string) => {
    table.searchQuery.value = value;
    table.applySearch(value);
};

const downloadExcel = () => {
    table.downloadExcel('usuarios.xlsx', 'Usuarios');
};

const toggleColumnVisibility = (key: string) => {
    table.toggleColumnVisibility(key);
};

// Watchers
watch(() => saveForm.tipo_persona, (newValue) => {
    saveForm.id_personal = '';
    saveForm.id_estudiante = '';
    saveForm.nombre = '';
    saveForm.apellido = '';
    clearEstudiante();
    
    if (newValue === 'ESTUDIANTE') {
        saveForm.id_rol = '';
    }
});

watch(estudianteSelected, (newValue) => {
    if (newValue) {
        saveForm.id_estudiante = String(newValue.id);
        estudianteSearchQuery.value = newValue.text;
    } else {
        saveForm.id_estudiante = '';
        estudianteSearchQuery.value = '';
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
                            <h1 class="text-h5 font-weight-bold mb-2">Usuarios</h1>
                            <p class="text-body-2 text-medium-emphasis mb-0">
                                Gestiona los usuarios del sistema; crea, edita, suspende o elimina según necesidad.
                            </p>
                        </div>
                        <v-btn
                            color="primary"
                            prepend-icon="mdi-plus"
                            variant="flat"
                            size="default"
                            @click="openCreateModal"
                            aria-label="Crear nuevo usuario"
                            class="text-none"
                        >
                            Nuevo Usuario
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
                    search-placeholder="Buscar usuarios..."
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
                                            prepend-icon="mdi-lock"
                                            title="Cambiar Contraseña"
                                            @click="openChangePasswordModal(item)"
                                        />
                                        <v-list-item
                                            v-if="item.fl_suspendido"
                                            prepend-icon="mdi-play"
                                            title="Activar"
                                            class="text-success"
                                            @click="handleActivate(item)"
                                        />
                                        <v-list-item
                                            v-else
                                            prepend-icon="mdi-pause"
                                            title="Suspender"
                                            class="text-warning"
                                            @click="openSuspendModal(item)"
                                        />
                                        <v-divider />
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

                        <template #item.fl_suspendido="{ value }">
                            <v-chip
                                :color="value ? 'warning' : 'success'"
                                size="small"
                                variant="flat"
                                class="text-uppercase font-weight-medium"
                            >
                                {{ value ? 'SUSPENDIDO' : 'ACTIVO' }}
                            </v-chip>
                        </template>

                        <template #item.tipo_persona="{ value }">
                            <v-chip
                                color="primary"
                                size="small"
                                variant="flat"
                                class="text-uppercase font-weight-medium"
                            >
                                {{ value || 'STANDARD' }}
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
                                            <label class="text-body-2 font-weight-medium mb-2 d-block">Imagen de Perfil</label>
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
                                            label="Examinar Imagen"
                                            prepend-icon="mdi-camera"
                                            variant="outlined"
                                            density="compact"
                                            accept="image/*"
                                            @change="imagenUpload.handleChange"
                                            hide-details
                                        />
                                    </v-card>
                                </v-col>
                                <v-col cols="12" md="9">
                                    <v-row>
                                        <v-col cols="12" md="4">
                                            <v-select
                                                v-model="saveForm.tipo_persona"
                                                label="Tipo de Persona"
                                                :items="[
                                                    { title: 'USUARIO STANDARD', value: 'STANDARD' },
                                                    { title: 'DOCENTE', value: 'DOCENTE' },
                                                    { title: 'ESTUDIANTE', value: 'ESTUDIANTE' },
                                                ]"
                                                required
                                                variant="outlined"
                                                density="compact"
                                            />
                                        </v-col>
                                        <v-col v-if="showPersonalSelect" cols="12">
                                            <v-select
                                                v-model="saveForm.id_personal"
                                                label="Docente"
                                                :items="personal.map(p => ({ title: p.text, value: String(p.id) }))"
                                                :required="showPersonalSelect"
                                                clearable
                                                variant="outlined"
                                                density="compact"
                                            />
                                        </v-col>
                                        <v-col v-if="showEstudianteSelect" cols="12">
                                            <v-autocomplete
                                                v-model="estudianteSelected"
                                                :items="estudiantesOptions"
                                                :search="estudianteSearchQuery"
                                                :loading="estudianteSearchLoading"
                                                label="Estudiante"
                                                placeholder="NOMBRES Y APELLIDOS || DNI (mínimo 3 caracteres)"
                                                :required="showEstudianteSelect"
                                                item-title="text"
                                                item-value="id"
                                                return-object
                                                clearable
                                                variant="outlined"
                                                density="compact"
                                                @update:search="(val) => { estudianteSearchQuery = val; if (val && val.length >= 3) searchEstudiantes(val); }"
                                            >
                                                <template #no-data>
                                                    <div class="pa-2 text-center">
                                                        {{ estudianteSearchQuery.length < 3 ? 'Digite mínimo 3 caracteres' : 'No se encontraron estudiantes' }}
                                                    </div>
                                                </template>
                                            </v-autocomplete>
                                            <input
                                                v-model="saveForm.id_estudiante"
                                                type="hidden"
                                                :required="showEstudianteSelect"
                                            />
                                        </v-col>
                                        <v-col v-if="showNombreApellido" cols="12" md="6">
                                            <v-text-field
                                                v-model="saveForm.nombre"
                                                label="Nombre"
                                                :required="showNombreApellido"
                                                maxlength="200"
                                                counter
                                                variant="outlined"
                                                density="compact"
                                            />
                                        </v-col>
                                        <v-col v-if="showNombreApellido" cols="12" md="6">
                                            <v-text-field
                                                v-model="saveForm.apellido"
                                                label="Apellidos"
                                                :required="showNombreApellido"
                                                maxlength="200"
                                                counter
                                                variant="outlined"
                                                density="compact"
                                            />
                                        </v-col>
                                        <v-col cols="12">
                                            <v-text-field
                                                v-model="saveForm.email"
                                                label="Correo Electrónico"
                                                type="email"
                                                required
                                                maxlength="100"
                                                counter
                                                variant="outlined"
                                                density="compact"
                                            />
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                v-model="saveForm.usuario"
                                                label="Usuario"
                                                required
                                                maxlength="50"
                                                counter
                                                variant="outlined"
                                                density="compact"
                                            />
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                v-model="saveForm.password"
                                                label="Contraseña"
                                                type="password"
                                                :required="!editingId"
                                                :placeholder="editingId ? 'Dejar vacío para mantener la contraseña actual' : 'Contraseña'"
                                                maxlength="255"
                                                counter
                                                variant="outlined"
                                                density="compact"
                                            />
                                            <small v-if="editingId" class="text-caption text-medium-emphasis d-block mt-1">
                                                Dejar vacío para mantener la contraseña actual
                                            </small>
                                        </v-col>
                                        <v-col v-if="showRolSelect" cols="12">
                                            <v-select
                                                v-model="saveForm.id_rol"
                                                label="Rol y Permisos"
                                                :items="roles.map(r => ({ title: r.text, value: String(r.id) }))"
                                                clearable
                                                variant="outlined"
                                                density="compact"
                                            />
                                        </v-col>
                                        <v-col cols="12">
                                            <v-checkbox
                                                v-model="saveForm.fl_ver_informacion_privada"
                                                label="Ver información privada"
                                                hide-details
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
                title="Eliminar Usuario"
                size="sm"
            >
                <template #body>
                    <v-container fluid class="pa-4">
                        <div class="text-center mb-4">
                            <v-icon icon="mdi-alert-circle" size="64" color="error" />
                        </div>
                        <p class="text-body-1 text-center">
                            ¿Está seguro que desea eliminar el usuario
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

            <!-- Modal Change Password -->
            <AppModal
                v-model:open="showChangePasswordModal"
                title="Cambiar Contraseña"
                size="sm"
            >
                <template #body>
                    <v-container fluid class="pa-4">
                        <div class="text-center mb-4">
                            <v-icon icon="mdi-lock" size="64" color="primary" />
                        </div>
                        <v-text-field
                            :model-value="changePasswordTarget?.usuario"
                            label="Usuario"
                            disabled
                            variant="outlined"
                            density="compact"
                            class="mb-4"
                        />
                        <v-text-field
                            :model-value="changePasswordTarget?.email"
                            label="Correo electrónico"
                            type="email"
                            disabled
                            variant="outlined"
                            density="compact"
                            class="mb-4"
                        />
                        <v-text-field
                            v-model="passwordForm.password"
                            label="Nueva Contraseña"
                            type="password"
                            required
                            maxlength="255"
                            counter
                            variant="outlined"
                            density="compact"
                        />
                    </v-container>
                </template>
                <template #footer>
                    <div class="d-flex justify-end ga-2">
                        <v-btn
                            variant="outlined"
                            @click="showChangePasswordModal = false"
                            :disabled="changingPassword"
                            class="text-none"
                        >
                            Cancelar
                        </v-btn>
                        <v-btn
                            color="primary"
                            variant="flat"
                            @click="handleChangePasswordSubmit"
                            :loading="changingPassword"
                            class="text-none"
                        >
                            Guardar
                        </v-btn>
                    </div>
                </template>
            </AppModal>

            <!-- Modal Suspend -->
            <AppModal
                v-model:open="showSuspendModal"
                title="Suspender Usuario"
                size="sm"
            >
                <template #body>
                    <v-container fluid class="pa-4">
                        <div class="text-center mb-4">
                            <v-icon icon="mdi-pause" size="64" color="warning" />
                        </div>
                        <v-text-field
                            :model-value="suspendTarget?.usuario"
                            label="Usuario"
                            disabled
                            variant="outlined"
                            density="compact"
                            class="mb-4"
                        />
                        <v-text-field
                            :model-value="suspendTarget?.email"
                            label="Correo electrónico"
                            type="email"
                            disabled
                            variant="outlined"
                            density="compact"
                        />
                    </v-container>
                </template>
                <template #footer>
                    <div class="d-flex justify-end ga-2">
                        <v-btn
                            variant="outlined"
                            @click="showSuspendModal = false"
                            :disabled="suspending"
                            class="text-none"
                        >
                            Cancelar
                        </v-btn>
                        <v-btn
                            color="warning"
                            variant="flat"
                            @click="handleSuspendConfirm"
                            :loading="suspending"
                            class="text-none"
                        >
                            Suspender Ahora!
                        </v-btn>
                    </div>
                </template>
            </AppModal>
        </v-container>
    </AuthenticatedLayout>
</template>
