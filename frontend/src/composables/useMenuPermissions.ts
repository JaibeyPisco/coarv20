import { computed, type ComputedRef } from 'vue';
import type { User } from '@/types/auth';
import type { Permiso } from '@/types/auth';

/**
 * Interfaz para items del menú
 */
interface MenuItem {
    label: string;
    href?: string;
    icon?: string;
    active?: boolean;
    children?: MenuItem[];
    /** Nombre del menú para validación de permisos (ej: "configuracion-area") */
    menu?: string;
}

/**
 * Composable para manejar permisos del menú basado en roles y permisos del usuario
 * 
 * Este composable proporciona funciones para verificar permisos y filtrar
 * items del menú según los permisos del usuario. Soporta:
 * - Usuarios especiales (SUPER ADMINISTRADOR, SUPER USUARIO) con acceso total
 * - Validación de permisos por menú
 * - Filtrado recursivo de items con children
 * - Items sin permisos requeridos (como Dashboard, Perfil)
 * 
 * @example
 * ```vue
 * <script setup>
 * import { useMenuPermissions } from '@/composables/useMenuPermissions';
 * import { useAuthStore } from '@/stores/auth';
 * 
 * const authStore = useAuthStore();
 * const user = computed(() => authStore.user);
 * const { filtrarItemsMenu, tienePermisoView } = useMenuPermissions(user);
 * 
 * const menuItems = [
 *   { label: 'Dashboard', href: '/dashboard' },
 *   { label: 'Áreas', href: '/areas', menu: 'configuracion-area' }
 * ];
 * 
 * const filteredMenu = computed(() => filtrarItemsMenu(menuItems));
 * </script>
 * ```
 * 
 * @param user - Computed ref del usuario autenticado
 * @returns Objeto con funciones para verificar y filtrar permisos
 * 
 * @public
 */
export function useMenuPermissions(user: ComputedRef<User | null>) {
    /**
     * Verifica si el usuario tiene permiso de view para un menú específico
     * 
     * @param menu - Nombre del menú a verificar (ej: "configuracion-area")
     * @returns `true` si el usuario tiene permiso, `false` en caso contrario
     */
    const tienePermisoView = (menu: string): boolean => {
        if (!user.value) {
            return false;
        }

        // Si es SUPER ADMINISTRADOR o SUPER USUARIO, tiene todos los permisos
        const tipoUsuario = user.value.tipo;
        if (tipoUsuario === 'SUPER ADMINISTRADOR' || tipoUsuario === 'SUPER USUARIO') {
            return true;
        }

        // Si no tiene permisos definidos, no tiene acceso
        if (!user.value.permisos || user.value.permisos.length === 0) {
            return false;
        }

        // Buscar el permiso para este menú
        const permiso = user.value.permisos.find((p: Permiso) => p.menu === menu);
        return permiso ? permiso.view === true : false;
    };

    /**
     * Filtra los items del menú según los permisos del usuario
     * 
     * Filtra recursivamente los items del menú, ocultando aquellos para los cuales
     * el usuario no tiene permiso de view. Los items sin `menu` definido (como
     * Dashboard o Perfil) siempre se mantienen visibles.
     * 
     * @param items - Array de items del menú a filtrar
     * @returns Array de items filtrados según permisos del usuario
     */
    const filtrarItemsMenu = (items: MenuItem[]): MenuItem[] => {
        return items
            .map((item) => {
                // Si el item tiene children, filtrar recursivamente primero
                if (item.children && item.children.length > 0) {
                    const childrenFiltrados = filtrarItemsMenu(item.children);
                    
                    // Si después de filtrar no quedan children, ocultar el item padre
                    if (childrenFiltrados.length === 0) {
                        return null;
                    }
                    
                    // Si el item padre tiene menu definido, verificar permiso
                    // Si no tiene permiso directo pero tiene children visibles, mostrarlo igual
                    // (esto permite que módulos como "Configuración" se muestren si tienen al menos un child visible)
                    if (item.menu) {
                        // Si tiene permiso directo o tiene children visibles, mostrarlo
                        if (tienePermisoView(item.menu) || childrenFiltrados.length > 0) {
                            return {
                                ...item,
                                children: childrenFiltrados,
                            };
                        }
                        return null;
                    }
                    
                    // Si no tiene menu definido (como Dashboard), mantenerlo si tiene children
                    return {
                        ...item,
                        children: childrenFiltrados,
                    };
                }
                
                // Si no tiene children, verificar permiso directamente
                if (item.menu) {
                    return tienePermisoView(item.menu) ? item : null;
                }
                
                // Si no tiene menu definido (como Dashboard, Perfil), mantenerlo
                return item;
            })
            .filter((item): item is MenuItem => item !== null);
    };

    return {
        tienePermisoView,
        filtrarItemsMenu,
    };
}

