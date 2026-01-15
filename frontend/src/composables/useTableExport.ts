/**
 * Composable para exportación de tablas a Excel
 * Encapsula la lógica de exportación usando XLSX
 */

import * as XLSX from 'xlsx';
import { logger } from '@/utils/logger';

export interface ExportOptions {
    /** Nombre del archivo (sin extensión) */
    filename: string;
    /** Nombre de la hoja */
    sheetName?: string;
}

/**
 * Composable para exportar datos de tabla a Excel
 */
export function useTableExport() {
    /**
     * Exporta datos a Excel
     */
    const exportToExcel = <T extends Record<string, unknown>>(
        data: T[],
        options: ExportOptions
    ): void => {
        try {
            const { filename, sheetName = 'Datos' } = options;

            if (!data || data.length === 0) {
                logger.warn('No hay datos para exportar', { filename });
                return;
            }

            // Crear workbook y worksheet
            const ws = XLSX.utils.json_to_sheet(data);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, sheetName);

            // Generar nombre de archivo con extensión
            const fullFilename = filename.endsWith('.xlsx') ? filename : `${filename}.xlsx`;

            // Escribir archivo
            XLSX.writeFile(wb, fullFilename);

            logger.info('Datos exportados exitosamente', {
                filename: fullFilename,
                rowCount: data.length,
            });
        } catch (error) {
            logger.error('Error exporting to Excel', error, {
                filename: options.filename,
                dataCount: data.length,
            });
            throw error;
        }
    };

    /**
     * Exporta datos a CSV
     */
    const exportToCSV = <T extends Record<string, unknown>>(
        data: T[],
        options: ExportOptions
    ): void => {
        try {
            const { filename } = options;

            if (!data || data.length === 0) {
                logger.warn('No hay datos para exportar', { filename });
                return;
            }

            // Crear worksheet
            const ws = XLSX.utils.json_to_sheet(data);

            // Convertir a CSV
            const csv = XLSX.utils.sheet_to_csv(ws);

            // Crear blob y descargar
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);

            const fullFilename = filename.endsWith('.csv') ? filename : `${filename}.csv`;

            link.setAttribute('href', url);
            link.setAttribute('download', fullFilename);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            logger.info('Datos exportados a CSV exitosamente', {
                filename: fullFilename,
                rowCount: data.length,
            });
        } catch (error) {
            logger.error('Error exporting to CSV', error, {
                filename: options.filename,
                dataCount: data.length,
            });
            throw error;
        }
    };

    return {
        exportToExcel,
        exportToCSV,
    };
}
