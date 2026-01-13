<script setup lang="ts">
import AuthenticatedLayout from '@/components/Layouts/AuthenticatedLayout.vue';
import {
    ref,
    reactive,
    computed,
    onMounted,
    onBeforeUnmount,
    watch,
    nextTick,
} from 'vue';
import apiClient from '@/api/axios';
// @ts-expect-error -- tabulator-tables no proporciona tipos ES module
import { TabulatorFull as Tabulator } from 'tabulator-tables';
import * as XLSX from 'xlsx';
import type { Estudiante } from '@/types/configuracion';
import AppModal from '@/components/Partial/AppModal.vue';
import TableCard from '@/components/Table/TableCard.vue';
import { notificacion } from '@/utils/notificacion';
import EstudianteForm from './EstudianteForm.vue';
import PadresForm from './PadresForm.vue';
import ApoderadosForm from './ApoderadosForm.vue';
import ImportarEstudianteModal from './ImportarEstudianteModal.vue';

const tableEl = ref<HTMLElement | null>(null);
const table = ref<Tabulator | null>(null);
const estudiantes = ref<Estudiante[]>([]);
const loading = ref(false);
const searchQuery = ref('');
const columnMenu = ref<{ title: string; field: string; visible: boolean }[]>([]);
const recordSummary = ref('Mostrando 0 registros');

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
        title: 'DNI',
        field: 'dni',
        width: 120,
        headerSort: true,
        formatter: 'plaintext',
    },
    {
        title: 'NOMBRE COMPLETO',
        field: 'estudiante',
        minWidth: 250,
        headerSort: true,
        formatter: (cell: { getValue: () => unknown; getRow: () => { getData: () => Estudiante } }) => {
            const estudiante = cell.getValue() as string | undefined;
            if (!estudiante) {
                const data = cell.getRow().getData() as Estudiante;
                return `${data.nombres || ''} ${data.apellidos || ''}`.trim() || '—';
            }
            return estudiante;
        },
    },
    {
        title: 'GRADO Y SECCIÓN',
        field: 'grado_seccion',
        width: 150,
        headerHozAlign: 'center',
        hozAlign: 'center',
        formatter: (cell: { getValue: () => unknown; getRow: () => { getData: () => Estudiante } }) => {
            const gradoSeccion = cell.getValue() as string | undefined;
            if (gradoSeccion) return gradoSeccion;
            const data = cell.getRow().getData() as Estudiante;
            if (data.grado && data.seccion) {
                return `${data.grado}° ${data.seccion}`;
            }
            return '—';
        },
    },
    {
        title: 'CORREO',
        field: 'correo_electronico',
        minWidth: 200,
        headerSort: true,
        formatter: 'plaintext',
    },
    {
        title: 'CONDICIÓN',
        field: 'condicion_estudiante',
        width: 150,
        headerHozAlign: 'center',
        hozAlign: 'center',
        formatter: (cell: { getValue: () => unknown }) => {
            const value = (cell.getValue() as string) || 'ESTUDIANTE';
            const config: Record<string, { label: string; className: string }> = {
                ESTUDIANTE: {
                    label: 'ESTUDIANTE',
                    className: 'badge rounded-pill px-3 bg-blue-lt text-blue fw-semibold',
                },
                EGRESADO: {
                    label: 'EGRESADO',
                    className: 'badge rounded-pill px-3 bg-green-lt text-green fw-semibold',
                },
            };

            const { label, className } = config[value] ?? {
                label: value,
                className: 'badge rounded-pill px-3 bg-gray-lt text-secondary fw-semibold',
            };

            return `<span class="${className}">${label}</span>`;
        },
    },
];

