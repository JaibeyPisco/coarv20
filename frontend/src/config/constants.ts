/**
 * Constantes globales de la aplicación
 */
export const APP_CONFIG = {
  NAME: 'COAR',
  VERSION: '1.0.0',
} as const;

/**
 * Configuración de paginación por defecto
 */
export const PAGINATION = {
  DEFAULT_PAGE_SIZE: 50,
  PAGE_SIZE_OPTIONS: [10, 25, 50, 100],
} as const;

/**
 * Configuración de notificaciones
 */
export const NOTIFICATION = {
  DEFAULT_DURATION: 3000,
  SUCCESS_DURATION: 3000,
  ERROR_DURATION: 5000,
} as const;

