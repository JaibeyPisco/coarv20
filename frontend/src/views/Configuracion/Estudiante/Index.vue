<script setup lang="ts">
import AuthenticatedLayout from '@/components/Layouts/AuthenticatedLayout.vue';
import {
    ref,
    reactive,
    computed,
    onMounted,
} from 'vue';
import apiClient from '@/api/axios';
import * as XLSX from 'xlsx';
import type { Estudiante } from '@/types/configuracion';
import AppModal from '@/components/Partial/AppModal.vue';
import VDataTableCard from '@/components/Table/VDataTableCard.vue';
import { useVuetifyTable } from '@/composables/useVuetifyTable';
import { notificacion } from '@/utils/notificacion';
import EstudianteForm from './EstudianteForm.vue';
import PadresForm from './PadresForm.vue';
import ApoderadosForm from './ApoderadosForm.vue';
import ImportarEstudianteModal from './ImportarEstudianteModal.vue';

// Helper para formatear condición del estudiante
function formatCondicionEstudianteChip(value: string): { label: string; color: string } {
    const config: Record<string, { label: string; color: string }> = {
        ESTUDIANTE: { label: 'ESTUDIANTE', color: 'primary' },
        EGRESADO: { label: 'EGRESADO', color: 'success' },
    };
    return config[value] ?? { label: value || 'ESTUDIANTE', color: 'info' };
}

const activeTab = ref('datos-estudiante');

const saveForm = reactive({
    dni: '',
    nombres: '',
    apellidos: '',
    grado: '',
    seccion: '',
    sexo: '',
    correo_electronico: '',
    fecha_nacimiento: '',
    codigo_estudiante: '',
    condicion_estudiante: 'ESTUDIANTE',
    obsv: '',
    lugar_nacimiento: '',
    fecha_caducidad_dni: '',
    num_telefonico: '',
    religion: '',
    region_domicilio_actual: '',
    provincia_domicilio_actual: '',
    distrito_domicilio_actual: '',
    direccion_domicilio_actual: '',
    referencia_domicilio_actual: '',
    lav: '',
    llaves: '',
    pabellon: '',
    ala: '',
    banos: '',
    monitor_acompana: '',
    cama_ropero: '',
    duchas: '',
    urinarios: '',
});
 
// Formularios de padres
const padreForm = reactive({
    id: null as number | null,
    vive: true,
    vive_con_estudiante: true,
    apellidos: '',
    nombres: '',
    dni: '',
    grado_instruccion: '',
    telefono: '',
    correo_electronico: '',
    ocupacion_actual: '',
    motivo_no_vive_con_estudiante: '',
});

const madreForm = reactive({
    id: null as number | null,
    vive: true,
    vive_con_estudiante: true,
    apellidos: '',
    nombres: '',
    dni: '',
    grado_instruccion: '',
    telefono: '',
    correo_electronico: '',
    ocupacion_actual: '',
    motivo_no_vive_con_estudiante: '',
});

const apoderadoRolPadreForm = reactive({
    id: null as number | null,
    parentesco_estudiante: '',
    apellidos: '',
    nombres: '',
    dni: '',
    telefono: '',
    tipo_familia: '',
});

// Lista de apoderados
interface Apoderado {
    id?: number | null;
    apellidos: string;
    nombres: string;
    dni: string;
    numero_telefonico: string;
    grado_parentesco: string;
    legalizado: boolean;
}

const apoderados = ref<Apoderado[]>([]);

const fotoFile = ref<File | null>(null);
const fotoPreview = ref<string>('');
const showSaveModal = ref(false);
const showDeleteModal = ref(false);
const showImportModal = ref(false);
const editingId = ref<number | null>(null);
const deleteTarget = ref<Estudiante | null>(null);
const saving = ref(false);
const deleting = ref(false);

const saveModalTitle = computed(() => (editingId.value ? 'Editar estudiante' : 'Nuevo estudiante'));

