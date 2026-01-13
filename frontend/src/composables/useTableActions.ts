
export function useTableActions() {

    const closeAllActionDropdowns = () => {
        document.querySelectorAll('.tabulator .actions-menu__dropdown.show').forEach((menu) => {
            menu.classList.remove('show');
        });
    };

    const handleGlobalClick = (e: MouseEvent) => {
        const target = e.target as HTMLElement;
        if (!target.closest('.tabulator .btn-group')) {
            closeAllActionDropdowns();
        }
    };

    const handleActionCellClick = (
        e: MouseEvent,
        cell: any,
        actions: {
            edit?: (data: any) => void;
            delete?: (data: any) => void;
            [key: string]: ((data: any) => void) | undefined;
        }
    ) => {
        const target = e.target as HTMLElement;
        const toggleButton = target.closest('button[data-action="toggle-menu"]');

        if (toggleButton) {
            e.stopPropagation();
            const group = toggleButton.closest('.actions-menu');
            const menu = group?.querySelector('.actions-menu__dropdown');
            const isOpen = menu?.classList.contains('show');
            closeAllActionDropdowns();
            if (!isOpen) {
                menu?.classList.add('show');
            }
            return;
        }

        const actionBtn = target.closest('button[data-action]');
        if (!actionBtn) return;

        e.stopPropagation();
        closeAllActionDropdowns();
        const action = actionBtn.getAttribute('data-action');
        const data = cell.getRow().getData();

        const actionHandler = actions[action || ''];
        if (actionHandler) {
            actionHandler(data);
        }
    };

    return {
        closeAllActionDropdowns,
        handleGlobalClick,
        handleActionCellClick,
    };
}

