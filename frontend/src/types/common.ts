/**
 * Tipos comunes para respuestas de la API
 */

/**
 * Respuesta estándar de la API
 */
export interface ApiResponse<T = any> {
  data: T;
  message?: string;
  errors?: Record<string, string[]>;
}

/**
 * Respuesta de lista paginada
 */
export interface PaginatedResponse<T> {
  data: T[];
  current_page?: number;
  last_page?: number;
  per_page?: number;
  total?: number;
}

/**
 * Respuesta de error de la API
 */
export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
  status?: number;
}

