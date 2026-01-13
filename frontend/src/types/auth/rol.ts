export interface Permiso {
    menu: string;
    view: boolean;
    new: boolean;
    edit: boolean;
    delete: boolean;
}

export interface Rol {
    id: number;
    nombre: string;
    fl_no_dashboard: boolean;
    estado?: number;
    permisos?: Permiso[]; // Opcional porque solo se carga al editar
    created_at?: string | null;
    updated_at?: string | null;
}

export interface ModuloPermiso {
    seccion: string;
    menus: {
        menu: string;
        nombre: string;
        tieneDelete?: boolean;
        tieneNew?: boolean;
        tieneEdit?: boolean;
    }[];
}


