<script setup lang="ts">
import { ref, shallowRef } from 'vue';
import AppModal from '@/components/Partial/AppModal.vue';
import apiClient from '@/api/axios';
import { notificacion } from '@/utils/notificacion';
import excelReader from '@/utils/ExcelReader';
import { useVuetifyTable } from '@/composables/useVuetifyTable';
import type { Estudiante } from '@/types/configuracion';

interface Props {
    open: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    close: [];
    imported: [];
}>();

const fileInput = ref<HTMLInputElement | null>(null);
const importing = ref(false);

  const density = shallowRef('default')


const handleFileChange  = async (event: Event) => {

    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (!file) {
        return;
    }

    const extension = file.name.split('.').pop()?.toLowerCase();
    if (extension !== 'xlsx' && extension !== 'xls') {
        notificacion('El archivo debe ser Excel (.xlsx o .xls)', {
            type: 'warning',
            title: 'Formato inválido',
        });
        if (fileInput.value) {
            fileInput.value.value = '';
        }

        return;
    }
    

    await processExcel(file)


     
    
}

const processExcel =  async (file: File) => {

    const header = [
        'numero',
        'estado',
        'apellidos',
        'nombre',
        'obsv',
        'grado',
        'seccion',
        'dni',
        'foto',
        'sexo',
        'email',
        'fecha_nacimiento',
        'lav',
        'llaves',
        'pabellon',
        'ala',
        'cama_ropero',
        'duchas',
        'banos',
        'urinarios',
        'monitor',
        'lugar_nacimiento',
        'fecha_caducidad_dni',
        'telefono',
        'religion',
        'region',
        'provincia',
        'distrito',
        'direccion_domicilio',
        'referencia_domicilio',
        'madre_viva',
        'estudiante_vive_con_madre',
        'apellidos_madre',
        'nombres_madre',
        'dni_madre',
        'grado_instruccion_madre',
        'ocupacion_madre',
        'telefono_madre',
        'email_madre',

        'motivo_estudiante_no_con_vive_madre',
        
        
        'padre_vivo',
        'estudiante_vive_con_padre',
        'apellidos_padre',
        'nombres_padre',
        'dni_padre',
        'grado_instruccion_padre',
        'ocupacion_padre',
        'telefono_padre',
        'email_padre',


        'motivo_estudiante_no_con_vive_padre',

        'parentesco_apoderado',
        'apellido_apoderado',
        'nombre_apoderado',
        'dni_apoderado',
        'telefono_apoderado',
        'tipo_familia_apoderado',


        'apellido_apoderado1',
        'nombre_apoderado1',
        'dni_apoderado1',
        'telefono_apoderado1',
        'parentesco_apoderado1',
        'legalizado_apoderado1',

         'apellido_apoderado2',
        'nombre_apoderado2',
        'dni_apoderado2',
        'telefono_apoderado2',
        'parentesco_apoderado2',
        'legalizado_apoderado2',
        

         'apellido_apoderado3',
        'nombre_apoderado3',
        'dni_apoderado3',
        'telefono_apoderado3',
        'parentesco_apoderado3',
        'legalizado_apoderado3',

         'apellido_apoderado4',
        'nombre_apoderado4',
        'dni_apoderado4',
        'telefono_apoderado4',
        'parentesco_apoderado4',
        'legalizado_apoderado4',

         'apellido_apoderado5',
        'nombre_apoderado5',
        'dni_apoderado5',
        'telefono_apoderado5',
        'parentesco_apoderado5',
        'legalizado_apoderado5',

        
         'apellido_apoderado6',
        'nombre_apoderado6',
        'dni_apoderado6',
        'telefono_apoderado6',
        'parentesco_apoderado6',
        'legalizado_apoderado6',

        
         'apellido_apoderado7',
        'nombre_apoderado7',
        'dni_apoderado7',
        'telefono_apoderado7',
        'parentesco_apoderado7',
        'legalizado_apoderado7',


    ];

    const dataExcel = await excelReader(file,  header , 4);

    const formData = new FormData();

    formData.append('estudiantes', JSON.stringify(dataExcel));

    const validarData = apiClient.post('/configuracion/estudiante/importacion/validar', 
    formData,
    {
          headers: { 'Content-Type': 'multipart/form-data' }
    }

    );


}



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

