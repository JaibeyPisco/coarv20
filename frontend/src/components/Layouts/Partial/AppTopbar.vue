<script setup lang="ts">
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useTheme } from 'vuetify';
import { useAuthStore } from '../../../stores/auth';
import { mapTablerIconToMDI } from '../../../utils/iconMapper';

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

const isDark = computed(() => theme.current.value.dark);

const toggleTheme = () => {
    theme.global.name.value = theme.global.current.value.dark ? 'light' : 'dark';
};

const themeLabel = computed(() =>
    isDark.value ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'
);

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

        <!-- Navegación superior (opcional, puede estar oculta en móvil) -->
        <v-btn-toggle
            v-if="nav && nav.length > 0"
            variant="text"
            density="compact"
            class="d-none d-md-flex"
        >
            <v-btn
                v-for="(item, index) in nav"
                :key="`top-nav-${index}`"
                :prepend-icon="item.icon ? mapTablerIconToMDI(item.icon) : undefined"
                :to="item.href"
                :active="item.active"
                variant="text"
            >
                {{ item.label }}
            </v-btn>
        </v-btn-toggle>

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
