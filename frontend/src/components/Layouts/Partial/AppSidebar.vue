<script setup lang="ts">
import { RouterLink, useRoute } from 'vue-router';
import { onMounted, onBeforeUnmount, nextTick, watch } from 'vue';
import { useSidebarDropdown } from '../../../composables/useSidebarDropdown';
import { useSidebarVisibility } from '../../../composables/useSidebarVisibility';
import type { SidebarItem } from '../../../types/sidebar';

const props = defineProps<{
    items: SidebarItem[];
    theme?: string;
    collapseId?: string;
}>();

const route = useRoute();
const collapseId = props.collapseId || 'sidebar-menu';

// Composables
const {
    hasChildren,
    hasActiveChild,
    handleDropdownToggle,
    keepActiveDropdownsOpen,
    setupGlobalDropdownPrevention
} = useSidebarDropdown();

const {
    isDesktop,
    togglerId,
    ensureDesktopVisibility,
    handleNavLinkClick,
    setupSidebarListeners,
    setupResizeListener
} = useSidebarVisibility(collapseId);

const {
    cleanupGlobalDropdownPrevention
} = useSidebarDropdown();

// Setup
let cleanupSidebarListeners: (() => void) | null | undefined = null;
let cleanupResizeListener: (() => void) | null | undefined = null;

onMounted(async () => {
    await nextTick();
    
    // Inicialización única - evitar doble carga
    setTimeout(() => {
        ensureDesktopVisibility();
        keepActiveDropdownsOpen();
        setupGlobalDropdownPrevention();
    }, 100);
    
    cleanupSidebarListeners = setupSidebarListeners();
    cleanupResizeListener = setupResizeListener();
});

onBeforeUnmount(() => {
    if (cleanupSidebarListeners) {
        cleanupSidebarListeners();
    }
    if (cleanupResizeListener) {
        cleanupResizeListener();
    }
    cleanupGlobalDropdownPrevention();
});

// Watchers
watch(() => props.items, () => {
    keepActiveDropdownsOpen();
}, { deep: true });

watch(() => route.path, () => {
    nextTick(() => {
        setTimeout(() => {
            ensureDesktopVisibility();
            keepActiveDropdownsOpen();
            
            const collapseEl = document.getElementById(collapseId);
            if (collapseEl) {
                const navLinks = collapseEl.querySelectorAll('.nav-link, .dropdown-item');
                navLinks.forEach((link) => {
                    link.removeEventListener('click', handleNavLinkClick);
                    link.addEventListener('click', handleNavLinkClick);
                });
            }
        }, 50);
    });
}, { immediate: false });
</script>

<template>
    <aside class="navbar navbar-vertical navbar-expand-lg navbar-dark-neutral" :data-bs-theme="props.theme || 'dark'">
        <div class="container-fluid">
            <button
                :id="togglerId"
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                :data-bs-target="`#${collapseId}`"
                :aria-controls="collapseId"
                :aria-expanded="isDesktop ? 'true' : 'false'"
                aria-label="Toggle sidebar navigation"
            >
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="navbar-brand navbar-brand-autodark">
                <slot name="brand">
                    <RouterLink class="navbar-brand-text fw-semibold text-decoration-none" to="/">
                        Dashboard
                    </RouterLink>
                </slot>
            </div>

            <div 
                class="collapse navbar-collapse"
                :class="{ show: isDesktop }"
                :id="props.collapseId || 'sidebar-menu'"
            >
                <ul class="navbar-nav pt-lg-3">
                    <template v-for="(item, index) in props.items" :key="`${item.label}-${index}`">
                        <li
                            v-if="hasChildren(item)"
                            class="nav-item dropdown"
                            :class="{ 
                                active: item.active || hasActiveChild(item),
                                show: hasActiveChild(item)
                            }"
                        >
                            <a
                                class="nav-link dropdown-toggle"
                                href="#"
                                data-bs-toggle="dropdown"
                                data-bs-auto-close="false"
                                role="button"
                                :aria-expanded="hasActiveChild(item) ? 'true' : 'false'"
                                @click.prevent="handleDropdownToggle($event, item)"
                            >
                                <span v-if="item.icon" class="nav-link-icon d-md-none d-lg-inline-block">
                                    <i :class="['icon', item.icon]" />
                                </span>
                                <span class="nav-link-title">
                                    {{ item.label }}
                                </span>
                            </a>
                            <div 
                                class="dropdown-menu"
                                :class="{ show: hasActiveChild(item) }"
                                @click.stop
                            >
                                <template v-for="(child, childIndex) in item.children" :key="`${child.label}-${childIndex}`">
                                    <RouterLink
                                        v-if="child.href && child.href !== '#'"
                                        class="dropdown-item"
                                        :to="child.href"
                                        :class="{ active: child.active }"
                                        @click.stop
                                    >
                                        {{ child.label }}
                                    </RouterLink>
                                </template>
                            </div>
                        </li>
                        <li
                            v-else-if="item.href"
                            class="nav-item"
                            :class="{ active: item.active }"
                        >
                            <RouterLink
                                class="nav-link"
                                :to="item.href"
                            >
                                <span v-if="item.icon" class="nav-link-icon d-md-none d-lg-inline-block">
                                    <i :class="['icon', item.icon]" />
                                </span>
                                <span class="nav-link-title">
                                    {{ item.label }}
                                </span>
                                <span
                                    v-if="item.badge"
                                    class="badge ms-auto"
                                    :class="`bg-${item.badge.variant || 'primary'}-lt`"
                                >
                                    {{ item.badge.text }}
                                </span>
                            </RouterLink>
                        </li>
                    </template>
                </ul>
            </div>
        </div>
    </aside>
</template>

<style>
/* Asegurar que Tabler muestre el sidebar en desktop (lg+) */
/* Sin scoped para que no entre en conflicto con Tabler CSS */
@media (min-width: 992px) {
    /* Forzar que el collapse se muestre en desktop */
    aside.navbar-vertical.navbar-expand-lg .navbar-collapse {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    /* Asegurar que los elementos del menú sean visibles */
    aside.navbar-vertical.navbar-expand-lg .navbar-nav {
        display: block !important;
    }
    
    aside.navbar-vertical.navbar-expand-lg .nav-item {
        display: block !important;
    }
    
    aside.navbar-vertical.navbar-expand-lg .nav-link {
        display: flex !important;
        align-items: center !important;
    }
}

/* En móvil, asegurar que cuando se muestre, no se oculte automáticamente */
@media (max-width: 991.98px) {
    aside.navbar-vertical.navbar-expand-lg .navbar-collapse.show {
        display: block !important;
    }
}

/* Asegurar que los dropdowns con hijos activos se mantengan abiertos */
.nav-item.dropdown.show .dropdown-menu {
    display: block !important;
    opacity: 1 !important;
    visibility: visible !important;
}

.nav-item.dropdown.show .nav-link.dropdown-toggle {
    color: var(--tblr-nav-link-color-active, inherit);
}

/* Prevenir que el dropdown se cierre cuando tiene un hijo activo */
.nav-item.dropdown.show .dropdown-menu.show {
    position: static;
    float: none;
    width: 100%;
    margin-top: 0;
    background-color: transparent;
    border: 0;
    box-shadow: none;
    display: block !important;
}

/* Forzar que el dropdown se mantenga visible cuando tiene la clase show */
.nav-item.dropdown.show {
    position: relative;
}

.nav-item.dropdown.show .dropdown-menu {
    position: static !important;
    transform: none !important;
    will-change: auto !important;
}
</style>

