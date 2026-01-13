export interface TipoPersonal {
    id: number;
    nombre: string;
    descripcion: string | null;
    estado: number;
    id_empresa?: number;
    created_at?: string | null;
    updated_at?: string | null;
}


