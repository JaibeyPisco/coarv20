/**
 * DTOs para configuración
 */

/**
 * DTO para crear un área
 */
export interface CreateAreaDto {
  nombre: string;
  descripcion?: string | null;
}

/**
 * DTO para actualizar un área
 */
export interface UpdateAreaDto {
  nombre?: string;
  descripcion?: string | null;
}

/**
 * DTO para cambiar contraseña
 */
export interface ChangePasswordDto {
  password: string;
}

/**
 * DTO para crear un estado de monitoreo
 */
export interface CreateEstadoMonitoreoDto {
  nombre: string;
  tipo: 'INCIDENCIA' | 'DERIVACION' | string;
  color_bg: string;
}

/**
 * DTO para actualizar un estado de monitoreo
 */
export interface UpdateEstadoMonitoreoDto {
  id: number;
  nombre?: string;
  tipo?: 'INCIDENCIA' | 'DERIVACION' | string;
  color_bg?: string;
}

/**
 * DTO para crear un tipo de incidencia
 */
export interface CreateTiposIncidenciaDto {
  nombre: string;
  nivel_incidencia: 'NEGATIVO' | 'POSITIVA' | 'NEUTRA' | string;
  nivel_severidad: 'BAJA' | 'MEDIA' | 'ALTA' | string;
  derivacion_inmediata: 'SI' | 'NO' | string;
}

/**
 * DTO para actualizar un tipo de incidencia
 */
export interface UpdateTiposIncidenciaDto {
  id: number;
  nombre?: string;
  nivel_incidencia?: 'NEGATIVO' | 'POSITIVA' | 'NEUTRA' | string;
  nivel_severidad?: 'BAJA' | 'MEDIA' | 'ALTA' | string;
  derivacion_inmediata?: 'SI' | 'NO' | string;
}

/**
 * DTO para crear un tipo de personal
 */
export interface CreateTipoPersonalDto {
  nombre: string;
  descripcion?: string | null;
}

/**
 * DTO para actualizar un tipo de personal
 */
export interface UpdateTipoPersonalDto {
  id: number;
  nombre?: string;
  descripcion?: string | null;
}