// Headers de la tabla
const headers = [
    {
        title: 'ACCIONES',
        key: 'actions',
        sortable: false,
        width: '150px',
    },
    {
        title: 'DNI',
        key: 'dni',
        sortable: true,
        width: '120px',
    },
    {
        title: 'NOMBRE COMPLETO',
        key: 'estudiante',
        sortable: true,
    },
    {
        title: 'GRADO Y SECCIÓN',
        key: 'grado_seccion',
        sortable: false,
        align: 'center' as const,
        width: '150px',
    },
    {
        title: 'CORREO',
        key: 'correo_electronico',
        sortable: true,
    },
    {
        title: 'CONDICIÓN',
        key: 'condicion_estudiante',
        sortable: true,
        align: 'center' as const,
        width: '150px',
    },
];

// Composable de tabla
const table = useVuetifyTable<Estudiante>({
    apiURL: '/configuracion/estudiante',
    searchFields: ['dni', 'nombres', 'apellidos', 'estudiante', 'correo_electronico'],
    serverSidePagination: false,
    serverSideSorting: false,
    serverSideSearch: false,
});

// Inicializar menú de columnas
table.updateColumnMenu(headers);

async function reloadTable() {
    await table.reloadTable();
}

async function openCreateModal() {
    editingId.value = null;
    resetSaveForm();
    fotoPreview.value = '';
    fotoFile.value = null;
    activeTab.value = 'datos-estudiante';
    showSaveModal.value = true;
}

function openImportModal() {
    showImportModal.value = true;
}

function closeImportModal() {
    showImportModal.value = false;
}

async function handleImported() {
    await reloadTable();
}

