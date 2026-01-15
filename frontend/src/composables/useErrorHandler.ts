/**
 * Composable para manejo centralizado de errores
 * Proporciona funciones consistentes para manejar errores de API y errores generales
 */

import { logger } from '@/utils/logger';
import { notificacion } from '@/utils/notificacion';
import type { AxiosError } from 'axios';

export interface ErrorContext {
    [key: string]: unknown;
}

export interface ErrorHandlerOptions {
    /** Si es true, muestra notificación al usuario */
    showNotification?: boolean;
    /** Mensaje personalizado para la notificación */
    customMessage?: string;
    /** Título personalizado para la notificación */
    customTitle?: string;
    /** Si es true, registra el error en el logger */
    logError?: boolean;
    /** Contexto adicional para logging */
    context?: ErrorContext;
}

/**
 * Extrae el mensaje de error de una respuesta de Axios
 */
function extractAxiosErrorMessage(error: AxiosError): string {
    // Intentar obtener mensaje de validación Laravel
    if (error.response?.data?.errors) {
        const errors = error.response.data.errors;
        const firstError = Object.values(errors)[0];
        if (Array.isArray(firstError) && firstError.length > 0) {
            return firstError[0] as string;
        }
    }

    // Mensaje directo del backend
    if (error.response?.data?.message) {
        return error.response.data.message as string;
    }

    // Mensaje de error estándar
    if (error.message) {
        return error.message;
    }

    return 'Ocurrió un error inesperado';
}

/**
 * Composable para manejo centralizado de errores
 */
export function useErrorHandler() {
    /**
     * Maneja errores de API (Axios)
     */
    const handleApiError = (
        error: unknown,
        options: ErrorHandlerOptions = {}
    ): string => {
        const {
            showNotification = true,
            customMessage,
            customTitle = 'Error',
            logError = true,
            context = {},
        } = options;

        const axiosError = error as AxiosError;
        const message = customMessage || extractAxiosErrorMessage(axiosError);

        // Log del error
        if (logError) {
            logger.error(
                customMessage || 'Error en petición API',
                error,
                {
                    ...context,
                    status: axiosError.response?.status,
                    url: axiosError.config?.url,
                    method: axiosError.config?.method,
                }
            );
        }

        // Mostrar notificación al usuario
        if (showNotification) {
            notificacion(message, {
                type: 'danger',
                title: customTitle,
            });
        }

        return message;
    };

    /**
     * Maneja errores generales (no API)
     */
    const handleError = (
        error: unknown,
        options: ErrorHandlerOptions = {}
    ): string => {
        const {
            showNotification = true,
            customMessage,
            customTitle = 'Error',
            logError = true,
            context = {},
        } = options;

        let message = customMessage || 'Ocurrió un error inesperado';

        if (error instanceof Error) {
            message = error.message;
        } else if (typeof error === 'string') {
            message = error;
        }

        // Log del error
        if (logError) {
            logger.error(customMessage || 'Error general', error, context);
        }

        // Mostrar notificación al usuario
        if (showNotification) {
            notificacion(message, {
                type: 'danger',
                title: customTitle,
            });
        }

        return message;
    };

    /**
     * Maneja errores de validación específicamente
     */
    const handleValidationError = (
        error: unknown,
        options: Omit<ErrorHandlerOptions, 'customTitle'> = {}
    ): string => {
        return handleApiError(error, {
            ...options,
            customTitle: 'Error de Validación',
        });
    };

    /**
     * Maneja errores de red específicamente
     */
    const handleNetworkError = (
        error: unknown,
        options: ErrorHandlerOptions = {}
    ): string => {
        return handleError(error, {
            ...options,
            customMessage: 'Error de conexión. Verifica tu conexión a internet.',
            customTitle: 'Error de Red',
        });
    };

    return {
        handleApiError,
        handleError,
        handleValidationError,
        handleNetworkError,
    };
}
