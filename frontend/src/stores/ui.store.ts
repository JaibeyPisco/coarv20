import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

/**
 * Store de UI
 * Maneja el estado de la interfaz de usuario (sidebar, modales, tema, etc.)
 */
export const useUIStore = defineStore('ui', () => {
    // Estado del sidebar
    const sidebarOpen = ref(true);
    const sidebarCollapsed = ref(false);

    // Estado de modales
    const activeModals = ref<Set<string>>(new Set());

    // Estado de carga global
    const globalLoading = ref(false);

    /**
     * Toggle del sidebar
     */
    const toggleSidebar = () => {
        sidebarOpen.value = !sidebarOpen.value;
    };

    /**
     * Cerrar sidebar
     */
    const closeSidebar = () => {
        sidebarOpen.value = false;
    };

    /**
     * Abrir sidebar
     */
    const openSidebar = () => {
        sidebarOpen.value = true;
    };

    /**
     * Toggle de colapso del sidebar
     */
    const toggleSidebarCollapse = () => {
        sidebarCollapsed.value = !sidebarCollapsed.value;
    };

    /**
     * Abrir modal
     */
    const openModal = (modalId: string) => {
        activeModals.value.add(modalId);
    };

    /**
     * Cerrar modal
     */
    const closeModal = (modalId: string) => {
        activeModals.value.delete(modalId);
    };

    /**
     * Verificar si un modal está abierto
     */
    const isModalOpen = (modalId: string) => {
        return activeModals.value.has(modalId);
    };

    /**
     * Cerrar todos los modales
     */
    const closeAllModals = () => {
        activeModals.value.clear();
    };

    /**
     * Activar carga global
     */
    const setGlobalLoading = (loading: boolean) => {
        globalLoading.value = loading;
    };

    return {
        // State
        sidebarOpen,
        sidebarCollapsed,
        activeModals,
        globalLoading,
        // Computed
        // Actions
        toggleSidebar,
        closeSidebar,
        openSidebar,
        toggleSidebarCollapse,
        openModal,
        closeModal,
        isModalOpen,
        closeAllModals,
        setGlobalLoading,
    };
});


















