export interface Empresa {
    id: number;
    numero_documento?: string | null;
    razon_social?: string | null;
    nombre_comercial?: string | null;
    direccion?: string | null;
    telefono?: string | null;
    email?: string | null;
    logo_url?: string | null;
}

import type { Permiso } from './rol';

export interface User {
    id: number;
    nombre: string;
    apellido: string;
    email: string;
    usuario: string;
    imagen?: string | null;
    id_empresa: number;
    id_rol?: number | null;
    tipo?: string | null; // SUPER ADMINISTRADOR, SUPER USUARIO, etc.
    name?: string; // Atributo calculado del modelo
    initials?: string; // Atributo calculado del modelo
    avatar_url?: string | null; // Atributo calculado del modelo
    empresa?: Empresa | null;
    permisos?: Permiso[]; // Permisos del usuario basados en su rol
    created_at?: string | null;
    updated_at?: string | null;
}


