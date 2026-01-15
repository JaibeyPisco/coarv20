/**
 * Composable para gestión de columnas de tabla
 * Maneja la visibilidad y configuración de columnas
 */

import { ref, computed } from 'vue';

export interface TableColumn {
    /** Clave única de la columna */
    key: string;
    /** Título de la columna */
    title: string;
    /** Si es true, la columna es ordenable */
    sortable?: boolean;
    /** Alineación del contenido */
    align?: 'start' | 'center' | 'end';
    /** Ancho de la columna */
    width?: string;
    /** Si es true, la columna es visible por defecto */
    visible?: boolean;
}

export interface ColumnMenu {
    title: string;
    key: string;
    visible: boolean;
}

/**
 * Composable para gestionar columnas de tabla
 */
export function useTableColumns(initialColumns: TableColumn[] = []) {
    const columns = ref<TableColumn[]>(initialColumns);
    const columnMenu = ref<ColumnMenu[]>([]);

    /**
     * Actualiza las columnas y genera el menú
     */
    const updateColumns = (newColumns: TableColumn[]) => {
        columns.value = newColumns;
        updateColumnMenu();
    };

    /**
     * Actualiza el menú de columnas basado en las columnas actuales
     */
    const updateColumnMenu = () => {
        columnMenu.value = columns.value.map(col => ({
            title: col.title,
            key: col.key,
            visible: col.visible !== false, // Por defecto visible
        }));
    };

    /**
     * Alterna la visibilidad de una columna
     */
    const toggleColumnVisibility = (key: string) => {
        const column = columnMenu.value.find(col => col.key === key);
        if (column) {
            column.visible = !column.visible;
        }
        
        // Sincronizar con columns
        const col = columns.value.find(c => c.key === key);
        if (col) {
            col.visible = column?.visible ?? true;
        }
    };

    /**
     * Obtiene las columnas visibles para usar en v-data-table-server
     */
    const visibleColumns = computed(() => {
        return columns.value.filter(col => {
            const menuItem = columnMenu.value.find(m => m.key === col.key);
            return menuItem?.visible !== false && col.visible !== false;
        });
    });

    /**
     * Obtiene los headers para v-data-table-server (solo visibles)
     */
    const headers = computed(() => {
        return visibleColumns.value.map(col => ({
            title: col.title,
            key: col.key,
            sortable: col.sortable ?? true,
            align: col.align ?? 'start',
            width: col.width,
        }));
    });

    /**
     * Resetea todas las columnas a visibles
     */
    const resetVisibility = () => {
        columnMenu.value.forEach(col => {
            col.visible = true;
        });
        columns.value.forEach(col => {
            col.visible = true;
        });
    };

    /**
     * Obtiene el resumen de columnas visibles
     */
    const visibleCount = computed(() => {
        return columnMenu.value.filter(col => col.visible).length;
    });

    // Inicializar menú al crear
    updateColumnMenu();

    return {
        columns,
        columnMenu,
        visibleColumns,
        headers,
        visibleCount,
        updateColumns,
        updateColumnMenu,
        toggleColumnVisibility,
        resetVisibility,
    };
}
