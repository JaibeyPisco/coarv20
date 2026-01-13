export interface Estudiante {
    id: number;
    dni: string;
    nombres: string;
    apellidos: string;
    estudiante?: string; // nombre completo concatenado
    grado?: number | null;
    seccion?: string | null;
    grado_seccion?: string; // concatenado
    correo_electronico?: string | null;
    condicion_estudiante?: 'ESTUDIANTE' | 'EGRESADO' | string;
    foto?: string | null;
    foto_url?: string | null;
    sexo?: 'MASCULINO' | 'FEMENINO' | string;
    fecha_nacimiento?: string | null;
    codigo_estudiante?: string | null;
    obsv?: string | null;
    lugar_nacimiento?: string | null;
    fecha_caducidad_dni?: string | null;
    num_telefonico?: string | null;
    religion?: string | null;
    region_domicilio_actual?: string | null;
    provincia_domicilio_actual?: string | null;
    distrito_domicilio_actual?: string | null;
    direccion_domicilio_actual?: string | null;
    referencia_domicilio_actual?: string | null;
    // Residencia
    lav?: string | null;
    llaves?: string | null;
    pabellon?: string | null;
    ala?: string | null;
    banos?: string | null;
    monitor_acompana?: string | null;
    cama_ropero?: string | null;
    duchas?: string | null;
    urinarios?: string | null;
    estado?: number;
    created_at?: string | null;
    updated_at?: string | null;
}


