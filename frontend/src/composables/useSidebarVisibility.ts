import { ref, onBeforeUnmount } from 'vue';

export function useSidebarVisibility(collapseId: string) {
    const togglerId = `toggler-${collapseId}`;
    const isDesktop = ref(typeof window !== 'undefined' && window.innerWidth >= 992);

    const ensureDesktopVisibility = () => {
        if (typeof window === 'undefined') return;
        
        isDesktop.value = window.innerWidth >= 992;
        
        const collapseEl = document.getElementById(collapseId);
        const togglerEl = document.getElementById(togglerId) || document.querySelector(`[data-bs-target="#${collapseId}"]`);
        
        if (collapseEl) {
            if (isDesktop.value) {
                collapseEl.classList.add('show');
                collapseEl.style.display = 'block';
                
                if (togglerEl) {
                    togglerEl.setAttribute('aria-expanded', 'true');
                }
            }
        }
    };

    const handleResize = () => {
        ensureDesktopVisibility();
    };

    const handleNavLinkClick = (e: Event) => {
        const target = e.target as HTMLElement;
        const link = target.closest('a');
        
        if (!link || !link.getAttribute('href') || link.getAttribute('href') === '#') {
            return;
        }
        
        if (window.innerWidth >= 992) {
            e.stopPropagation();
            return;
        }
        
        const collapseEl = document.getElementById(collapseId);
        if (collapseEl && collapseEl.classList.contains('show')) {
            setTimeout(() => {
                if (window.innerWidth < 992 && collapseEl.classList.contains('show')) {
                    const bsCollapse = (window as any).bootstrap?.Collapse?.getInstance(collapseEl);
                    if (bsCollapse) {
                        bsCollapse.hide();
                    } else {
                        collapseEl.classList.remove('show');
                        collapseEl.style.display = 'none';
                        const togglerEl = document.getElementById(togglerId) || document.querySelector(`[data-bs-target="#${collapseId}"]`);
                        if (togglerEl) {
                            togglerEl.setAttribute('aria-expanded', 'false');
                        }
                    }
                }
            }, 300);
        }
    };

    const setupSidebarListeners = () => {
        const collapseEl = document.getElementById(collapseId);
        if (!collapseEl) return;

        collapseEl.addEventListener('hide.bs.collapse', (e) => {
            if (window.innerWidth >= 992) {
                e.preventDefault();
                e.stopImmediatePropagation();
                collapseEl.classList.add('show');
                collapseEl.style.display = 'block';
            }
        });

        const observer = new MutationObserver((mutations) => {
            if (window.innerWidth >= 992) {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        const target = mutation.target as HTMLElement;
                        if (!target.classList.contains('show')) {
                            target.classList.add('show');
                            target.style.display = 'block';
                        }
                    }
                });
            }
        });

        observer.observe(collapseEl, {
            attributes: true,
            attributeFilter: ['class', 'style'],
        });

        const navLinks = collapseEl.querySelectorAll('.nav-link, .dropdown-item');
        navLinks.forEach((link) => {
            link.addEventListener('click', handleNavLinkClick);
        });

        (collapseEl as any)._sidebarObserver = observer;

        return () => {
            observer.disconnect();
            delete (collapseEl as any)._sidebarObserver;
            navLinks.forEach((link) => {
                link.removeEventListener('click', handleNavLinkClick);
            });
        };
    };

    const setupResizeListener = () => {
        window.addEventListener('resize', handleResize);
        return () => {
            window.removeEventListener('resize', handleResize);
        };
    };

    onBeforeUnmount(() => {
        // Cleanup se maneja en el componente principal
    });

    return {
        isDesktop,
        togglerId,
        ensureDesktopVisibility,
        handleNavLinkClick,
        setupSidebarListeners,
        setupResizeListener,
        handleResize
    };
}

