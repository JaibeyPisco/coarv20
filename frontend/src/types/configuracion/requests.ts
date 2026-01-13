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


