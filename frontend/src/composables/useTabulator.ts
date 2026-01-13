import { ref } from 'vue';
import * as XLSX from 'xlsx';
import type { Tabulator } from 'tabulator-tables';

/**
 * Composable para funciones comunes de Tabulator
 */
export function useTabulatorHelpers() {
    const columnMenu = ref<{ title: string; field: string; visible: boolean }[]>([]);

    /**
     * Descarga la tabla en formato Excel
     */
    function downloadExcel(table: Tabulator | null, filename: string, sheetName: string = 'Datos') {
        if (!table) return;
        // @ts-ignore Assign XLSX global
        (window as any).XLSX = XLSX;
        table.download('xlsx', filename, { sheetName });
    }

    /**
     * Imprime la tabla
     */
    function printTable(table: Tabulator | null) {
        if (!table) return;
        // @ts-ignore - Tabulator print method signature
        table.print(false, true);
    }

    /**
     * Alterna la visibilidad de una columna
     */
    function toggleColumnVisibility(table: Tabulator | null, field: string) {
        if (!table) return;
        const column = table.getColumn(field);
        if (!column) return;

        column.toggle();
        columnMenu.value = columnMenu.value.map((item) =>
            item.field === field ? { ...item, visible: column.isVisible() } : item,
        );
    }

    /**
     * Prepara el menú de columnas
     */
    function prepareColumnMenu(table: Tabulator | null) {
        if (!table) return;
        columnMenu.value = table.getColumns().map((column) => ({
            title: column.getDefinition().title ?? '',
            field: column.getField(),
            visible: column.isVisible(),
        }));
    }

    /**
     * Obtiene la configuración base común de Tabulator
     */
    function getBaseTabulatorConfig() {
        return {
            layout: 'fitColumns' as const,
            reactiveData: false,
            placeholder: 'No se encontraron registros',
            height: 'calc(100vh - 360px)',
            columnDefaults: {
                resizable: true,
            },
            ajaxContentType: 'json' as const,
        };
    }

    return {
        columnMenu,
        downloadExcel,
        printTable,
        toggleColumnVisibility,
        prepareColumnMenu,
        getBaseTabulatorConfig,
    };
}


