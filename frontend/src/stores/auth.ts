import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { authService } from '../api/services';
import type { User } from '../types/auth';
import type { LoginDto } from '../types/auth';

export const useAuthStore = defineStore('auth', () => {

    const user = ref<User | null>(null);

    const token = ref<string | null>(localStorage.getItem('auth_token'));

    const loading = ref(false);

    const checkingAuth = ref(false);

    const isAuthenticated = computed(() => !!user.value && !!token.value);

    /**
     * Iniciar sesión
     */
    async function login(email: string, password: string, remember: boolean = false): Promise<void> {
        loading.value = true;
        try {
            const credentials: LoginDto = { email, password, remember };
            const response = await authService.login(credentials);

            // Guardar el token recibido
            if (response.token) {
                token.value = response.token;
                localStorage.setItem('auth_token', response.token);
            }

            // Guardar el usuario
            if (response.user) {
                user.value = response.user;
            } else {
                // Si no viene el usuario, obtenerlo (solo si no lo tenemos ya)
                if (!user.value) {
                    await fetchUser();
                }
            }
        } catch (error: any) {
            const errorMessage = error.response?.data?.message
                || error.response?.data?.errors?.email?.[0]
                || error.response?.data?.error
                || error.message
                || 'Error al iniciar sesión';
            throw new Error(errorMessage);
        } finally {
            loading.value = false;
        }
    }

    /**
     * Obtener usuario autenticado
     */
    async function fetchUser(): Promise<void> {
        try {
            const userData = await authService.getCurrentUser();
            user.value = userData;
        } catch (error) {
            user.value = null;
            token.value = null;
            localStorage.removeItem('auth_token');
        }
    }

    /**
     * Cerrar sesión
     */
    async function logout(skipServerCall: boolean = false): Promise<void> {
        try {
            // Solo intentar logout en el servidor si hay token y no se solicita saltar
            if (!skipServerCall && token.value) {
                await authService.logout();
            }
        } catch (error) {
            // Si falla el logout en el servidor, continuar limpiando localmente
            console.error('Error al cerrar sesión:', error);
        } finally {
            // Siempre limpiar el estado local
            user.value = null;
            token.value = null;
            localStorage.removeItem('auth_token');
        }
    }

    /**
     * Verificar autenticación
     */
    async function checkAuth(): Promise<boolean> {
        // Si no hay token, no está autenticado
        if (!token.value) {
            return false;
        }

        // Si ya tenemos el usuario, está autenticado
        if (user.value) {
            return true;
        }

        // Si ya se está verificando, esperar a que termine
        if (checkingAuth.value) {
            // Esperar un poco y verificar si ya tenemos el usuario
            await new Promise(resolve => setTimeout(resolve, 100));
            if (user.value) {
                return true;
            }
            return false;
        }

        checkingAuth.value = true;
        try {
            // checkAuth ahora retorna el usuario directamente, evitando llamada duplicada
            const userData = await authService.checkAuth();
            if (userData) {
                user.value = userData;
                return true;
            }
            return false;
        } catch (error) {
            user.value = null;
            token.value = null;
            localStorage.removeItem('auth_token');
            return false;
        } finally {
            checkingAuth.value = false;
        }
    }

    return {
        user,
        token,
        loading,
        isAuthenticated,
        login,
        logout,
        fetchUser,
        checkAuth,
    };
});

