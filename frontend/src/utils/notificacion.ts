// Sistema de notificaciones simple usando alertas del navegador
// Puedes reemplazarlo con una librería como vue-toastification o similar

export interface NotificationOptions {
    type?: 'success' | 'danger' | 'warning' | 'info';
    title?: string;
    duration?: number;
}

export function notificacion(message: string, options: NotificationOptions = {}) {
    const { type = 'info', title, duration = 3000 } = options;

    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'danger' ? 'danger' : type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'info'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.setAttribute('role', 'alert');

    if (title) {
        const titleEl = document.createElement('strong');
        titleEl.textContent = title;
        notification.appendChild(titleEl);
        notification.appendChild(document.createTextNode(' '));
    }

    notification.appendChild(document.createTextNode(message));

    const closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'btn-close';
    closeBtn.setAttribute('data-bs-dismiss', 'alert');
    closeBtn.setAttribute('aria-label', 'Close');
    notification.appendChild(closeBtn);

    document.body.appendChild(notification);

    // Auto-remover después de la duración
    setTimeout(() => {
        if (notification.parentNode) {
            notification.classList.remove('show');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 150);
        }
    }, duration);

    // Remover al hacer click
    closeBtn.addEventListener('click', () => {
        notification.classList.remove('show');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 150);
    });
}

// Exponer globalmente para compatibilidad
if (typeof window !== 'undefined') {
    (window as any).notificacion = notificacion;
}


