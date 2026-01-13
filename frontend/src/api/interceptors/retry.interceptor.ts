import type { AxiosError, InternalAxiosRequestConfig } from 'axios';

/**
 * Interceptor para reintentar peticiones fallidas por errores de red
 */
export function setupRetryInterceptor(apiClient: any) {
    apiClient.interceptors.response.use(
        (response: any) => response,
        async (error: AxiosError) => {
            const originalRequest = error.config as InternalAxiosRequestConfig & { _retry?: boolean };

            // Reintentar solo errores de red (sin respuesta del servidor)
            if (!error.response && originalRequest && !originalRequest._retry) {
                originalRequest._retry = true;

                // Esperar antes de reintentar
                await new Promise(resolve => setTimeout(resolve, 500));

                // Verificar que el token esté disponible
                const token = localStorage.getItem('auth_token');
                if (token) {
                    return apiClient(originalRequest);
                }
            }

            return Promise.reject(error);
        }
    );
}

