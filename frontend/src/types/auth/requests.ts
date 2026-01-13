/**
 * DTOs para autenticación
 */

/**
 * DTO para login
 */
export interface LoginDto {
  email: string;
  password: string;
  remember?: boolean;
}

