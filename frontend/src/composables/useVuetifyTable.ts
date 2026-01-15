import { ref, computed } from 'vue';
import apiClient from '@/api/axios';
import { logger } from '@/utils/logger';
import { useTableExport } from './useTableExport';
import { useTablePrint } from './useTablePrint';

/**
 * Opciones de tabla para v-data-table-server
 */
export interface TableOptions {
    page: number;
    itemsPerPage: number;
    sortBy?: Array<{ key: string; order: 'asc' | 'desc' }>;
    groupBy?: string[];
    search?: string;
}

/**
 * Configuración para el composable useVuetifyTable
 */
export interface VuetifyTableConfig<T = any> {
    /** URL del endpoint API para cargar datos */
    apiURL: string;
    /** Parámetros adicionales para la petición API (opcional) */
    apiParams?: () => Record<string, any>;
    /** Campos en los que se puede buscar (para búsqueda en cliente si no hay server-side) */
    searchFields?: (keyof T)[];
    /** Callback ejecutado cuando los datos se cargan exitosamente */
    onDataLoaded?: (data: T[]) => void;
    /** Callback ejecutado cuando comienza la carga de datos */
    onDataLoading?: () => void;
    /** Si el backend soporta paginación server-side */
    serverSidePagination?: boolean;
    /** Si el backend soporta ordenamiento server-side */
    serverSideSorting?: boolean;
    /** Si el backend soporta búsqueda server-side */
    serverSideSearch?: boolean;
}

