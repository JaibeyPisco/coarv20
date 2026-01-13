import axios from 'axios';
import { setupAuthInterceptor } from './interceptors/auth.interceptor';
import { setupRetryInterceptor } from './interceptors/retry.interceptor';
import { setupErrorInterceptor } from './interceptors/error.interceptor';

// Extender el tipo de configuración de axios para incluir opciones personalizadas
declare module 'axios' {
    export interface AxiosRequestConfig {
        skipErrorNotification?: boolean; // Opción para omitir la notificación automática de errores
    }
}

// En desarrollo, usar rutas relativas para que Vite proxy funcione
// En producción, usar la URL completa del .env
const isDevelopment = import.meta.env.DEV;
const API_BASE_URL = isDevelopment ? '/api' : import.meta.env.VITE_API_BASE_URL;

if (!isDevelopment && !API_BASE_URL) {
    throw new Error('VITE_API_BASE_URL no está configurada en el archivo .env');
}

// Crear instancia de axios
const apiClient = axios.create({
    baseURL: API_BASE_URL,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// Configurar interceptores
// Request: auth interceptor (agrega token)
setupAuthInterceptor(apiClient);

// Response: orden inverso de registro (el último registrado se ejecuta primero)
// 1. Error interceptor (se ejecuta primero - muestra notificaciones)
// 2. Retry interceptor (se ejecuta después - reintenta errores de red)
setupErrorInterceptor(apiClient);
setupRetryInterceptor(apiClient);

export default apiClient;