async function openEditModal(estudianteData: Estudiante) {
    editingId.value = estudianteData.id;

    // Cargar datos completos del estudiante
    try {
        const response = await apiClient.get(
            `/configuracion/estudiante/editar/${estudianteData.id}`
        );
        const data = response.data;

        saveForm.dni = data.dni || '';
        saveForm.nombres = data.nombres || '';
        saveForm.apellidos = data.apellidos || '';
        saveForm.grado = data.grado ? String(data.grado) : '';
        saveForm.seccion = data.seccion || '';
        saveForm.sexo = data.sexo || '';
        saveForm.correo_electronico = data.correo_electronico || '';
        saveForm.fecha_nacimiento = data.fecha_nacimiento || '';
        saveForm.codigo_estudiante = data.codigo_estudiante || '';
        saveForm.condicion_estudiante = data.condicion_estudiante || 'ESTUDIANTE';
        saveForm.obsv = data.obsv || '';
        saveForm.lugar_nacimiento = data.lugar_nacimiento || '';
        saveForm.fecha_caducidad_dni = data.fecha_caducidad_dni || '';
        saveForm.num_telefonico = data.num_telefonico || '';
        saveForm.religion = data.religion || '';
        saveForm.region_domicilio_actual = data.region_domicilio_actual || '';
        saveForm.provincia_domicilio_actual = data.provincia_domicilio_actual || '';
        saveForm.distrito_domicilio_actual = data.distrito_domicilio_actual || '';
        saveForm.direccion_domicilio_actual = data.direccion_domicilio_actual || '';
        saveForm.referencia_domicilio_actual = data.referencia_domicilio_actual || '';
        saveForm.lav = data.lav || '';
        saveForm.llaves = data.llaves || '';
        saveForm.pabellon = data.pabellon || '';
        saveForm.ala = data.ala || '';
        saveForm.banos = data.banos || '';
        saveForm.monitor_acompana = data.monitor_acompana || '';
        saveForm.cama_ropero = data.cama_ropero || '';
        saveForm.duchas = data.duchas || '';
        saveForm.urinarios = data.urinarios || '';

        fotoFile.value = null;

        if (data.foto) {
            fotoPreview.value = data.foto_url || `/storage/estudiantes/${data.foto}`;
        } else {
            fotoPreview.value = '/images/sin_imagen.jpg';
        }

        // Cargar datos de padres
        if (data.padre) {
            padreForm.id = data.padre.id;
            padreForm.vive = data.padre.vive == '1';
            padreForm.vive_con_estudiante = data.padre.vive_con_estudiante == '1';
            padreForm.apellidos = data.padre.apellidos || '';
            padreForm.nombres = data.padre.nombres || '';
            padreForm.dni = data.padre.dni || '';
            padreForm.grado_instruccion = data.padre.grado_instruccion || '';
            padreForm.telefono = data.padre.telefono || '';
            padreForm.correo_electronico = data.padre.correo_electronico || '';
            padreForm.ocupacion_actual = data.padre.ocupacion_actual || '';
            padreForm.motivo_no_vive_con_estudiante = data.padre.motivo_no_vive_con_estudiante || '';
        }

        if (data.madre) {
            madreForm.id = data.madre.id;
            madreForm.vive = data.madre.vive == '1';
            madreForm.vive_con_estudiante = data.madre.vive_con_estudiante == '1';
            madreForm.apellidos = data.madre.apellidos || '';
            madreForm.nombres = data.madre.nombres || '';
            madreForm.dni = data.madre.dni || '';
            madreForm.grado_instruccion = data.madre.grado_instruccion || '';
            madreForm.telefono = data.madre.telefono || '';
            madreForm.correo_electronico = data.madre.correo_electronico || '';
            madreForm.ocupacion_actual = data.madre.ocupacion_actual || '';
            madreForm.motivo_no_vive_con_estudiante = data.madre.motivo_no_vive_con_estudiante || '';
        }

        if (data.padre_apoderado) {
            apoderadoRolPadreForm.id = data.padre_apoderado.id;
            apoderadoRolPadreForm.parentesco_estudiante = data.padre_apoderado.parentesco_estudiante || '';
            apoderadoRolPadreForm.apellidos = data.padre_apoderado.apellidos || '';
            apoderadoRolPadreForm.nombres = data.padre_apoderado.nombres || '';
            apoderadoRolPadreForm.dni = data.padre_apoderado.dni || '';
            apoderadoRolPadreForm.telefono = data.padre_apoderado.telefono || '';
            apoderadoRolPadreForm.tipo_familia = data.padre_apoderado.tipo_familia || '';
        }

        // Cargar apoderados
        if (data.apoderados && Array.isArray(data.apoderados)) {
            apoderados.value = data.apoderados.map((ap: Apoderado & { telefono?: string; parentesco_estudiante?: string; fl_legalizado?: number | boolean }) => ({
                id: ap.id || null,
                apellidos: ap.apellidos || '',
                nombres: ap.nombres || '',
                dni: ap.dni || '',
                numero_telefonico: ap.numero_telefonico || ap.telefono || '',
                grado_parentesco: ap.grado_parentesco || ap.parentesco_estudiante || '',
                legalizado: Boolean(ap.legalizado) || Boolean(ap.fl_legalizado),
            }));
        } else {
            apoderados.value = [];
        }
    } catch (error: any) {
        notificacion('Error al cargar los datos del estudiante.', {
            type: 'danger',
            title: 'Error',
        });
        console.error('Error cargando estudiante:', error);
        return;
    }

    activeTab.value = 'datos-estudiante';
    showSaveModal.value = true;
}

function openDeleteModal(estudianteData: Estudiante) {
    deleteTarget.value = estudianteData;
    showDeleteModal.value = true;
}

function handleFotoChange(file: File) {
    fotoFile.value = file;

    const reader = new FileReader();
    reader.onload = (e) => {
        fotoPreview.value = e.target?.result as string;
    };
    reader.readAsDataURL(file);
}