async function initializeTable() {
    await nextTick();
    if (!tableEl.value) return;

    table.value = new Tabulator(tableEl.value, {
        layout: 'fitColumns',
        reactiveData: false,
        placeholder: 'No se encontraron registros',
        columns,
        printHeader: '<h4 class="mb-3">Listado de estudiantes</h4>',
        printFooter: '<small>Generado desde la intranet</small>',
        height: 'calc(100vh - 360px)',
        columnDefaults: {
            resizable: true,
        },
        ajaxURL: 'configuracion/estudiante',
        ajaxContentType: 'json',
        ajaxRequestFunc: async (url: string) => {
            const response = await apiClient.get(url);
            return response.data;
        },
        ajaxResponse: (_url: string, _params: unknown, response: { data?: Estudiante[] }) => {
            const data: Estudiante[] = response?.data ?? [];
            estudiantes.value = data;
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
    const columns = table.value.getColumns() as Array<{
        getDefinition: () => { title?: string };
        getField: () => string;
        isVisible: () => boolean;
    }>;
    columnMenu.value = columns.map((column) => ({
        title: column.getDefinition().title ?? '',
        field: column.getField(),
        visible: column.isVisible(),
    }));
}

function handleActionCellClick(e: MouseEvent, cell: { getRow: () => { getData: () => Estudiante } }) {
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
    const data = cell.getRow().getData() as Estudiante;

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
    await table.value.replaceData('configuracion/estudiante');
    applySearch(searchQuery.value);
    updateRecordSummary();
}

function applySearch(query: string) {
    const normalized = query.trim().toLowerCase();
    if (!table.value) return;

    if (!normalized) {
        table.value.clearFilter(true);
    } else {
        table.value.setFilter((rowData: Estudiante) => {
            const values = [
                rowData.dni,
                rowData.nombres,
                rowData.apellidos,
                rowData.estudiante,
                rowData.correo_electronico,
            ];
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
    const totalRows = table.value.getData().length || estudiantes.value.length;
    recordSummary.value = `Mostrando ${filteredRows} de ${totalRows} registros`;
}

async function openCreateModal() {
    editingId.value = null;
    resetSaveForm();
    fotoPreview.value = '';
    fotoFile.value = null;
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
    if (!table.value) return;
    // @ts-ignore Assign XLSX global
    (window as any).XLSX = XLSX;
    table.value.download('xlsx', 'estudiantes.xlsx', { sheetName: 'Estudiantes' });
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
        item.field === field ? { ...item, visible: column.isVisible() } : item
    );
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

function updateSearchValue(value: string) {
    searchQuery.value = value;
}

onMounted(async () => {
    // @ts-ignore expose for Tabulator download module
    (window as any).XLSX = XLSX;
    await initializeTable();
    document.addEventListener('click', handleGlobalClick);
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
                    <h1 class="h2 mb-1">Configuración / Estudiantes</h1>
                    <p class="text-secondary mb-0">
                        Gestiona los estudiantes; crea, edita o elimina según necesidad.
                    </p>
                </div>
                <div class="btn-group">
                    <button
                        type="button"
                        class="btn btn-success d-flex align-items-center gap-2"
                        @click="openImportModal"
                    >
                        <i class="ti ti-upload"></i>
                        Importar
                    </button>
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

        <div class="estudiante-page">
            <TableCard
                :loading="loading"
                :column-menu="columnMenu"
                :search-value="searchQuery"
                search-placeholder="Buscar estudiante..."
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

        <AppModal :open="showSaveModal" :title="saveModalTitle" size="full" @close="closeSaveModal">
            <template #body>
                <form class="space-y-3" @submit.prevent>
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link active"
                                data-bs-toggle="tab"
                                data-bs-target="#tab-datos-estudiante"
                                type="button"
                                role="tab"
                            >
                                Datos del estudiante
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link"
                                data-bs-toggle="tab"
                                data-bs-target="#tab-padres"
                                type="button"
                                role="tab"
                            >
                                Padres
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link"
                                data-bs-toggle="tab"
                                data-bs-target="#tab-apoderados"
                                type="button"
                                role="tab"
                            >
                                Apoderados
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div
                            class="tab-pane fade show active"
                            id="tab-datos-estudiante"
                            role="tabpanel"
                        >
                            <EstudianteForm
                                v-model="saveForm"
                                :foto-preview="fotoPreview"
                                :editing-id="editingId"
                                @foto-change="handleFotoChange"
                                @generate-codigo="generateCodigoEstudiante"
                            />
                        </div>

                        <div class="tab-pane fade" id="tab-padres" role="tabpanel">
                            <PadresForm
                                :padre-form="padreForm"
                                :madre-form="madreForm"
                                :apoderado-rol-padre-form="apoderadoRolPadreForm"
                                @update:padre-form="(value) => Object.assign(padreForm, value)"
                                @update:madre-form="(value) => Object.assign(madreForm, value)"
                                @update:apoderado-rol-padre-form="(value) => Object.assign(apoderadoRolPadreForm, value)"
                            />
                        </div>
                        <div class="tab-pane fade" id="tab-apoderados" role="tabpanel">
                            <ApoderadosForm v-model="apoderados" />
                        </div>
                    </div>
                </form>
            </template>
            <template #footer>
                <div class="d-flex justify-content-between w-100">
                    <button
                        type="button"
                        class="btn btn-default btn-sm pull-left"
                        @click="closeSaveModal"
                    >
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
                <div class="d-flex justify-content-between w-100">
                    <button
                        type="button"
                        class="btn btn-default btn-sm pull-left"
                        @click="closeDeleteModal"
                    >
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

        <ImportarEstudianteModal
            :open="showImportModal"
            @close="closeImportModal"
            @imported="handleImported"
        />
    </AuthenticatedLayout>
</template>
