import { useNotificationsStore } from '@/stores/notifications';

export interface NotificationOptions {
    type?: 'success' | 'danger' | 'warning' | 'info';
    title?: string;
    duration?: number;
}

/**
 * Sistema de notificaciones usando Vuetify v-snackbar
 * Mantiene compatibilidad con la API anterior
 */
export function notificacion(message: string, options: NotificationOptions = {}) {
    const { type = 'info', title, duration = 3000 } = options;
    
    // Mapear 'danger' a 'error' para Vuetify
    const vuetifyType = type === 'danger' ? 'error' : type;
    
    const notificationsStore = useNotificationsStore();
    
    // Usar los métodos del store según el tipo
    switch (vuetifyType) {
        case 'success':
            notificationsStore.success(message, { title, duration });
            break;
        case 'error':
            notificationsStore.error(message, { title, duration });
            break;
        case 'warning':
            notificationsStore.warning(message, { title, duration });
            break;
        default:
            notificationsStore.info(message, { title, duration });
    }
}
