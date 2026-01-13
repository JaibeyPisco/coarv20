export interface Personal {
    id: number;
    numero_documento: number | string;
    id_tipo_personal: number;
    nombre_tipo_personal?: string;
    id_tipo_documento: number;
    nombre_documento?: string;
    nombre: string;
    apellido: string;
    tipo_contratacion: 'DIRECTA' | 'TERCERO' | string;
    direccion?: string | null;
    ubigeo?: number | string | null;
    ubigeo_text?: string | null;
    comentario?: string | null;
    id_proveedor?: number | null;
    proveedor?: string | null;
    imagen?: string | null;
    firma?: string | null;
    estado: number;
    id_empresa?: number;
    created_at?: string | null;
    updated_at?: string | null;
}


