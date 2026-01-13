<template>
    <AuthenticatedLayout>
           
        <div class="card mb-2">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Fecha inicio</label>
                        <input type="date" class="form-control" name="fecha_inicio" v-model="fechaInicio">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Fecha fin</label>
                        <input type="date" class="form-control" name="fecha_fin" v-model="fechaFin">
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" @click="buscar">
                            Buscar
                        </button>
                    </div>
                </div>
            </div>
        </div>

            <div>
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
    </AuthenticatedLayout>
</template>

<script setup lang="ts">


import TableCard from '@/components/Table/TableCard.vue';
import AuthenticatedLayout from '@/components/Layouts/AuthenticatedLayout.vue'; 
import { nextTick, onMounted, ref } from 'vue';
import type { MovimientoInformacion } from '@/types/reportes/MovimientoInformacion';
 import { TabulatorFull as Tabulator } from 'tabulator-tables';
import apiClient from '@/api/axios';
import * as XLSX from 'xlsx';
import { CurrentDate, formatTimeAndDate } from '@/utils/HelperDates';

const tableEl = ref<HTMLElement|null> (null);
const table = ref<any | null>(null);
const movimientoInformacion = ref<MovimientoInformacion[]>([]);
const loading = ref(false);
const searchQuery = ref('');
const columnMenu = ref<{ title: string; field: string; visible: boolean }[]>([]);
const recordSummary = ref('Mostrando 0 registros');
const fechaFin = ref(CurrentDate());
const fechaInicio = ref(CurrentDate());

const buscar = () =>{
    table.value?.setData();
}


const columns = [
 
    {
        title: 'FECHA',
        field: 'fecha',
       
        headerSort: true,
        formatter:  (cell: any) =>{
            console.log(cell);
            
            return formatTimeAndDate(cell.getValue());
        },
    },
    {
        title: 'USUARIO',
        field: 'usuario',
    },
       {
        title: 'MÓDULO',
        field: 'modulo',
    },
      {
        title: 'MENÚ',
        field: 'menu',
    },
    {
        title: 'ACCION',
        field: 'accion',
      
        headerHozAlign: 'center',
        hozAlign: 'center',
        formatter: (cell: any) => {
            const accion =  cell.getValue();
 
                 let html = '';

                    if (accion == 'NUEVO') {
                        html = `<span class="badge bg-blue text-blue-fg">${accion}</span>`;
                    }
                    else if (accion == 'EDITAR') {
                        html = `<span class="badge bg-yellow text-yellow-fg">${accion}</span>`;
                    }
                    else if (accion == 'ELIMINAR') {
                        html = `<span class="badge bg-yellow text-yellow-fg">${accion}</span>`;
                    }
                    else if (accion == 'ANULAR') {
                        html = `<span class="badge bg-red text-red-fg">${accion}</span>`;
                    }
                    else {
                        html = `<span class="badge bg-default text-default-fg">${accion}</span>`;
                    }
 
            return html;
        },
    },

        {
        title: 'DESCRIPCIÓN',
        field: 'descripcion',
          formatter:  (cell: any) =>{
            console.log(cell);
            
            return `<div>${cell.getValue()}</div>`;
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
        printHeader: '<h4 class="mb-3">Listado de movimiento Informacion</h4>',
        printFooter: '<small>Generado desde la intranet</small>',
        height: 'calc(100vh - 360px)',
        columnDefaults: {
            resizable: true,
        },
        ajaxURL: 'reportes/movimiento_informacion',
        ajaxContentType: 'json',

        ajaxParams: () => ({
            fecha_inicio: fechaInicio.value,
            fecha_fin: fechaFin.value,
        }),
        ajaxRequestFunc: async (url: string, config: any, params: any) => {
            const response = await apiClient.get(url, {params});
            
            return response.data;
        },
        ajaxResponse: (_url: string, _params: any, response: any) => {
            const data: MovimientoInformacion[] = response?.data ?? [];
            movimientoInformacion.value = data;
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


function updateRecordSummary() {
    if (!table.value) {
        recordSummary.value = 'Mostrando 0 registros';
        return;
    }

    const filteredRows = table.value.getRows(true).length;
    const totalRows = table.value.getData().length || movimientoInformacion.value.length;
    recordSummary.value = `Mostrando ${filteredRows} de ${totalRows} registros`;
}
function prepareColumnMenu() {
    if (!table.value) return;
    columnMenu.value = table.value.getColumns().map((column: any) => ({
        title: column.getDefinition().title ?? '',
        field: column.getField(),
        visible: column.isVisible(),
    }));
}

onMounted(async () => {
    // @ts-ignore expose for Tabulator download module
    (window as any).XLSX = XLSX;
    await initializeTable();
    // No llamar reloadTable() aquí - la tabla ya carga datos automáticamente con ajaxURL
  //  document.addEventListener('click', handleGlobalClick);
});

</script>