// Helper para formatear condición del estudiante
function formatCondicionEstudianteChip(value: string): { label: string; color: string } {
    const config: Record<string, { label: string; color: string }> = {
        ESTUDIANTE: { label: 'ESTUDIANTE', color: 'primary' },
        EGRESADO: { label: 'EGRESADO', color: 'success' },
    };
    return config[value] ?? { label: value || 'ESTUDIANTE', color: 'info' };
}


async function handleSubmit() {
    
    if (!fileInput.value?.files?.[0]) {
        notificacion('Por favor, selecciona un archivo Excel.', {
            type: 'warning',
            title: 'Archivo requerido',
        });
        return;
    }

    const file = fileInput.value.files[0];
    const extension = file.name.split('.').pop()?.toLowerCase();
    if (extension !== 'xlsx' && extension !== 'xls') {
        notificacion('El archivo debe ser Excel (.xlsx o .xls)', {
            type: 'warning',
            title: 'Formato inválido',
        });
        return;
    }

    importing.value = true;

    try {
        const formData = new FormData();
        formData.append('fileexportar', file);

        await apiClient.post('/configuracion/estudiante/importar', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        notificacion('Estudiantes importados correctamente.', {
            type: 'success',
            title: 'Importación exitosa',
        });

        emit('imported');
        closeModal();
    } catch (error: any) {
        const message =
            error.response?.data?.message || 'Ocurrió un inconveniente al importar los estudiantes.';
        notificacion(message, { type: 'danger', title: 'Error' });
    } finally {
        importing.value = false;
    }
}

function closeModal() {
    if (importing.value) return;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
    emit('close');
}
</script>

<template>
    <AppModal :open="open" title="Importar estudiantes" size="fullscreen" @close="closeModal">
   
         <template #body>
        <v-container fluid class="pa-0" style="height: 100%;">
        <v-row
            no-gutters
            class="fill-height"
            style="background-color: #121212;"
        >
       
            <!-- IZQUIERDA -->
                <v-col
                cols="12"
                sm="3"
                class="d-flex align-center justify-center"
                style="background-color:#1e1e1e;"
                >
                
                <v-sheet
                    class="d-flex flex-column align-center justify-center"
                    height="200"
                    width="100%"
                    rounded
                    style="border: 2px dashed #4caf50;"
                >
                 <h1>Cargar archivo</h1>
                    <v-file-upload
                    density="compact"
                    accept=".xlsx,.xls"
                      title="Arrasta o suelta el excel"
                    @change="handleFileChange"
                    />

                </v-sheet>
               
                </v-col>

            <!-- DERECHA -->
            <v-col
            cols="12"
            sm="9"
            class="d-flex align-center justify-center"
            style="background-color:#2a2a2a; color:aquamarine;"
            >
            <v-sheet class="pa-4" width="100%" height="100%" color="transparent">
                   <v-card rounded="lg" elevation="1">
                <VDataTableCard
                    :loading="table.loading.value"
                    :column-menu="table.columnMenu.value"
                    :search-value="table.searchQuery.value"
                    search-placeholder="Buscar estudiante..."
                   
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
            </v-sheet>
            </v-col>

        </v-row>
        </v-container>

</template>
        <template #footer>
            <div class="d-flex justify-space-between w-100">
              

                   <v-btn
                        variant="outlined"
                        @click="closeModal"
                        :disabled="importing"
                        class="text-none"
                    >
                        <v-icon start>mdi-close</v-icon>
                        Cancelar
                    </v-btn>

                    <v-btn
                        color="primary"
                        variant="flat"
                        :loading="importing"
                        :disabled="importing"
                        @click="handleSubmit"
                        class="text-none"
                    >
                        <v-icon start>mdi-content-save</v-icon>
                       Importar
                    </v-btn>
            </div>
        </template>
    </AppModal>
</template>

