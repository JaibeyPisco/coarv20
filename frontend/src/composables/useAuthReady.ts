import { onMounted, ref } from 'vue';
import { useAuthStore } from '@/stores/auth';

/**
 * Composable para asegurar que la autenticación esté lista antes de hacer peticiones
 * 
 * Este composable verifica automáticamente el estado de autenticación cuando
 * el componente se monta, evitando condiciones de carrera donde las peticiones
 * se hacen antes de que el token esté disponible.
 * 
 * @example
 * ```vue
 * <script setup>
 * import { useAuthReady } from '@/composables/useAuthReady';
 * 
 * const { isReady, waitForAuth } = useAuthReady();
 * 
 * // En el template, mostrar loader mientras no esté listo
 * <div v-if="!isReady">Verificando autenticación...</div>
 * <div v-else>Contenido</div>
 * </script>
 * ```
 * 
 * @returns Objeto con:
 * - `isReady`: Ref reactivo que indica si la autenticación está lista
 * - `waitForAuth`: Función para esperar manualmente la autenticación
 * 
 * @public
 */
export function useAuthReady() {
    const authStore = useAuthStore();
    const isReady = ref(false);

    /**
     * Esperar a que la autenticación esté lista
     * 
     * Verifica si hay token disponible y si el usuario está cargado.
     * Si hay token pero no usuario, intenta verificar la autenticación.
     * 
     * @returns Promise que resuelve a `true` si está autenticado, `false` en caso contrario
     */
    async function waitForAuth(): Promise<boolean> {
        // Si no hay token, no está autenticado
        if (!authStore.token && !localStorage.getItem('auth_token')) {
            isReady.value = false;
            return false;
        }

        // Si ya tenemos el usuario, está listo
        if (authStore.user) {
            isReady.value = true;
            return true;
        }

        // Si hay token pero no usuario, verificar autenticación
        if (authStore.token || localStorage.getItem('auth_token')) {
            try {
                const authenticated = await authStore.checkAuth();
                isReady.value = authenticated;
                return authenticated;
            } catch (error) {
                console.error('Error verificando autenticación:', error);
                isReady.value = false;
                return false;
            }
        }

        isReady.value = false;
        return false;
    }

    // Esperar automáticamente cuando el componente se monte
    onMounted(async () => {
        await waitForAuth();
    });

    return {
        isReady,
        waitForAuth,
    };
}

