/**
 * Servicio de logging estructurado para el frontend
 * Proporciona diferentes niveles de log con contexto y formato consistente
 */

export enum LogLevel {
    DEBUG = 'debug',
    INFO = 'info',
    WARN = 'warn',
    ERROR = 'error',
}

interface LogContext {
    [key: string]: any;
}

class Logger {
    private isDevelopment = import.meta.env.DEV;

    /**
     * Log de debug - solo en desarrollo
     */
    debug(message: string, context?: LogContext): void {
        if (this.isDevelopment) {
            console.debug(`[DEBUG] ${message}`, context || '');
        }
    }

    /**
     * Log de información
     */
    info(message: string, context?: LogContext): void {
        console.info(`[INFO] ${message}`, context || '');
    }

    /**
     * Log de advertencia
     */
    warn(message: string, context?: LogContext): void {
        console.warn(`[WARN] ${message}`, context || '');
    }

    /**
     * Log de error con stack trace
     */
    error(message: string, error?: Error | unknown, context?: LogContext): void {
        const errorContext: LogContext = {
            ...context,
            timestamp: new Date().toISOString(),
        };

        if (error instanceof Error) {
            errorContext.error = {
                message: error.message,
                stack: error.stack,
                name: error.name,
            };
        } else if (error) {
            errorContext.error = error;
        }

        console.error(`[ERROR] ${message}`, errorContext);

        // En producción, aquí podrías enviar el error a un servicio de monitoreo
        // Ej: Sentry, LogRocket, etc.
        if (!this.isDevelopment && error) {
            // this.sendToMonitoringService(message, errorContext);
        }
    }

    /**
     * Log de operaciones de API
     */
    api(method: string, url: string, status?: number, duration?: number, context?: LogContext): void {
        const apiContext: LogContext = {
            method,
            url,
            status,
            duration: duration ? `${duration}ms` : undefined,
            ...context,
        };

        if (status && status >= 400) {
            this.warn(`API ${method} ${url} - Status: ${status}`, apiContext);
        } else {
            this.debug(`API ${method} ${url}`, apiContext);
        }
    }

    /**
     * Log de acciones del usuario
     */
    userAction(action: string, context?: LogContext): void {
        this.info(`User action: ${action}`, {
            ...context,
            timestamp: new Date().toISOString(),
        });
    }
}

export const logger = new Logger();















