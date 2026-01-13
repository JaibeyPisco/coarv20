export interface Usuario {
    id: number;
    nombre: string;
    apellido: string;
    usuario: string;
    email: string;
    tipo_persona: 'STANDARD' | 'DOCENTE' | 'ESTUDIANTE' | string;
    id_rol?: number | null;
    rol?: string;
    id_personal?: number | null;
    personal_nombre?: string;
    id_estudiante?: number | null;
    estudiante_nombre?: string;
    imagen?: string | null;
    imagen_url?: string | null;
    fl_ver_informacion_privada: boolean;
    fl_suspendido: boolean;
    created_at?: string | null;
    updated_at?: string | null;
}


