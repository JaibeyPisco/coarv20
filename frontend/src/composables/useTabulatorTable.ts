import { ref, nextTick, type Ref } from 'vue';
import { TabulatorFull as Tabulator } from 'tabulator-tables';
import apiClient from '@/api/axios';

/**
 * Configuración para el composable useTabulatorTable
 */
export interface TabulatorConfig {
    /** Referencia al elemento HTML donde se renderizará la tabla */
    tableEl: Ref<HTMLElement | null>;
    /** Array de definiciones de columnas para Tabulator */
    columns: any[];
    /** URL del endpoint API para cargar datos via AJAX */
    ajaxURL: string;
    /** HTML personalizado para el encabezado al imprimir (opcional) */
    printHeader?: string;
    /** HTML personalizado para el pie de página al imprimir (opcional) */
    printFooter?: string;
    /** Altura de la tabla en CSS (opcional, por defecto: 'calc(100vh - 360px)') */
    height?: string;
    /** Callback ejecutado cuando los datos se cargan exitosamente */
    onDataLoaded?: (data: any[]) => void;
    /** Callback ejecutado cuando comienza la carga de datos */
    onDataLoading?: () => void;
}

/**
 * Composable para gestionar tablas Tabulator con funcionalidades CRUD
 * 
 * Este composable encapsula toda la lógica de inicialización, configuración
 * y operaciones comunes de tablas Tabulator, incluyendo:
 * - Carga de datos via AJAX
 * - Búsqueda y filtrado
 * - Exportación a Excel
 * - Impresión
 * - Gestión de visibilidad de columnas
 * - Resumen de registros
 * 
 * @example
 * ```vue
 * <script setup>
 * import { useTabulatorTable } from '@/composables/useTabulatorTable';
 * 
 * const tableEl = ref(null);
 * const columns = [/* definiciones de columnas *\/];
 * 
 * const table = useTabulatorTable({
 *   tableEl,
 *   columns,
 *   ajaxURL: '/api/items',
 *   onDataLoaded: (data) => console.log('Datos cargados:', data)
 * });
 * 
 * onMounted(() => {
 *   table.initializeTable();
 * });
 * </script>
 * ```
 * 
 * @template T - Tipo de los datos que manejará la tabla
 * @param config - Configuración de la tabla
 * @returns Objeto con estado reactivo y funciones para operar la tabla
 * 
 * @public
 */
export function useTabulatorTable<T = any>(config: TabulatorConfig) {
    const table = ref<any | null>(null);
    const loading = ref(false);
    const data = ref<T[]>([]);
    const columnMenu = ref<{ title: string; field: string; visible: boolean }[]>([]);
    const recordSummary = ref('Mostrando 0 registros');

    const initializeTable = async () => {
        await nextTick();
        if (!config.tableEl.value) return;

        table.value = new Tabulator(config.tableEl.value, {
            layout: 'fitColumns',
            reactiveData: false,
            placeholder: 'No se encontraron registros',
            columns: config.columns,
            printHeader: config.printHeader || '<h4 class="mb-3">Listado</h4>',
            printFooter: config.printFooter || '<small>Generado desde la intranet</small>',
            height: config.height || 'calc(100vh - 360px)',
            columnDefaults: {
                resizable: true,
            },
            ajaxURL: config.ajaxURL,
            ajaxContentType: 'json',
            ajaxRequestFunc: async (url: string) => {
                const response = await apiClient.get(url);
                return response.data;
            },
            ajaxResponse: (_url: string, _params: any, response: any) => {
                const responseData: T[] = response?.data ?? [];
                data.value = responseData;
                loading.value = false;
                updateRecordSummary();
                
                if (config.onDataLoaded) {
                    config.onDataLoaded(responseData);
                }
                
                return responseData;
            },
        });

        table.value.on('dataLoading', () => {
            loading.value = true;
            if (config.onDataLoading) {
                config.onDataLoading();
            }
        });

        table.value.on('tableBuilt', prepareColumnMenu);
        table.value.on('dataLoaded', updateRecordSummary);
        table.value.on('dataFiltered', updateRecordSummary);
        table.value.on('columnVisibilityChanged', prepareColumnMenu);
    };

    const prepareColumnMenu = () => {
        if (!table.value) return;
        columnMenu.value = table.value.getColumns().map((column: any) => ({
            title: column.getDefinition().title ?? '',
            field: column.getField(),
            visible: column.isVisible(),
        }));
    };

    const reloadTable = async () => {
        if (!table.value) return;
        loading.value = true;
        await table.value.replaceData(config.ajaxURL);
        updateRecordSummary();
    };

    const updateRecordSummary = () => {
        if (!table.value) {
            recordSummary.value = 'Mostrando 0 registros';
            return;
        }

        const filteredRows = table.value.getRows(true).length;
        const totalRows = table.value.getData().length || data.value.length;
        recordSummary.value = `Mostrando ${filteredRows} de ${totalRows} registros`;
    };

    const applySearch = (query: string, searchFields: (keyof T)[]) => {
        const normalized = query.trim().toLowerCase();
        if (!table.value) return;

        if (!normalized) {
            table.value.clearFilter(true);
        } else {
            table.value.setFilter((rowData: T) => {
                const values = searchFields.map(field => rowData[field]);
                return values.some((value) => 
                    value?.toString().toLowerCase().includes(normalized)
                );
            });
        }

        updateRecordSummary();
    };

    const downloadExcel = (filename: string, sheetName: string) => {
        if (!table.value) return;
        // @ts-ignore Assign XLSX global
        (window as any).XLSX = (window as any).XLSX;
        table.value.download('xlsx', filename, { sheetName });
    };

    const printTable = () => {
        table.value?.print(false, true);
    };

    const toggleColumnVisibility = (field: string) => {
        if (!table.value) return;
        const column = table.value.getColumn(field);
        if (!column) return;
        column.toggle();
        prepareColumnMenu();
    };

    const destroy = () => {
        table.value?.destroy();
    };

    return {
        table,
        loading,
        data,
        columnMenu,
        recordSummary,
        initializeTable,
        reloadTable,
        applySearch,
        updateRecordSummary,
        downloadExcel,
        printTable,
        toggleColumnVisibility,
        prepareColumnMenu,
        destroy,
    };
}