/**
 * Composable para gestionar tablas v-data-table-server con funcionalidades CRUD
 * 
 * Este composable encapsula toda la lógica de inicialización, configuración
 * y operaciones comunes de tablas v-data-table-server, incluyendo:
 * - Carga de datos via API (server-side o client-side)
 * - Búsqueda y filtrado
 * - Exportación a Excel
 * - Impresión
 * - Gestión de visibilidad de columnas
 * - Resumen de registros
 * 
 * @example
 * ```vue
 * <script setup>
 * import { useVuetifyTable } from '@/composables/useVuetifyTable';
 * 
 * const table = useVuetifyTable({
 *   apiURL: '/api/items',
 *   searchFields: ['nombre', 'descripcion'],
 *   onDataLoaded: (data) => console.log('Datos cargados:', data)
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
export function useVuetifyTable<T = any>(config: VuetifyTableConfig<T>) {
    const { exportToExcel } = useTableExport();
    const { printTable: printTableUtil } = useTablePrint();
    
    const items = ref<T[]>([]);
    const loading = ref(false);
    const totalItems = ref(0);
    const page = ref(1);
    const itemsPerPage = ref(10);
    const sortBy = ref<Array<{ key: string; order: 'asc' | 'desc' }>>([]);
    const searchQuery = ref('');
    const columnMenu = ref<{ title: string; key: string; visible: boolean }[]>([]);
    
    const {
        apiURL,
        apiParams,
        searchFields = [],
        onDataLoaded,
        onDataLoading,
        serverSidePagination = false,
        serverSideSorting = false,
        serverSideSearch = false,
    } = config;

    /**
     * Carga los datos de la tabla
     */
    const loadItems = async (options: TableOptions) => {
        loading.value = true;
        if (onDataLoading) {
            onDataLoading();
        }

        try {
            const params: Record<string, any> = apiParams ? apiParams() : {};
            
            if (serverSidePagination) {
                params.page = options.page;
                params.per_page = options.itemsPerPage;
            }
            
            if (serverSideSorting && options.sortBy && options.sortBy.length > 0 && options.sortBy[0]) {
                params.sort_by = options.sortBy[0].key;
                params.sort_desc = options.sortBy[0].order === 'desc';
            }
            
            if (serverSideSearch && options.search) {
                params.search = options.search;
            }

            const response = await apiClient.get(apiURL, { params });
            
            let responseData: T[] = [];
            let total = 0;

            if (serverSidePagination) {
                // Backend devuelve: { data: [...], total: 100, per_page: 10, current_page: 1 }
                responseData = response.data?.data ?? response.data ?? [];
                total = response.data?.total ?? responseData.length;
            } else {
                // Backend devuelve: { data: [...] } o directamente [...]
                // El backend Laravel típicamente devuelve { data: [...] }
                if (Array.isArray(response.data)) {
                    responseData = response.data;
                } else if (response.data?.data && Array.isArray(response.data.data)) {
                    responseData = response.data.data;
                } else {
                    responseData = [];
                }
                total = responseData.length;
            }

            // Aplicar búsqueda en cliente si no es server-side
            if (!serverSideSearch && searchQuery.value && searchFields.length > 0) {
                const normalized = searchQuery.value.trim().toLowerCase();
                if (normalized) {
                    responseData = responseData.filter((item: T) => {
                        const values = searchFields.map(field => item[field]);
                        return values.some((value) => 
                            value?.toString().toLowerCase().includes(normalized)
                        );
                    });
                }
            }

            items.value = responseData;
            totalItems.value = total;

            if (onDataLoaded) {
                onDataLoaded(responseData);
            }
        } catch (error) {
            logger.error('Error loading table data', error, {
                apiURL,
                params: apiParams ? apiParams() : {},
            });
            items.value = [];
            totalItems.value = 0;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Recarga los datos de la tabla
     */
    const reloadTable = async () => {
        await loadItems({
            page: page.value,
            itemsPerPage: itemsPerPage.value,
            sortBy: sortBy.value,
            search: searchQuery.value,
        });
    };

    /**
     * Aplica búsqueda
     */
    const applySearch = (query: string) => {
        searchQuery.value = query;
        if (serverSideSearch) {
            // Recargar desde servidor
            page.value = 1; // Resetear a primera página
            reloadTable();
        } else {
            // Búsqueda en cliente ya se aplica en loadItems
            reloadTable();
        }
    };

    /**
     * Exporta los datos a Excel
     */
    const downloadExcel = (filename: string, sheetName: string) => {
        const dataToExport = items.value as Record<string, unknown>[];
        exportToExcel(dataToExport, {
            filename,
            sheetName,
        });
    };

    /**
     * Imprime la tabla
     */
    const printTable = (options?: { title?: string; subtitle?: string; footer?: string }) => {
        const columns = columnMenu.value.map(col => ({
            key: col.key,
            title: col.title,
            visible: col.visible,
        }));

        printTableUtil(items.value as Record<string, unknown>[], columns, {
            title: options?.title || 'Listado',
            subtitle: options?.subtitle,
            footer: options?.footer || 'Generado desde la intranet',
        });
    };

    /**
     * Actualiza el menú de columnas
     * @deprecated Use useTableColumns composable instead
     */
    const updateColumnMenu = (headers: Array<{ title: string; key: string }>) => {
        columnMenu.value = headers.map(header => ({
            title: header.title,
            key: header.key,
            visible: true, // Por defecto todas visibles
        }));
    };

    /**
     * Alterna la visibilidad de una columna
     * @deprecated Use useTableColumns composable instead
     */
    const toggleColumnVisibility = (key: string) => {
        const column = columnMenu.value.find(col => col.key === key);
        if (column) {
            column.visible = !column.visible;
        }
    };

    /**
     * Resumen de registros
     */
    const recordSummary = computed(() => {
        const start = serverSidePagination 
            ? ((page.value - 1) * itemsPerPage.value) + 1
            : 1;
        const end = serverSidePagination
            ? Math.min(page.value * itemsPerPage.value, totalItems.value)
            : items.value.length;
        const total = serverSidePagination ? totalItems.value : items.value.length;
        
        return `Mostrando ${start}-${end} de ${total} registros`;
    });

    return {
        // Estado
        items,
        loading,
        totalItems,
        page,
        itemsPerPage,
        sortBy,
        searchQuery,
        columnMenu,
        recordSummary,
        
        // Métodos
        loadItems,
        reloadTable,
        applySearch,
        downloadExcel,
        printTable,
        updateColumnMenu,
        toggleColumnVisibility,
    };
}
