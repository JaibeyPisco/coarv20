<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useTheme } from 'vuetify';
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
    drawer?: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:drawer', value: boolean): void;
}>();

const router = useRouter();
const authStore = useAuthStore();
const theme = useTheme();

const isDark = computed(() => theme.current.value?.dark ?? false);

// Cargar tema desde localStorage
const loadThemeFromStorage = () => {
    try {
        const storedTheme = localStorage.getItem('app-theme');
        if (storedTheme === 'dark' || storedTheme === 'light') {
            theme.global.name.value = storedTheme;
        }
    } catch (error) {
        console.warn('Error loading theme from localStorage:', error);
    }
};

// Guardar tema en localStorage
const saveThemeToStorage = (themeName: string) => {
    try {
        localStorage.setItem('app-theme', themeName);
    } catch (error) {
        console.warn('Error saving theme to localStorage:', error);
    }
};

const toggleTheme = () => {
    const currentIsDark = theme.global.current.value?.dark ?? false;
    const newTheme = currentIsDark ? 'light' : 'dark';
    theme.global.name.value = newTheme;
    saveThemeToStorage(newTheme);
};

const themeLabel = computed(() =>
    isDark.value ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'
);

// Cargar tema al montar el componente
onMounted(() => {
    loadThemeFromStorage();
});

const toggleDrawer = () => {
    emit('update:drawer', !props.drawer);
};

async function handleLogout() {
    await authStore.logout();
    router.push({ name: 'login' });
}

const userInitials = computed(() => {
    if (props.user?.initials) return props.user.initials;
    if (props.user?.name) {
        const parts = props.user.name.split(' ');
        if (parts.length >= 2 && parts[0] && parts[1]) {
            return (parts[0][0] + parts[1][0]).toUpperCase();
        }
        return props.user.name.slice(0, 2).toUpperCase();
    }
    return '?';
});
</script>

<template>
    <v-app-bar color="surface" elevation="1">
        <v-app-bar-nav-icon @click="toggleDrawer" />

        <v-toolbar-title>
            <slot name="brand">
                <span class="text-body-2 font-weight-semibold">{{ props.user?.name || 'COAR' }}</span>
            </slot>
        </v-toolbar-title>

      

        <v-spacer />

        <!-- Botón de tema -->
        <v-btn
            icon
            :aria-label="themeLabel"
            @click="toggleTheme"
        >
            <v-icon>{{ isDark ? 'mdi-weather-sunny' : 'mdi-weather-night' }}</v-icon>
        </v-btn>

        <!-- Slot para acciones adicionales -->
        <slot name="actions" />

        <!-- Menú de usuario -->
        <v-menu location="bottom end">
            <template #activator="{ props: menuProps }">
                <v-btn
                    icon
                    v-bind="menuProps"
                    aria-label="Menú de usuario"
                >
                    <v-avatar size="32">
                        <v-img
                            v-if="props.user?.avatar_url"
                            :src="props.user.avatar_url"
                            :alt="`Avatar de ${props.user.name}`"
                        />
                        <span v-else>{{ userInitials }}</span>
                    </v-avatar>
                </v-btn>
            </template>

            <v-list>
                <v-list-item
                    v-if="props.user?.name || props.user?.email"
                    :title="props.user.name"
                    :subtitle="props.user.email"
                    disabled
                />
                <v-divider v-if="props.user?.name || props.user?.email" />
                <template v-for="(item, index) in props.userMenu" :key="`user-menu-${index}`">
                    <v-list-item
                        v-if="item.href !== '#' && item.label !== 'Cerrar sesión'"
                        :title="item.label"
                        :to="item.href"
                    />
                    <v-list-item
                        v-else-if="item.label === 'Cerrar sesión'"
                        :title="item.label"
                        prepend-icon="mdi-logout"
                        @click="handleLogout"
                    />
                </template>
            </v-list>
        </v-menu>
    </v-app-bar>
</template>
