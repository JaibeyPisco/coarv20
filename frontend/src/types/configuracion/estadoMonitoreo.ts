export interface EstadoMonitoreo {
    id: number;
    nombre: string;
    tipo: 'INCIDENCIA' | 'DERIVACION' | string;
    color_bg: string;
    color_text: string;
    id_empresa?: number;
    created_at?: string | null;
    updated_at?: string | null;
}


