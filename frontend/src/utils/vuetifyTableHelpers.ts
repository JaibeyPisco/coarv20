/**
 * Utilidades para tablas v-data-table-server
 */

export interface ActionColumnConfig {
    width?: string;
    hasEdit?: boolean;
    hasDelete?: boolean;
    editIcon?: string;
    deleteIcon?: string;
    customActions?: Array<{
        action: string;
        label: string;
        icon?: string;
        color?: string;
    }> | ((rowData: any) => Array<{
        action: string;
        label: string;
        icon?: string;
        color?: string;
    }>);
}

/**
 * Crea la definición de columna de acciones para v-data-table-server
 * Retorna un objeto con la configuración de la columna y un slot template
 */
export function createActionColumnHeader(
    config: ActionColumnConfig = {}
): { title: string; key: string; sortable: boolean; width?: string } {
    const { width = '150px' } = config;

    return {
        title: 'ACCIONES',
        key: 'actions',
        sortable: false,
        width,
    };
}

/**
 * Helper para crear acciones en el slot de acciones
 * Retorna funciones para manejar las acciones
 */
export function createActionHandlers<T = any>(actions: {
    edit?: (data: T) => void;
    delete?: (data: T) => void;
    [key: string]: ((data: T) => void) | undefined;
}) {
    return {
        handleEdit: (item: T) => {
            if (actions.edit) {
                actions.edit(item);
            }
        },
        handleDelete: (item: T) => {
            if (actions.delete) {
                actions.delete(item);
            }
        },
        handleCustomAction: (action: string, item: T) => {
            const handler = actions[action];
            if (handler) {
                handler(item);
            }
        },
    };
}

/**
 * Helper para formatear estado como chip
 */
export function formatStatusChip(value: number | string): { label: string; color: string } {
    const numValue = Number(value);
    const statusConfig: Record<number, { label: string; color: string }> = {
        1: { label: 'ACTIVO', color: 'success' },
        0: { label: 'INACTIVO', color: 'error' },
    };

    return statusConfig[numValue] ?? { label: 'SIN ESTADO', color: 'info' };
}

/**
 * Helper para formatear texto largo con ellipsis
 */
export function formatLongText(text: string | null | undefined, maxLength: number = 100): string {
    if (!text) return '—';
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
}

/**
 * Helper para formatear nivel de incidencia como chip
 */
export function formatNivelIncidenciaChip(value: string): { label: string; color: string } {
    const config: Record<string, { label: string; color: string }> = {
        NEGATIVO: { label: 'NEGATIVO', color: 'error' },
        POSITIVA: { label: 'POSITIVA', color: 'success' },
        NEUTRA: { label: 'NEUTRA', color: 'info' },
    };
    return config[value] ?? { label: value || '—', color: 'info' };
}

/**
 * Helper para formatear nivel de severidad como chip
 */
export function formatNivelSeveridadChip(value: string): { label: string; color: string } {
    const config: Record<string, { label: string; color: string }> = {
        BAJA: { label: 'BAJA', color: 'primary' },
        MEDIA: { label: 'MEDIA', color: 'warning' },
        ALTA: { label: 'ALTA', color: 'error' },
    };
    return config[value] ?? { label: value || '—', color: 'info' };
}

/**
 * Helper para formatear derivación inmediata como chip
 */
export function formatDerivacionInmediataChip(value: string): { label: string; color: string } {
    const config: Record<string, { label: string; color: string }> = {
        SI: { label: 'SÍ', color: 'warning' },
        NO: { label: 'NO', color: 'info' },
    };
    return config[value] ?? { label: value || '—', color: 'info' };
}

/**
 * Helper para formatear tipo de contratación como chip
 */
export function formatTipoContratacionChip(value: string): { label: string; color: string } {
    const config: Record<string, { label: string; color: string }> = {
        DIRECTA: { label: 'DIRECTA', color: 'primary' },
        TERCERO: { label: 'TERCERO', color: 'info' },
    };
    return config[value] ?? { label: value || '—', color: 'grey' };
}

/**
 * Helper para formatear tipo de estado de monitoreo como chip
 */
export function formatTipoEstadoMonitoreoChip(value: string): { label: string; color: string } {
    const config: Record<string, { label: string; color: string }> = {
        INCIDENCIA: { label: 'INCIDENCIA', color: 'primary' },
        DERIVACION: { label: 'DERIVACIÓN', color: 'warning' },
    };
    return config[value] ?? { label: value || '—', color: 'info' };
}

/**
 * Calcula el color del texto basado en el color de fondo (para contraste)
 */
export function calculateTextColor(hexColor: string): string {
    const hex = hexColor.replace('#', '');
    const r = parseInt(hex.substring(0, 2), 16);
    const g = parseInt(hex.substring(2, 4), 16);
    const b = parseInt(hex.substring(4, 6), 16);
    const brightness = (r * 299 + g * 587 + b * 114) / 1000;
    return brightness < 128 ? '#ffffff' : '#000000';
}

/**
 * Helper para formatear acción de movimiento de información como chip
 */
export function formatAccionChip(value: string): { label: string; color: string } {
    const config: Record<string, { label: string; color: string }> = {
        NUEVO: { label: 'NUEVO', color: 'primary' },
        EDITAR: { label: 'EDITAR', color: 'warning' },
        ELIMINAR: { label: 'ELIMINAR', color: 'warning' },
        ANULAR: { label: 'ANULAR', color: 'error' },
    };
    return config[value] ?? { label: value || '—', color: 'info' };
}
