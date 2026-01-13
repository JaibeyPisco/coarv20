import { describe, it, expect, vi, beforeEach } from 'vitest';
import { reactive } from 'vue';
import { useCrudModal } from '@/composables/useCrudModal';
import apiClient from '@/api/axios';

// Mock de apiClient
vi.mock('@/api/axios', () => ({
    default: {
        post: vi.fn(),
        put: vi.fn(),
        delete: vi.fn(),
    },
}));

// Mock de notificacion
vi.mock('@/utils/notificacion', () => ({
    notificacion: vi.fn(),
}));

describe('useCrudModal', () => {
    const mockForm = reactive({ nombre: '', descripcion: '' });

    const mockConfig = {
        entityName: 'área',
        getPayload: (form: any) => ({
            nombre: form.nombre.trim(),
            descripcion: form.descripcion.trim() || null,
        }),
        validateForm: (form: any) => {
            if (!form.nombre.trim()) return 'El nombre es obligatorio.';
            return null;
        },
        resetForm: () => {
            mockForm.nombre = '';
            mockForm.descripcion = '';
        },
        onCreate: async (data: any) => {
            const response = await apiClient.post('/areas', data);
            return response.data;
        },
        onUpdate: async (id: number, data: any) => {
            const response = await apiClient.put(`/areas/${id}`, data);
            return response.data;
        },
        onDeleteCustom: async (id: number) => {
            await apiClient.delete(`/areas/${id}`);
        },
    };

    beforeEach(() => {
        vi.clearAllMocks();
        mockForm.nombre = '';
        mockForm.descripcion = '';
    });

    it('debería inicializar con modales cerrados', () => {
        const crudModal = useCrudModal(mockConfig);

        expect(crudModal.showSaveModal.value).toBe(false);
        expect(crudModal.showDeleteModal.value).toBe(false);
        expect(crudModal.editingId.value).toBeNull();
    });

    it('debería abrir modal de creación', () => {
        const crudModal = useCrudModal(mockConfig);

        crudModal.openCreateModal();

        expect(crudModal.showSaveModal.value).toBe(true);
        expect(crudModal.editingId.value).toBeNull();
    });

    it('debería abrir modal de edición con datos', () => {
        const crudModal = useCrudModal({
            ...mockConfig,
            onEdit: vi.fn(),
        });

        const item = { id: 1, nombre: 'Test', descripcion: 'Descripción' };
        crudModal.openEditModal(item);

        expect(crudModal.showSaveModal.value).toBe(true);
        expect(crudModal.editingId.value).toBe(1);
    });

    it('debería validar formulario antes de guardar', async () => {
        const crudModal = useCrudModal(mockConfig);

        mockForm.nombre = '';
        await crudModal.handleSaveSubmit();

        expect(apiClient.post).not.toHaveBeenCalled();
        expect(apiClient.put).not.toHaveBeenCalled();
    });

    it('debería crear nueva entidad cuando no hay editingId', async () => {
        const mockResponse = { data: { id: 1, nombre: 'Nuevo', descripcion: 'Desc' } };
        (apiClient.post as any).mockResolvedValue(mockResponse);

        const crudModal = useCrudModal(mockConfig);
        mockForm.nombre = 'Nuevo';
        mockForm.descripcion = 'Descripción';

        await crudModal.handleSaveSubmit();

        expect(apiClient.post).toHaveBeenCalledWith('/areas', {
            nombre: 'Nuevo',
            descripcion: 'Descripción',
        });
    });

    it('debería actualizar entidad cuando hay editingId', async () => {
        const mockResponse = { data: { id: 1, nombre: 'Actualizado', descripcion: 'Desc' } };
        (apiClient.put as any).mockResolvedValue(mockResponse);

        const crudModal = useCrudModal(mockConfig);
        crudModal.editingId.value = 1;
        mockForm.nombre = 'Actualizado';
        mockForm.descripcion = 'Descripción';

        await crudModal.handleSaveSubmit();

        expect(apiClient.put).toHaveBeenCalledWith('/areas/1', {
            nombre: 'Actualizado',
            descripcion: 'Descripción',
        });
    });
});















