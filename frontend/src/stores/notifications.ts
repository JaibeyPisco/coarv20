import { defineStore } from 'pinia';
import { ref } from 'vue';

export interface Notification {
    id: string;
    message: string;
    type: 'success' | 'error' | 'warning' | 'info';
    title?: string;
    duration?: number;
    timeout?: number;
}

export const useNotificationsStore = defineStore('notifications', () => {
    const notifications = ref<Notification[]>([]);

    const addNotification = (notification: Omit<Notification, 'id' | 'timeout'>) => {
        const id = `notification-${Date.now()}-${Math.random().toString(36).slice(2, 9)}`;
        const duration = notification.duration ?? 3000;
        
        const newNotification: Notification = {
            ...notification,
            id,
            timeout: duration,
        };

        notifications.value.push(newNotification);

        // Auto-remover después de la duración
        if (duration > 0) {
            setTimeout(() => {
                removeNotification(id);
            }, duration);
        }

        return id;
    };

    const removeNotification = (id: string) => {
        const index = notifications.value.findIndex(n => n.id === id);
        if (index > -1) {
            notifications.value.splice(index, 1);
        }
    };

    const clearAll = () => {
        notifications.value = [];
    };

    // Helpers para tipos específicos
    const success = (message: string, options?: { title?: string; duration?: number }) => {
        return addNotification({
            message,
            type: 'success',
            ...options,
        });
    };

    const error = (message: string, options?: { title?: string; duration?: number }) => {
        return addNotification({
            message,
            type: 'error',
            duration: options?.duration ?? 5000, // Errores duran más por defecto
            ...options,
        });
    };

    const warning = (message: string, options?: { title?: string; duration?: number }) => {
        return addNotification({
            message,
            type: 'warning',
            ...options,
        });
    };

    const info = (message: string, options?: { title?: string; duration?: number }) => {
        return addNotification({
            message,
            type: 'info',
            ...options,
        });
    };

    return {
        notifications,
        addNotification,
        removeNotification,
        clearAll,
        success,
        error,
        warning,
        info,
    };
});
