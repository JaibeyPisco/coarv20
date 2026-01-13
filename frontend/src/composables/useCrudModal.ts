import { ref, computed } from 'vue';
import { notificacion } from '@/utils/notificacion';

/**
 * Configuración para el composable useCrudModal
 * 
 * @template T - Tipo de la entidad que manejará el modal (debe tener propiedad `id`)
 */
export interface CrudModalConfig<T> {
    /** Nombre de la entidad para mensajes de usuario (ej: "área", "usuario") */
    entityName: string;
    /** Función para transformar el formulario en payload para la API */
    getPayload: (form: any) => any;
    /** Función de validación del formulario. Retorna mensaje de error o null si es válido */
    validateForm?: (form: any) => string | null;
    /** Callback ejecutado después de guardar exitosamente */
    onSuccess?: () => void;
    /** Callback ejecutado al abrir modal de edición (para poblar formulario con datos) */
    onEdit?: (item: T) => void | Promise<void>;
    /** Función personalizada para eliminar (opcional, sobrescribe la lógica por defecto) */
    onDelete?: (item: T) => Promise<void>;
    /** Función para crear nueva entidad (debe retornar Promise con la entidad creada) */
    onCreate: (data: any) => Promise<T>;
    /** Función para actualizar entidad existente (debe retornar Promise con la entidad actualizada) */
    onUpdate: (id: number, data: any) => Promise<T>;
    /** Función personalizada para eliminar (opcional, alternativa a onDelete) */
    onDeleteCustom?: (id: number) => Promise<void>;
    /** Función para resetear el formulario a valores iniciales */
    resetForm: () => void;
}

/**
 * Composable para gestionar modales CRUD (Crear, Leer, Actualizar, Eliminar)
 * 
 * Este composable encapsula toda la lógica común de modales CRUD, incluyendo:
 * - Gestión de estado de modales (crear/editar/eliminar)
 * - Validación de formularios
 * - Llamadas a API para operaciones CRUD
 * - Manejo de errores y notificaciones
 * - Callbacks personalizables
 * 
 * @example
 * ```vue
 * <script setup>
 * import { useCrudModal } from '@/composables/useCrudModal';
 * import apiClient from '@/api/axios';
 * 
 * const saveForm = reactive({ nombre: '', descripcion: '' });
 * 
 * const crudModal = useCrudModal({
 *   entityName: 'área',
 *   getPayload: (form) => ({ nombre: form.nombre, descripcion: form.descripcion }),
 *   validateForm: (form) => !form.nombre.trim() ? 'El nombre es obligatorio' : null,
 *   onCreate: (data) => apiClient.post('/api/areas', data).then(r => r.data.data),
 *   onUpdate: (id, data) => apiClient.put(`/api/areas/${id}`, data).then(r => r.data.data),
 *   onDeleteCustom: (id) => apiClient.delete(`/api/areas/${id}`),
 *   resetForm: () => { saveForm.nombre = ''; saveForm.descripcion = ''; },
 *   onEdit: (area) => { saveForm.nombre = area.nombre; saveForm.descripcion = area.descripcion; }
 * });
 * </script>
 * ```
 * 
 * @template T - Tipo de la entidad que manejará el modal (debe tener propiedad `id: number`)
 * @param config - Configuración del modal CRUD
 * @returns Objeto con estado reactivo y funciones para operar el modal
 * 
 * @public
 */
export function useCrudModal<T extends { id: number }>(config: CrudModalConfig<T>) {
    const showSaveModal = ref(false);
    const showDeleteModal = ref(false);
    const editingId = ref<number | null>(null);
    const deleteTarget = ref<T | null>(null);
    const saving = ref(false);
    const deleting = ref(false);

    const saveModalTitle = computed(() => 
        editingId.value ? `Editar ${config.entityName}` : `Nuevo ${config.entityName}`
    );

    const openCreateModal = () => {
        editingId.value = null;
        config.resetForm();
        showSaveModal.value = true;
    };

    const openEditModal = async (item: T) => {
        editingId.value = item.id;
        
        if (config.onEdit) {
            await config.onEdit(item);
        }
        
        showSaveModal.value = true;
    };

    const openDeleteModal = (item: T) => {
        deleteTarget.value = item;
        showDeleteModal.value = true;
    };

    const closeSaveModal = () => {
        showSaveModal.value = false;
        editingId.value = null;
    };

    const closeDeleteModal = () => {
        showDeleteModal.value = false;
        deleteTarget.value = null;
    };

    /**
     * Maneja el submit del formulario de guardar/editar
     */
    const handleSaveSubmit = async (form: any, reloadTable: () => Promise<void>) => {
        // Validación
        if (config.validateForm) {
            const error = config.validateForm(form);
            if (error) {
                notificacion(error, { type: 'danger', title: 'Validación' });
                return;
            }
        }

        const payload = config.getPayload(form);
        saving.value = true;

        try {
            if (editingId.value) {
                await config.onUpdate(editingId.value, payload);
                notificacion(`${config.entityName} actualizado correctamente.`, { type: 'success' });
            } else {
                await config.onCreate(payload);
                notificacion(`${config.entityName} registrado correctamente.`, { type: 'success' });
            }

            closeSaveModal();
            
            if (config.onSuccess) {
                config.onSuccess();
            }
            
            await reloadTable();
        } catch (error: any) {
            if (error.response?.data?.errors) {
                const errors = error.response.data.errors;
                const firstError = Object.values(errors)[0] as string[];
                const message = firstError?.[0] || 'Error de validación';
                notificacion(message, { type: 'danger', title: 'Validación' });
            } else {
                const message = error.response?.data?.message || 
                    `Ocurrió un inconveniente al guardar el registro.`;
                notificacion(message, { type: 'danger', title: 'Error' });
            }
        } finally {
            saving.value = false;
        }
    };

    /**
     * Maneja la confirmación de eliminación
     */
    const handleDeleteConfirm = async (reloadTable: () => Promise<void>) => {
        if (!deleteTarget.value) return;

        deleting.value = true;

        try {
            if (config.onDelete) {
                await config.onDelete(deleteTarget.value);
            } else if (config.onDeleteCustom) {
                await config.onDeleteCustom(deleteTarget.value.id);
            } else {
                throw new Error('No se ha configurado la función de eliminación');
            }
            
            notificacion(`${config.entityName} eliminado correctamente.`, { type: 'success' });
            closeDeleteModal();
            await reloadTable();
        } catch (error: any) {
            const message = error.response?.data?.message || 
                'No fue posible eliminar el registro.';
            notificacion(message, { type: 'danger', title: 'Error' });
        } finally {
            deleting.value = false;
        }
    };

    return {
        showSaveModal,
        showDeleteModal,
        editingId,
        deleteTarget,
        saving,
        deleting,
        saveModalTitle,
        openCreateModal,
        openEditModal,
        openDeleteModal,
        closeSaveModal,
        closeDeleteModal,
        handleSaveSubmit,
        handleDeleteConfirm,
    };
}