function generateCodigoEstudiante() {
    // Generar código aleatorio de 8 caracteres
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let result = '';
    for (let i = 0; i < 8; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    saveForm.codigo_estudiante = result;
}

async function handleSaveSubmit() {
    if (!saveForm.dni.trim()) {
        notificacion('El DNI es obligatorio.', { type: 'danger', title: 'Validación' });
        return;
    }

    if (!saveForm.nombres.trim()) {
        notificacion('Los nombres son obligatorios.', { type: 'danger', title: 'Validación' });
        return;
    }

    if (!saveForm.apellidos.trim()) {
        notificacion('Los apellidos son obligatorios.', { type: 'danger', title: 'Validación' });
        return;
    }

    if (!saveForm.grado) {
        notificacion('El grado es obligatorio.', { type: 'danger', title: 'Validación' });
        return;
    }

    if (!saveForm.seccion) {
        notificacion('La sección es obligatoria.', { type: 'danger', title: 'Validación' });
        return;
    }

    if (!saveForm.sexo) {
        notificacion('El sexo es obligatorio.', { type: 'danger', title: 'Validación' });
        return;
    }

    if (!saveForm.correo_electronico.trim()) {
        notificacion('El correo electrónico es obligatorio.', {
            type: 'danger',
            title: 'Validación',
        });
        return;
    }

    const formData = new FormData();
    formData.append('dni', saveForm.dni.trim());
    formData.append('nombres', saveForm.nombres.trim());
    formData.append('apellidos', saveForm.apellidos.trim());
    formData.append('grado', String(saveForm.grado));
    formData.append('seccion', saveForm.seccion);
    formData.append('sexo', saveForm.sexo);
    formData.append('correo_electronico', saveForm.correo_electronico.trim());

    if (saveForm.fecha_nacimiento) {
        formData.append('fecha_nacimiento', saveForm.fecha_nacimiento);
    }

    if (saveForm.codigo_estudiante) {
        formData.append('codigo_estudiante', saveForm.codigo_estudiante.trim());
    }

    formData.append('condicion_estudiante', saveForm.condicion_estudiante);

    if (saveForm.obsv) {
        formData.append('obsv', saveForm.obsv.trim());
    }

    if (saveForm.lugar_nacimiento) {
        formData.append('lugar_nacimiento', saveForm.lugar_nacimiento.trim());
    }

    if (saveForm.fecha_caducidad_dni) {
        formData.append('fecha_caducidad_dni', saveForm.fecha_caducidad_dni);
    }

    if (saveForm.num_telefonico) {
        formData.append('num_telefonico', saveForm.num_telefonico.trim());
    }

    if (saveForm.religion) {
        formData.append('religion', saveForm.religion.trim());
    }

    if (saveForm.region_domicilio_actual) {
        formData.append('region_domicilio_actual', saveForm.region_domicilio_actual.trim());
    }

    if (saveForm.provincia_domicilio_actual) {
        formData.append('provincia_domicilio_actual', saveForm.provincia_domicilio_actual.trim());
    }

    if (saveForm.distrito_domicilio_actual) {
        formData.append('distrito_domicilio_actual', saveForm.distrito_domicilio_actual.trim());
    }

    if (saveForm.direccion_domicilio_actual) {
        formData.append('direccion_domicilio_actual', saveForm.direccion_domicilio_actual.trim());
    }

    if (saveForm.referencia_domicilio_actual) {
        formData.append('referencia_domicilio_actual', saveForm.referencia_domicilio_actual.trim());
    }

    if (saveForm.lav) {
        formData.append('lav', saveForm.lav.trim());
    }

    if (saveForm.llaves) {
        formData.append('llaves', saveForm.llaves.trim());
    }

    if (saveForm.pabellon) {
        formData.append('pabellon', saveForm.pabellon.trim());
    }

    if (saveForm.ala) {
        formData.append('ala', saveForm.ala.trim());
    }

    if (saveForm.banos) {
        formData.append('banos', saveForm.banos.trim());
    }

    if (saveForm.monitor_acompana) {
        formData.append('monitor_acompana', saveForm.monitor_acompana.trim());
    }

    if (saveForm.cama_ropero) {
        formData.append('cama_ropero', saveForm.cama_ropero.trim());
    }

    if (saveForm.duchas) {
        formData.append('duchas', saveForm.duchas.trim());
    }

    if (saveForm.urinarios) {
        formData.append('urinarios', saveForm.urinarios.trim());
    }

    if (fotoFile.value) {
        formData.append('foto', fotoFile.value);
    }

    // Agregar datos de padres
    if (padreForm.id) {
        formData.append('padre_id', String(padreForm.id));
    }
    formData.append('padre_vivo', padreForm.vive ? '1' : '0');
    formData.append('padre_con_estudiante', padreForm.vive_con_estudiante ? '1' : '0');
    formData.append('nombres_padre', padreForm.nombres);
    formData.append('apellidos_padre', padreForm.apellidos);
    formData.append('dni_padre', padreForm.dni);
    formData.append('grado_instruccion_padre', padreForm.grado_instruccion);
    formData.append('ocupacion_actual_padre', padreForm.ocupacion_actual);
    formData.append('num_celular_padre', padreForm.telefono);
    formData.append('correo_electronico_padre', padreForm.correo_electronico);
    formData.append('motivo_padre_no_vive_con_estudiante', padreForm.motivo_no_vive_con_estudiante);

    if (madreForm.id) {
        formData.append('madre_id', String(madreForm.id));
    }
    formData.append('madre_viva', madreForm.vive ? '1' : '0');
    formData.append('madre_con_estudiante', madreForm.vive_con_estudiante ? '1' : '0');
    formData.append('nombres_madre', madreForm.nombres);
    formData.append('apellidos_madre', madreForm.apellidos);
    formData.append('dni_madre', madreForm.dni);
    formData.append('grado_instruccion_madre', madreForm.grado_instruccion);
    formData.append('ocupacion_actual_madre', madreForm.ocupacion_actual);
    formData.append('num_celular_madre', madreForm.telefono);
    formData.append('correo_electronico_madre', madreForm.correo_electronico);
    formData.append('motivo_madre_no_vive_con_estudiante', madreForm.motivo_no_vive_con_estudiante);

    if (apoderadoRolPadreForm.id) {
        formData.append('apoderado_rol_padre_madre_id', String(apoderadoRolPadreForm.id));
    }
    formData.append('parentesco_con_apoderado', apoderadoRolPadreForm.parentesco_estudiante);
    formData.append('nombres_apoderado', apoderadoRolPadreForm.nombres);
    formData.append('apellidos_apoderado', apoderadoRolPadreForm.apellidos);
    formData.append('dni_apoderado', apoderadoRolPadreForm.dni);
    formData.append('num_celular_apoderado', apoderadoRolPadreForm.telefono);
    formData.append('tipo_familia', apoderadoRolPadreForm.tipo_familia);

    // Agregar apoderados como JSON
    formData.append('apoderados', JSON.stringify(apoderados.value));

    saving.value = true;

    try {
        // Si está editando, agregar el ID al formData
        if (editingId.value) {
            formData.append('id', String(editingId.value));
        }

        // Usar la misma ruta para crear y actualizar
        await apiClient.post('/configuracion/estudiante/save', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        notificacion(
            editingId.value
                ? 'Estudiante actualizado correctamente.'
                : 'Estudiante registrado correctamente.',
            { type: 'success' }
        );

        showSaveModal.value = false;
        await reloadTable();
    } catch (error: any) {
        if (error.response?.data?.errors) {
            const errors = error.response.data.errors;
            const firstError = Object.values(errors)[0] as string[];
            const message = firstError?.[0] || 'Error de validación';
            notificacion(message, { type: 'danger', title: 'Validación' });
        } else {
            const message =
                error.response?.data?.message || 'Ocurrió un inconveniente al guardar el registro.';
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
        await apiClient.delete(`/configuracion/estudiante/${deleteTarget.value.id}`);
        notificacion('Estudiante eliminado correctamente.', { type: 'success' });
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
    table.downloadExcel('estudiantes.xlsx', 'Estudiantes');
}

function printTable() {
    table.printTable();
}

function toggleColumnVisibility(key: string) {
    table.toggleColumnVisibility(key);
}

function updateSearchValue(value: string) {
    table.searchQuery.value = value;
    table.applySearch(value);
}

function resetSaveForm() {
    saveForm.dni = '';
    saveForm.nombres = '';
    saveForm.apellidos = '';
    saveForm.grado = '';
    saveForm.seccion = '';
    saveForm.sexo = '';
    saveForm.correo_electronico = '';
    saveForm.fecha_nacimiento = '';
    saveForm.codigo_estudiante = '';
    saveForm.condicion_estudiante = 'ESTUDIANTE';
    saveForm.obsv = '';
    saveForm.lugar_nacimiento = '';
    saveForm.fecha_caducidad_dni = '';
    saveForm.num_telefonico = '';
    saveForm.religion = '';
    saveForm.region_domicilio_actual = '';
    saveForm.provincia_domicilio_actual = '';
    saveForm.distrito_domicilio_actual = '';
    saveForm.direccion_domicilio_actual = '';
    saveForm.referencia_domicilio_actual = '';
    saveForm.lav = '';
    saveForm.llaves = '';
    saveForm.pabellon = '';
    saveForm.ala = '';
    saveForm.banos = '';
    saveForm.monitor_acompana = '';
    saveForm.cama_ropero = '';
    saveForm.duchas = '';
    saveForm.urinarios = '';

    // Reset padres
    padreForm.id = null;
    padreForm.vive = true;
    padreForm.vive_con_estudiante = true;
    padreForm.apellidos = '';
    padreForm.nombres = '';
    padreForm.dni = '';
    padreForm.grado_instruccion = '';
    padreForm.telefono = '';
    padreForm.correo_electronico = '';
    padreForm.ocupacion_actual = '';
    padreForm.motivo_no_vive_con_estudiante = '';

    madreForm.id = null;
    madreForm.vive = true;
    madreForm.vive_con_estudiante = true;
    madreForm.apellidos = '';
    madreForm.nombres = '';
    madreForm.dni = '';
    madreForm.grado_instruccion = '';
    madreForm.telefono = '';
    madreForm.correo_electronico = '';
    madreForm.ocupacion_actual = '';
    madreForm.motivo_no_vive_con_estudiante = '';

    apoderadoRolPadreForm.id = null;
    apoderadoRolPadreForm.parentesco_estudiante = '';
    apoderadoRolPadreForm.apellidos = '';
    apoderadoRolPadreForm.nombres = '';
    apoderadoRolPadreForm.dni = '';
    apoderadoRolPadreForm.telefono = '';
    apoderadoRolPadreForm.tipo_familia = '';

    // Reset apoderados
    apoderados.value = [];
}


function closeSaveModal() {
    if (saving.value) return;
    showSaveModal.value = false;
}

function closeDeleteModal() {
    if (deleting.value) return;
    showDeleteModal.value = false;
}

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
                            <h1 class="text-h5 font-weight-bold mb-2">Estudiantes</h1>
                            <p class="text-body-2 text-medium-emphasis mb-0">
                                Gestiona los estudiantes; crea, edita o elimina según necesidad.
                            </p>
                        </div>
                        <div class="d-flex ga-2">
                            <v-btn
                                color="success"
                                prepend-icon="mdi-upload"
                                variant="flat"
                                size="default"
                                @click="openImportModal"
                                aria-label="Importar estudiantes"
                                class="text-none"
                            >
                                Importar
                            </v-btn>
                            <v-btn
                                color="primary"
                                prepend-icon="mdi-plus"
                                variant="flat"
                                size="default"
                                @click="openCreateModal"
                                aria-label="Crear nuevo estudiante"
                                class="text-none"
                            >
                                Nuevo
                            </v-btn>
                        </div>
                    </div>
                </v-card-text>
            </v-card>
            <v-card rounded="lg" elevation="1">
                <VDataTableCard
                    :loading="table.loading.value"
                    :column-menu="table.columnMenu.value"
                    :search-value="table.searchQuery.value"
                    search-placeholder="Buscar estudiante..."
                    @print="printTable"
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

                        <template #item.estudiante="{ item }">
                            {{ item.estudiante || `${item.nombres || ''} ${item.apellidos || ''}`.trim() || '—' }}
                        </template>

                        <template #item.grado_seccion="{ item }">
                            {{ item.grado && item.seccion ? `${item.grado}° ${item.seccion}` : '—' }}
                        </template>

                        <template #item.condicion_estudiante="{ value }">
                            <v-chip
                                :color="formatCondicionEstudianteChip(value || 'ESTUDIANTE').color"
                                size="small"
                                variant="flat"
                                class="text-uppercase font-weight-medium"
                            >
                                {{ formatCondicionEstudianteChip(value || 'ESTUDIANTE').label }}
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
        </v-container>

        <AppModal :open="showSaveModal" :title="saveModalTitle" size="fullscreen" @close="closeSaveModal">
            <template #body>
                <v-tabs v-model="activeTab" bg-color="primary" stacked class="mb-4">
                    <v-tab value="datos-estudiante">
                        <v-icon>mdi-account</v-icon>
                        <span class="text-caption">Datos del Estudiante</span>
                    </v-tab>
                    <v-tab value="padres">
                        <v-icon>mdi-account-group</v-icon>
                        <span class="text-caption">Padres</span>
                    </v-tab>
                    <v-tab value="apoderados">
                        <v-icon>mdi-account-supervisor</v-icon>
                        <span class="text-caption">Apoderados</span>
                    </v-tab>
                </v-tabs>

                <v-window v-model="activeTab">
                    <v-window-item value="datos-estudiante">
                        <EstudianteForm
                            v-model="saveForm"
                            :foto-preview="fotoPreview"
                            :editing-id="editingId"
                            @foto-change="handleFotoChange"
                            @generate-codigo="generateCodigoEstudiante"
                        />
                    </v-window-item>

                    <v-window-item value="padres">
                        <PadresForm
                            :padre-form="padreForm"
                            :madre-form="madreForm"
                            :apoderado-rol-padre-form="apoderadoRolPadreForm"
                            @update:padre-form="(value) => Object.assign(padreForm, value)"
                            @update:madre-form="(value) => Object.assign(madreForm, value)"
                            @update:apoderado-rol-padre-form="(value) => Object.assign(apoderadoRolPadreForm, value)"
                        />
                    </v-window-item>

                    <v-window-item value="apoderados">
                        <ApoderadosForm v-model="apoderados" />
                    </v-window-item>
                </v-window>
            </template>
            <template #footer>
                <div class="d-flex justify-space-between w-100">
                    <v-btn
                        variant="outlined"
                        @click="closeSaveModal"
                        :disabled="saving"
                        class="text-none"
                    >
                        <v-icon start>mdi-close</v-icon>
                        Cancelar
                    </v-btn>
                    <v-btn
                        color="primary"
                        variant="flat"
                        :loading="saving"
                        :disabled="saving"
                        @click="handleSaveSubmit"
                        class="text-none"
                    >
                        <v-icon start>mdi-content-save</v-icon>
                        {{ editingId ? 'Actualizar' : 'Guardar' }}
                    </v-btn>
                </div>
            </template>
        </AppModal>

        <AppModal
            :open="showDeleteModal"
            title="Eliminar estudiante"
            size="sm"
            @close="closeDeleteModal"
        >
            <template #body>
                <p class="mb-0">
                    ¿Seguro que deseas eliminar el estudiante
                    <strong>{{ deleteTarget?.nombres }} {{ deleteTarget?.apellidos }}</strong
                    >? Esta acción no se puede deshacer.
                </p>
            </template>
            <template #footer>
                <div class="d-flex justify-space-between w-100">
                    <v-btn
                        variant="outlined"
                        @click="closeDeleteModal"
                        :disabled="deleting"
                        class="text-none"
                    >
                        <v-icon start>mdi-close</v-icon>
                        Cancelar
                    </v-btn>
                    <v-btn
                        color="error"
                        variant="flat"
                        :loading="deleting"
                        :disabled="deleting"
                        @click="handleDeleteConfirm"
                        class="text-none"
                    >
                        <v-icon start>mdi-delete</v-icon>
                        Eliminar
                    </v-btn>
                </div>
            </template>
        </AppModal>

        <ImportarEstudianteModal
            :open="showImportModal"
            @close="closeImportModal"
            @imported="handleImported"
        />
    </AuthenticatedLayout>
</template>
