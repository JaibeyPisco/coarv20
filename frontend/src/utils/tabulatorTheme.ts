/**
 * Utilidad para obtener el tema actual y configurar Tabulator
 */

/**
 * Obtiene el tema actual del documento
 */
export function getCurrentTheme(): 'dark' | 'light' {
    if (typeof document === 'undefined') {
        return 'light';
    }
    
    const theme = document.documentElement.getAttribute('data-bs-theme');
    return theme === 'dark' ? 'dark' : 'light';
}

/**
 * Obtiene la configuración base de Tabulator con tema
 */
export function getTabulatorThemeConfig() {
    const theme = getCurrentTheme();
    
    return {
        theme: 'bootstrap5',
        themeConfig: {
            header: {
                backgroundColor: theme === 'dark' ? 'rgba(30, 41, 59, 0.95)' : 'rgba(244, 247, 252, 0.92)',
                color: theme === 'dark' ? 'rgba(255, 255, 255, 0.9)' : '#0f172a',
            },
            row: {
                backgroundColor: theme === 'dark' ? '#1e293b' : '#fbfcfe',
                color: theme === 'dark' ? 'rgba(255, 255, 255, 0.85)' : '#0f172a',
            },
            cell: {
                borderColor: theme === 'dark' ? 'rgba(255, 255, 255, 0.05)' : 'rgba(120, 144, 156, 0.12)',
            },
        },
    };
}

/**
 * Aplica estilos personalizados a una instancia de Tabulator basado en el tema
 */
export function applyTabulatorTheme(table: any) {
    if (!table) return;
    
    const theme = getCurrentTheme();
    const tableElement = table.element;
    
    if (!tableElement) return;
    
    // Aplicar clase de tema si es necesario
    if (theme === 'dark') {
        tableElement.classList.add('tabulator-dark');
    } else {
        tableElement.classList.remove('tabulator-dark');
    }
    
    // Forzar redibujado para aplicar estilos
    table.redraw(true);
}


