import { nextTick } from 'vue';
import type { SidebarItem } from '../types/sidebar';

export function useSidebarDropdown() {
    const hasChildren = (item: SidebarItem): boolean => {
        return Array.isArray(item.children) && item.children.length > 0;
    };

    const hasActiveChild = (item: SidebarItem): boolean => {
        if (!hasChildren(item)) return false;
        return item.children!.some(child => child.active === true);
    };

    const handleDropdownToggle = (event: Event, item: SidebarItem): boolean => {
        if (hasActiveChild(item)) {
            event.preventDefault();
            event.stopPropagation();
            const dropdown = (event.target as HTMLElement).closest('.nav-item.dropdown');
            if (dropdown) {
                dropdown.classList.add('show');
                const dropdownMenu = dropdown.querySelector('.dropdown-menu');
                if (dropdownMenu) {
                    dropdownMenu.classList.add('show');
                }
                (event.target as HTMLElement).setAttribute('aria-expanded', 'true');
            }
            return false;
        }
        return true;
    };

    const keepActiveDropdownsOpen = () => {
        nextTick(() => {
            const dropdownToggles = document.querySelectorAll('.nav-item.dropdown .nav-link.dropdown-toggle');
            
            dropdownToggles.forEach(toggle => {
                const dropdown = toggle.closest('.nav-item.dropdown');
                const dropdownMenu = dropdown?.querySelector('.dropdown-menu');
                
                if (dropdown && dropdownMenu) {
                    const hasActive = dropdown.querySelector('.dropdown-item.active') !== null;
                    
                    if (hasActive) {
                        dropdown.classList.add('show');
                        dropdownMenu.classList.add('show');
                        toggle.setAttribute('aria-expanded', 'true');
                        
                        if ((window as any).bootstrap) {
                            const dropdownInstance = (window as any).bootstrap.Dropdown.getOrCreateInstance(toggle, {
                                autoClose: false
                            });
                            if (!dropdownMenu.classList.contains('show')) {
                                dropdownInstance.show();
                            }
                        }
                        
                        const preventClose = (e: Event) => {
                            if (hasActive) {
                                e.preventDefault();
                                e.stopPropagation();
                                e.stopImmediatePropagation();
                                dropdown.classList.add('show');
                                dropdownMenu.classList.add('show');
                                toggle.setAttribute('aria-expanded', 'true');
                                return false;
                            }
                        };
                        
                        if ((toggle as any)._preventCloseHandler) {
                            toggle.removeEventListener('hide.bs.dropdown', (toggle as any)._preventCloseHandler);
                        }
                        
                        (toggle as any)._preventCloseHandler = preventClose;
                        toggle.addEventListener('hide.bs.dropdown', preventClose, true);
                        
                        const dropdownItems = dropdownMenu.querySelectorAll('.dropdown-item');
                        dropdownItems.forEach(item => {
                            if ((item as any)._keepOpenHandler) {
                                item.removeEventListener('click', (item as any)._keepOpenHandler);
                            }
                            
                            const keepOpenHandler = (e: Event) => {
                                if (hasActive) {
                                    e.stopPropagation();
                                    setTimeout(() => {
                                        dropdown.classList.add('show');
                                        dropdownMenu.classList.add('show');
                                        toggle.setAttribute('aria-expanded', 'true');
                                        
                                        if ((window as any).bootstrap) {
                                            const dropdownInstance = (window as any).bootstrap.Dropdown.getOrCreateInstance(toggle, {
                                                autoClose: false
                                            });
                                            if (!dropdownMenu.classList.contains('show')) {
                                                dropdownInstance.show();
                                            }
                                        }
                                    }, 50);
                                }
                            };
                            
                            (item as any)._keepOpenHandler = keepOpenHandler;
                            item.addEventListener('click', keepOpenHandler, true);
                        });
                    } else {
                        if ((toggle as any)._preventCloseHandler) {
                            toggle.removeEventListener('hide.bs.dropdown', (toggle as any)._preventCloseHandler);
                            delete (toggle as any)._preventCloseHandler;
                        }
                    }
                }
            });
        });
    };

    let globalPreventCloseHandler: ((e: Event) => void) | null = null;
    let globalClickHandler: ((e: Event) => void) | null = null;

    const setupGlobalDropdownPrevention = () => {
        // Evitar múltiples registros
        if (globalPreventCloseHandler && globalClickHandler) {
            return;
        }

        globalPreventCloseHandler = (e: Event) => {
            let target = e.target as HTMLElement;
            let dropdown: Element | null = null;
            
            while (target && target !== document.body) {
                if (target.classList?.contains('nav-item') && target.classList.contains('dropdown')) {
                    dropdown = target;
                    break;
                }
                target = target.parentElement as HTMLElement;
            }
            
            if (dropdown) {
                const hasActive = dropdown.querySelector('.dropdown-item.active') !== null;
                if (hasActive) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    dropdown.classList.add('show');
                    const dropdownMenu = dropdown.querySelector('.dropdown-menu');
                    if (dropdownMenu) {
                        dropdownMenu.classList.add('show');
                    }
                    const toggle = dropdown.querySelector('.nav-link.dropdown-toggle');
                    if (toggle) {
                        toggle.setAttribute('aria-expanded', 'true');
                    }
                    
                    if ((window as any).bootstrap && toggle) {
                        const dropdownInstance = (window as any).bootstrap.Dropdown.getInstance(toggle);
                        if (dropdownInstance) {
                            dropdownInstance._config.autoClose = false;
                        }
                    }
                    
                    return false;
                }
            }
        };
        
        globalClickHandler = (e: Event) => {
            const clickedDropdown = (e.target as HTMLElement).closest('.nav-item.dropdown');
            if (!clickedDropdown) {
                const allDropdowns = document.querySelectorAll('.nav-item.dropdown.show');
                allDropdowns.forEach(dropdown => {
                    const hasActive = dropdown.querySelector('.dropdown-item.active') !== null;
                    if (hasActive) {
                        dropdown.classList.add('show');
                        const dropdownMenu = dropdown.querySelector('.dropdown-menu');
                        if (dropdownMenu) {
                            dropdownMenu.classList.add('show');
                        }
                        const toggle = dropdown.querySelector('.nav-link.dropdown-toggle');
                        if (toggle) {
                            toggle.setAttribute('aria-expanded', 'true');
                        }
                    }
                });
            }
        };
        
        document.addEventListener('hide.bs.dropdown', globalPreventCloseHandler, true);
        document.addEventListener('click', globalClickHandler, true);
    };

    const cleanupGlobalDropdownPrevention = () => {
        if (globalPreventCloseHandler) {
            document.removeEventListener('hide.bs.dropdown', globalPreventCloseHandler, true);
            globalPreventCloseHandler = null;
        }
        if (globalClickHandler) {
            document.removeEventListener('click', globalClickHandler, true);
            globalClickHandler = null;
        }
    };

    return {
        hasChildren,
        hasActiveChild,
        handleDropdownToggle,
        keepActiveDropdownsOpen,
        setupGlobalDropdownPrevention,
        cleanupGlobalDropdownPrevention
    };
}

