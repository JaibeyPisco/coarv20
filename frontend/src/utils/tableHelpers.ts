/**
 * Utilidades compartidas para tablas
 */

export interface ActionColumnConfig {
    width?: number;
    hasEdit?: boolean;
    hasDelete?: boolean;
    customActions?: Array<{
        action: string;
        label: string;
        icon?: string;
        className?: string;
    }>;
}

/**
 * Genera la columna de acciones estándar para Tabulator
 */
export function createActionColumn(
    config: ActionColumnConfig = {},
    cellClickHandler: (e: MouseEvent, cell: any) => void
) {
    const {
        width = 120,
        hasEdit = true,
        hasDelete = true,
        customActions = [],
    } = config;

    return {
        title: 'ACCIONES',
        field: '_actions',

        width,
        headerHozAlign: 'center' as const,
        hozAlign: 'center' as const,
        resizable: false,
        headerSort: false,
        formatter: (cell: any) => {
            const data = cell.getRow().getData();
            const isActive = data.estado === 1;

            let editButton = '';
            if (hasEdit) {
                editButton = `
                    <button
                        class="btn btn-sm btn-primary"
                        type="button"
                        data-action="edit">
                        Editar
                    </button>
                `;
            }

            let customActionsHtml = '';
            if (customActions.length > 0) {
                customActionsHtml = customActions.map(action => `
                    <button 
                        type="button" 
                        class="dropdown-item ${action.className || ''}" 
                        data-action="${action.action}"
                    >
                        ${action.icon ? `<i class="${action.icon} me-2"></i>` : ''}${action.label}
                    </button>
                `).join('');
            }

            let deleteButton = '';
            if (hasDelete) {
                deleteButton = `
                    <button 
                        type="button" 
                        class="dropdown-item text-danger" 
                        data-action="delete"
                    >
                        <i class="ti ti-trash me-2"></i>Eliminar
                    </button>
                `;
            }

            const hasDropdown = customActions.length > 0 || hasDelete;

            return `
                <div class="btn-group actions-menu" style="position: relative;">
                    ${editButton}
                    ${hasDropdown ? `
                        <button
                            class="btn btn-sm btn-primary dropdown-toggle dropdown-toggle-split actions-menu__toggle"
                            type="button"
                            data-action="toggle-menu"
                        >
                            <span class="visually-hidden">Toggle Dropdown</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-start actions-menu__dropdown" style="position: absolute; left: 0; top: 100%; margin-top: 0.125rem; min-width: 180px; z-index: 1000;">
                            ${customActionsHtml}
                            ${deleteButton}
                        </div>
                    ` : ''}
                </div>
            `;
        },
        cellClick: cellClickHandler,
    };
}

/**
 * Genera columnas estándar comunes
 */
export function createStandardColumns(fields: Array<{
    title: string;
    field: string;
    minWidth?: number;
    width?: number;
    formatter?: string | ((cell: any) => string);
}>) {
    return fields.map(field => ({
        title: field.title,
        field: field.field,
        minWidth: field.minWidth || 150,
        width: field.width,
        headerSort: true,
        formatter: field.formatter || 'plaintext',
    }));
}

