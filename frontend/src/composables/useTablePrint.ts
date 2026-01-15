/**
 * Composable para impresión de tablas
 * Genera HTML formateado para imprimir datos de tabla
 */

import { logger } from '@/utils/logger';

export interface PrintOptions {
    /** Título del documento */
    title?: string;
    /** Subtítulo o descripción */
    subtitle?: string;
    /** Texto del pie de página */
    footer?: string;
    /** Si es true, abre el diálogo de impresión automáticamente */
    autoPrint?: boolean;
}

export interface TableColumn {
    /** Clave del campo */
    key: string;
    /** Título de la columna */
    title: string;
    /** Si es true, la columna será visible en la impresión */
    visible?: boolean;
}

/**
 * Composable para imprimir tablas
 */
export function useTablePrint() {
    /**
     * Genera HTML de la tabla para impresión
     */
    const generateTableHTML = <T extends Record<string, unknown>>(
        data: T[],
        columns: TableColumn[],
        options: PrintOptions = {}
    ): string => {
        const { title = 'Listado', subtitle, footer = 'Generado desde la intranet' } = options;

        // Filtrar columnas visibles
        const visibleColumns = columns.filter(col => col.visible !== false);

        if (data.length === 0) {
            return `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>${title}</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        h4 { margin-bottom: 20px; }
                    </style>
                </head>
                <body>
                    <h4>${title}</h4>
                    ${subtitle ? `<p>${subtitle}</p>` : ''}
                    <p>No hay datos para mostrar.</p>
                    <small>${footer}</small>
                </body>
                </html>
            `;
        }

        // Generar headers
        let headersHTML = '<thead><tr>';
        visibleColumns.forEach(col => {
            headersHTML += `<th style="border: 1px solid #ddd; padding: 8px; text-align: left; background-color: #f2f2f2; font-weight: bold;">${col.title}</th>`;
        });
        headersHTML += '</tr></thead>';

        // Generar filas
        let rowsHTML = '<tbody>';
        data.forEach((item) => {
            rowsHTML += '<tr>';
            visibleColumns.forEach(col => {
                const value = item[col.key];
                const displayValue = value !== null && value !== undefined 
                    ? String(value) 
                    : '—';
                rowsHTML += `<td style="border: 1px solid #ddd; padding: 8px; text-align: left;">${displayValue}</td>`;
            });
            rowsHTML += '</tr>';
        });
        rowsHTML += '</tbody>';

        return `
            <!DOCTYPE html>
            <html>
            <head>
                <title>${title}</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    h4 { margin-bottom: 20px; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; font-weight: bold; }
                    @media print {
                        body { padding: 10px; }
                        @page { margin: 1cm; }
                    }
                </style>
            </head>
            <body>
                <h4>${title}</h4>
                ${subtitle ? `<p>${subtitle}</p>` : ''}
                <table>
                    ${headersHTML}
                    ${rowsHTML}
                </table>
                <small style="margin-top: 20px; display: block;">${footer}</small>
            </body>
            </html>
        `;
    };

    /**
     * Imprime una tabla
     */
    const printTable = <T extends Record<string, unknown>>(
        data: T[],
        columns: TableColumn[],
        options: PrintOptions = {}
    ): void => {
        try {
            const html = generateTableHTML(data, columns, {
                ...options,
                autoPrint: true,
            });

            const printWindow = window.open('', '_blank');
            if (!printWindow) {
                logger.warn('No se pudo abrir la ventana de impresión');
                return;
            }

            printWindow.document.write(html);
            printWindow.document.close();

            // Esperar a que se cargue el contenido antes de imprimir
            printWindow.onload = () => {
                printWindow.print();
            };

            logger.info('Tabla impresa exitosamente', {
                title: options.title,
                rowCount: data.length,
            });
        } catch (error) {
            logger.error('Error printing table', error, {
                title: options.title,
                dataCount: data.length,
            });
            throw error;
        }
    };

    return {
        generateTableHTML,
        printTable,
    };
}
