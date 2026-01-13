/**
 * Mapeo de iconos de Tabler Icons a Material Design Icons
 * Para facilitar la migración de iconos durante la transición a Vuetify
 */

const iconMap: Record<string, string> = {
    'ti-dashboard': 'mdi-view-dashboard',
    'ti-user': 'mdi-account',
    'ti-settings': 'mdi-cog',
    'ti-home': 'mdi-home',
    'ti-plus': 'mdi-plus',
    'ti-edit': 'mdi-pencil',
    'ti-trash': 'mdi-delete',
    'ti-search': 'mdi-magnify',
    'ti-printer': 'mdi-printer',
    'ti-file-spreadsheet': 'mdi-file-excel',
    'ti-eye': 'mdi-eye',
    'ti-eye-off': 'mdi-eye-off',
    'ti-times': 'mdi-close',
    'ti-check': 'mdi-check',
    'ti-x': 'mdi-close',
    'ti-chevron-down': 'mdi-chevron-down',
    'ti-chevron-up': 'mdi-chevron-up',
    'ti-chevron-left': 'mdi-chevron-left',
    'ti-chevron-right': 'mdi-chevron-right',
    'ti-menu': 'mdi-menu',
    'ti-logout': 'mdi-logout',
    'ti-lock': 'mdi-lock',
    'ti-mail': 'mdi-email',
    'ti-phone': 'mdi-phone',
    'ti-calendar': 'mdi-calendar',
    'ti-clock': 'mdi-clock',
    'ti-arrow-left': 'mdi-arrow-left',
    'ti-arrow-right': 'mdi-arrow-right',
    'ti-download': 'mdi-download',
    'ti-upload': 'mdi-upload',
    'ti-file': 'mdi-file',
    'ti-folder': 'mdi-folder',
    'ti-image': 'mdi-image',
    'ti-link': 'mdi-link',
    'ti-info-circle': 'mdi-information',
    'ti-alert-circle': 'mdi-alert-circle',
    'ti-check-circle': 'mdi-check-circle',
    'ti-x-circle': 'mdi-close-circle',
    'ti-star': 'mdi-star',
    'ti-heart': 'mdi-heart',
    'ti-share': 'mdi-share',
    'ti-filter': 'mdi-filter',
    'ti-sort-asc': 'mdi-sort-ascending',
    'ti-sort-desc': 'mdi-sort-descending',
    'ti-refresh': 'mdi-refresh',
    'ti-reload': 'mdi-reload',
    'ti-save': 'mdi-content-save',
    'ti-cancel': 'mdi-cancel',
    'ti-close': 'mdi-close',
};

/**
 * Convierte un icono de Tabler a Material Design Icon
 * @param tablerIcon - Icono de Tabler (ej: 'ti ti-dashboard' o 'ti-dashboard')
 * @returns Icono de Material Design (ej: 'mdi-view-dashboard')
 */
export function mapTablerIconToMDI(tablerIcon: string | undefined): string {
    if (!tablerIcon) return 'mdi-circle';
    
    // Limpiar el formato 'ti ti-icono' o 'icon ti-icono'
    const cleanIcon = tablerIcon
        .replace(/^(ti|icon)\s+/, '')
        .replace(/^ti-/, '')
        .trim();
    
    // Buscar en el mapa
    const mdiIcon = iconMap[cleanIcon] || iconMap[`ti-${cleanIcon}`];
    
    if (mdiIcon) {
        return mdiIcon;
    }
    
    // Si no se encuentra, retornar un icono por defecto
    // En el futuro se puede mejorar con conversión automática
    console.warn(`Icono no encontrado: ${tablerIcon}, usando mdi-circle como fallback`);
    return 'mdi-circle';
}

/**
 * Convierte múltiples iconos de Tabler a Material Design Icons
 */
export function mapTablerIconsToMDI(icons: string[]): string[] {
    return icons.map(mapTablerIconToMDI);
}
