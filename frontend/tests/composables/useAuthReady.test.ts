import { describe, it, expect, vi, beforeEach } from 'vitest';
import { useAuthReady } from '@/composables/useAuthReady';
import { useAuthStore } from '@/stores/auth';

// Mock del store de autenticación
vi.mock('@/stores/auth', () => ({
    useAuthStore: vi.fn(),
}));

describe('useAuthReady', () => {
    const mockAuthStore = {
        token: 'test-token',
        user: null,
        checkAuth: vi.fn(),
    };

    beforeEach(() => {
        vi.clearAllMocks();
        (useAuthStore as any).mockReturnValue(mockAuthStore);
        mockAuthStore.token = 'test-token';
        mockAuthStore.user = null;
    });

    it('debería retornar false si no hay token', async () => {
        mockAuthStore.token = null;
        localStorage.removeItem('auth_token');

        const { waitForAuth } = useAuthReady();
        const result = await waitForAuth();

        expect(result).toBe(false);
    });

    it('debería retornar true si ya hay usuario', async () => {
        mockAuthStore.user = { id: 1, nombre: 'Test' };

        const { waitForAuth } = useAuthReady();
        const result = await waitForAuth();

        expect(result).toBe(true);
        expect(mockAuthStore.checkAuth).not.toHaveBeenCalled();
    });

    it('debería llamar checkAuth si hay token pero no usuario', async () => {
        mockAuthStore.checkAuth.mockResolvedValue(true);
        mockAuthStore.user = null;

        const { waitForAuth } = useAuthReady();
        const result = await waitForAuth();

        expect(mockAuthStore.checkAuth).toHaveBeenCalled();
        expect(result).toBe(true);
    });

    it('debería retornar false si checkAuth falla', async () => {
        mockAuthStore.checkAuth.mockResolvedValue(false);

        const { waitForAuth } = useAuthReady();
        const result = await waitForAuth();

        expect(result).toBe(false);
    });
});















