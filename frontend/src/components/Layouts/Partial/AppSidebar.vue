<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useDisplay } from 'vuetify';
import type { SidebarItem } from '../../../types/sidebar';
import { mapTablerIconToMDI } from '../../../utils/iconMapper';
import { useMenuPermissions } from '../../../composables/useMenuPermissions';
import { useAuthStore } from '../../../stores/auth';

const props = defineProps<{
    items: SidebarItem[];
}>();

const route = useRoute();
const { mobile } = useDisplay();
const authStore = useAuthStore();

const drawer = ref(!mobile.value);
const openGroups = ref<Set<string>>(new Set());

const user = computed(() => authStore.user);
const { filtrarItemsMenu } = useMenuPermissions(user);

// Filtrar items según permisos
const filteredItems = computed(() => filtrarItemsMenu(props.items));

// Detectar items activos y abrir grupos automáticamente
watch(
    () => route.path,
    () => {
        filteredItems.value.forEach((item, index) => {
            if (item.children) {
                const hasActive = item.children.some(child => child.active);
                if (hasActive) {
                    openGroups.value.add(`group-${index}`);
                }
            }
        });
    },
    { immediate: true }
);

// Detectar si un item tiene hijos activos
const hasActiveChild = (item: SidebarItem): boolean => {
    if (!item.children) return false;
    return item.children.some(child => child.active === true);
};

// Cerrar drawer en móvil al hacer click en un link
const handleLinkClick = () => {
    if (mobile.value) {
        drawer.value = false;
    }
};

// Exponer drawer para control externo
defineExpose({
    drawer,
    toggle: () => {
        drawer.value = !drawer.value;
    },
    open: () => {
        drawer.value = true;
    },
    close: () => {
        drawer.value = false;
    },
});
</script>

<template>
    <v-navigation-drawer
        v-model="drawer"
        :permanent="!mobile"
        :temporary="mobile"
        location="left"
        color="surface"
    >
        <template #prepend>
            <slot name="brand">
                <v-list-item
                    prepend-avatar="https://via.placeholder.com/40"
                    title="COAR"
                    subtitle="Sistema de Gestión"
                    to="/dashboard"
                />
            </slot>
        </template>

        <v-list density="compact" nav>
            <template v-for="(item, index) in filteredItems" :key="`sidebar-item-${index}`">
                <!-- Item con hijos (grupo expandible) -->
                <v-list-group
                    v-if="item.children && item.children.length > 0"
                    :value="openGroups.has(`group-${index}`) || hasActiveChild(item)"
                    @update:value="(value: boolean) => value ? openGroups.add(`group-${index}`) : openGroups.delete(`group-${index}`)"
                >
                    <template #activator="{ props: groupProps }">
                        <v-list-item
                            v-bind="groupProps"
                            :prepend-icon="item.icon ? mapTablerIconToMDI(item.icon) : undefined"
                            :title="item.label"
                            :active="item.active || hasActiveChild(item)"
                        />
                    </template>

                    <v-list-item
                        v-for="(child, childIndex) in item.children"
                        :key="`child-${index}-${childIndex}`"
                        :prepend-icon="child.icon ? mapTablerIconToMDI(child.icon) : undefined"
                        :title="child.label"
                        :value="child.href"
                        :to="child.href"
                        :active="child.active"
                        @click="handleLinkClick"
                    >
                        <template v-if="(child as SidebarItem).badge" #append>
                            <v-chip
                                :color="(child as SidebarItem).badge?.variant || 'primary'"
                                size="x-small"
                                variant="flat"
                            >
                                {{ (child as SidebarItem).badge?.text }}
                            </v-chip>
                        </template>
                    </v-list-item>
                </v-list-group>

                <!-- Item simple (sin hijos) -->
                <v-list-item
                    v-else-if="item.href"
                    :prepend-icon="item.icon ? mapTablerIconToMDI(item.icon) : undefined"
                    :title="item.label"
                    :value="item.href"
                    :to="item.href"
                    :active="item.active"
                    @click="handleLinkClick"
                >
                    <template v-if="(item as SidebarItem).badge" #append>
                        <v-chip
                            :color="(item as SidebarItem).badge?.variant || 'primary'"
                            size="x-small"
                            variant="flat"
                        >
                            {{ (item as SidebarItem).badge?.text }}
                        </v-chip>
                    </template>
                </v-list-item>
            </template>
        </v-list>
    </v-navigation-drawer>
</template>
