import type { AxiosError, InternalAxiosRequestConfig } from 'axios';

/**
 * Interceptor para manejar autenticación y tokens
 */
export function setupAuthInterceptor(apiClient: any) {
    // Interceptor de request: agregar token a las peticiones
    apiClient.interceptors.request.use(
        (config: InternalAxiosRequestConfig) => {
            const token = localStorage.getItem('auth_token');
            if (token) {
                config.headers['Authorization'] = `Bearer ${token}`;
            }
            return config;
        },
        (error: AxiosError) => Promise.reject(error)
    );
}

