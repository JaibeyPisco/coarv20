<script setup lang="ts">
import { RouterLink, useRouter } from 'vue-router';
import { computed, onMounted, ref } from 'vue';
import { useAuthStore } from '../../../stores/auth';

interface TopNavItem {
    label: string;
    href: string;
    icon?: string;
    active?: boolean;
}

interface UserMenuItem {
    label: string;
    href: string;
    method?: string;
    as?: string;
}

interface User {
    name?: string;
    email?: string;
    avatar_url?: string;
    initials?: string;
}

const props = defineProps<{
    nav: TopNavItem[];
    user: User;
    userMenu: UserMenuItem[];
    collapseId?: string;
}>();

const router = useRouter();
const authStore = useAuthStore();

const THEME_STORAGE_KEY = 'tabler-theme';
const theme = ref('light');

function applyTheme(mode: string) {
    const value = mode === 'dark' ? 'dark' : 'light';
    document.documentElement.setAttribute('data-bs-theme', value);
    window.localStorage.setItem(THEME_STORAGE_KEY, value);
    theme.value = value;
}

function toggleTheme() {
    applyTheme(theme.value === 'dark' ? 'light' : 'dark');
}

const themeLabel = computed(() =>
    theme.value === 'dark' ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro',
);
const isDark = computed(() => theme.value === 'dark');

async function handleLogout() {
    await authStore.logout();
    router.push({ name: 'login' });
}

onMounted(() => {
    const stored =
        window.localStorage.getItem(THEME_STORAGE_KEY) ||
        document.documentElement.getAttribute('data-bs-theme') ||
        'light';
    applyTheme(stored);
});
</script>

<template>
    <header class="navbar navbar-expand-md d-print-none">
        <div class="container-xl">
            <div class="navbar-brand flex-grow-1 flex-md-grow-0 pe-3 align-items-center d-none d-md-flex">
                <slot name="brand" />
            </div>


            <div class="navbar-nav flex-row order-md-last align-items-center">
                <div class="nav-item me-2">
                    <button
                        type="button"
                        class="btn btn-outline-secondary btn-icon theme-toggle-btn"
                        :aria-label="themeLabel"
                        @click="toggleTheme"
                    >
                        <svg
                            v-if="isDark"
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                            focusable="false"
                            class="icon"
                        >
                            <circle cx="12" cy="12" r="4" />
                            <path d="M3 12h1m8-9v1m8 8h1m-9 8v1m-7.071-2.071l.707-.707m12.728 0l.707.707m0-12.728l-.707.707M5.636 5.636l-.707-.707" />
                        </svg>
                        <svg
                            v-else
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                            focusable="false"
                            class="icon"
                        >
                            <path d="M12 3c.132 0 .263.003.393.01a7.5 7.5 0 0 0 8.596 8.596a6 6 0 1 1 -8.99 -8.606z" />
                            <path d="M17.5 17.5l-1.5 1.5l1.5 1.5" />
                            <path d="M20.5 17.5l1.5 1.5l-1.5 1.5" />
                            <path d="M18.5 15l-1.5 1.5l1.5 1.5" />
                        </svg>
                    </button>
                </div>
                <slot name="actions" />
                <div class="nav-item dropdown">
                    <a
                        class="nav-link d-flex lh-1 text-reset p-0"
                        href="#"
                        data-bs-toggle="dropdown"
                        aria-label="Abrir menú de usuario"
                    >
                        <span class="avatar avatar-sm" aria-hidden="true">
                            <img
                                v-if="props.user?.avatar_url"
                                :src="props.user.avatar_url"
                                alt="Avatar de usuario"
                                class="avatar-img"
                            />
                            <span v-else>
                                {{ (props.user?.initials || props.user?.name || '?').toString().slice(0, 2).toUpperCase() }}
                            </span>
                        </span>
                        <div class="d-none d-xl-block ps-2">
                            <div>{{ props.user?.name }}</div>
                            <div class="mt-1 small text-secondary">
                                {{ props.user?.email }}
                            </div>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                        <template v-for="(item, index) in props.userMenu" :key="`user-menu-${index}`">
                            <RouterLink
                                v-if="item.href !== '#' && item.label !== 'Cerrar sesión'"
                                class="dropdown-item"
                                :to="item.href"
                            >
                                {{ item.label }}
                            </RouterLink>
                            <button
                                v-else-if="item.label === 'Cerrar sesión'"
                                class="dropdown-item"
                                type="button"
                                @click="handleLogout"
                            >
                                {{ item.label }}
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <div class="collapse navbar-collapse" :id="props.collapseId || 'navbar-menu'">
                <nav aria-label="Primary">
                    <ul class="navbar-nav">
                        <li
                            v-for="(item, index) in props.nav"
                            :key="`top-nav-${index}`"
                            class="nav-item"
                            :class="{ active: item.active }"
                        >
                            <RouterLink
                                class="nav-link"
                                :to="item.href"
                            >
                                <span
                                    v-if="item.icon"
                                    class="nav-link-icon d-md-none d-lg-inline-block"
                                >
                                    <i :class="['icon', item.icon]" />
                                </span>
                                <span class="nav-link-title">{{ item.label }}</span>
                            </RouterLink>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
</template>


