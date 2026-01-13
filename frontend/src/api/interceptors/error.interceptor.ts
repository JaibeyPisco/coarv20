import type { AxiosError, InternalAxiosRequestConfig } from 'axios';
import { notificacion } from '../../utils/notificacion';
import { logger } from '../../utils/logger';

/**
 * Interceptor para manejar errores y mostrar notificaciones
 */
export function setupErrorInterceptor(apiClient: any) {
    apiClient.interceptors.response.use(
        (response: any) => response,
        async (error: AxiosError) => {
            const originalRequest = error.config as InternalAxiosRequestConfig & { _retry?: boolean };

            // Manejar errores 401 (autenticación)
            if (error.response?.status === 401 && !originalRequest?._retry) {
                originalRequest._retry = true;
                
                logger.error('Error de autenticación 401', error, {
                    url: originalRequest?.url,
                    method: originalRequest?.method,
                });

                const token = localStorage.getItem('auth_token');
                if (token) {
                    // Intentar verificar el token una vez más antes de hacer logout
                    await new Promise(resolve => setTimeout(resolve, 300));

                    try {
                        const { useAuthStore } = await import('../../stores/auth');
                        const authStore = useAuthStore();
                        const isValid = await authStore.checkAuth();

                        if (isValid && originalRequest) {
                            return apiClient(originalRequest);
                        }
                    } catch {
                        // Si falla, continuar con el logout
                    }
                }

                // Limpiar autenticación
                localStorage.removeItem('auth_token');
                const { useAuthStore } = await import('../../stores/auth');
                const authStore = useAuthStore();
                await authStore.logout(true);

                // Redirigir a login
                const router = await import('../../router').then(m => m.default);
                if (router.currentRoute.value.name !== 'login') {
                    router.push({ name: 'login', query: { redirect: router.currentRoute.value.fullPath } });
                }

                return Promise.reject(error);
            }

            // Mostrar notificación de error (excepto si se solicita omitir)
            if (!originalRequest?.skipErrorNotification) {
                if (error.response) {
                    const errorMessage = (error.response.data as any)?.message || 'Ha ocurrido un error. Por favor, intenta nuevamente.';
                    const status = error.response.status;
                    const type = status >= 500 ? 'danger' : 'warning';
                    
                    logger.api(
                        originalRequest?.method?.toUpperCase() || 'UNKNOWN',
                        originalRequest?.url || 'unknown',
                        status,
                        undefined,
                        { message: errorMessage }
                    );
                    
                    notificacion(errorMessage, { type, title: 'Error' });
                } else {
                    // Error de red
                    logger.error('Error de conexión de red', error, {
                        url: originalRequest?.url,
                        method: originalRequest?.method,
                    });
                    
                    notificacion(
                        'Error de conexión. Por favor, verifica tu conexión a internet e intenta nuevamente.',
                        { type: 'warning', title: 'Error de conexión' }
                    );
                }
            }

            return Promise.reject(error);
        }
    );
}

