/**
 * Tipos para respuestas de autenticación
 */

import type { User } from './user';

/**
 * Respuesta de login
 */
export interface LoginResponse {
  token: string;
  user?: any;
}

/**
 * Respuesta de usuario autenticado
 */
export interface UserResponse extends User {
  // Hereda todos los campos de User, incluyendo tipo y permisos
}


