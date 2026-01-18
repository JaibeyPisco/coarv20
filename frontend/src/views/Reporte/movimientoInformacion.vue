<script setup lang="ts">
import AuthenticatedLayout from '@/components/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';
// import * as XLSX from 'xlsx';
import type { MovimientoInformacion } from '@/types/reportes/MovimientoInformacion';
import VDataTableCard from '@/components/Table/VDataTableCard.vue';
import { useVuetifyTable } from '@/composables/useVuetifyTable';
import { CurrentDate, formatTimeAndDate } from '@/utils/HelperDates';
import { formatAccionChip } from '@/utils/vuetifyTableHelpers';

const fechaFin = ref(CurrentDate());
const fechaInicio = ref(CurrentDate());

// Headers de la tabla
const headers = [
    {
        title: 'FECHA',
        key: 'fecha',
        sortable: true,
    },
    {
        title: 'USUARIO',
        key: 'usuario',
        sortable: true,
    },
    {
        title: 'MÓDULO',
        key: 'modulo',
        sortable: true,
    },
    {
        title: 'MENÚ',
        key: 'menu',
        sortable: true,
    },
    {
        title: 'ACCIÓN',
        key: 'accion',
        sortable: true,
        align: 'center' as const,
        width: '150px',
    },
    {
        title: 'DESCRIPCIÓN',
        key: 'descripcion',
        sortable: false,
    },
];

// Composable de tabla
const table = useVuetifyTable<MovimientoInformacion>({
    apiURL: '/reportes/movimiento_informacion',
    apiParams: () => ({
        fecha_inicio: fechaInicio.value,
        fecha_fin: fechaFin.value,
    }),
    searchFields: ['usuario', 'modulo', 'menu', 'descripcion'],
    serverSidePagination: false,
    serverSideSorting: false,
    serverSideSearch: false,
});

// Inicializar menú de columnas
table.updateColumnMenu(headers);

// Funciones
const buscar = () => {
    table.reloadTable();
};

const updateSearchValue = (value: string) => {
    table.searchQuery.value = value;
    table.applySearch(value);
};

const downloadExcel = () => {
    table.downloadExcel('movimiento-informacion.xlsx', 'Movimiento de Información');
};

const toggleColumnVisibility = (key: string) => {
    table.toggleColumnVisibility(key);
};

// Lifecycle
// onMounted(async () => {
//     (window as any).XLSX = XLSX;
//     await table.loadItems({
//         page: 1,
//         itemsPerPage: 10,
//     });
// });
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <v-container fluid class="py-0">
                <v-row align="center">
                    <v-col>
                        <h1 class="text-h5 mb-1">Reportes / Movimiento de Información</h1>
                        <p class="text-subtitle-1 text-medium-emphasis mb-0">
                            Consulta el historial de movimientos de información en el sistema
                        </p>
                    </v-col>
                </v-row>
            </v-container>
        </template>

        <v-row>
            <v-col cols="12">
                <v-card class="mb-4" rounded="lg" elevation="1">
                    <v-card-text>
                        <v-row align="end" class="g-2">
                            <v-col cols="12" md="3">
                                <v-text-field
                                    v-model="fechaInicio"
                                    label="Fecha inicio"
                                    type="date"
                                    density="compact"
                                    variant="outlined"
                                    hide-details
                                />
                            </v-col>

                            <v-col cols="12" md="3">
                                <v-text-field
                                    v-model="fechaFin"
                                    label="Fecha fin"
                                    type="date"
                                    density="compact"
                                    variant="outlined"
                                    hide-details
                                />
                            </v-col>

                            <v-col cols="12" md="auto">
                                <v-btn
                                    color="primary"
                                    block
                                    @click="buscar"
                                    prepend-icon="mdi-magnify"
                                >
                                    Buscar
                                </v-btn>
                            </v-col>
                        </v-row>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <v-row>
            <v-col cols="12">
                <v-card rounded="lg" elevation="1">
                    <VDataTableCard
                        :loading="table.loading.value"
                        :column-menu="table.columnMenu.value"
                        :search-value="table.searchQuery.value"
                        search-placeholder="Buscar movimiento..."
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
                            <template #item.fecha="{ value }">
                                <span class="text-body-2">{{ formatTimeAndDate(value) }}</span>
                            </template>

                            <template #item.accion="{ value }">
                                <v-chip
                                    :color="formatAccionChip(value).color"
                                    size="small"
                                    variant="flat"
                                    class="text-uppercase font-weight-medium"
                                >
                                    {{ formatAccionChip(value).label }}
                                </v-chip>
                            </template>

                            <template #item.descripcion="{ value }">
                                <div class="text-body-2" style="max-width: 400px; word-wrap: break-word; overflow-wrap: break-word; white-space: normal; line-height: 1.4;">
                                    {{ value || '—' }}
                                </div>
                            </template>
                        </v-data-table-server>

                        <template #footer-left>
                            <span class="text-body-2 text-medium-emphasis">{{ table.recordSummary.value }}</span>
                        </template>
                        <template #footer-right>
                            <span class="text-body-2 text-medium-emphasis">Actualizado automáticamente al guardar cambios.</span>
                        </template>
                    </VDataTableCard>
                </v-card>
            </v-col>
        </v-row>
    </AuthenticatedLayout>
</template>
