<template>
    <v-app>
        <AppLoader />
        <AppNotifications />
        <router-view />
    </v-app>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { useAuthStore } from './stores/auth';
import AppLoader from './components/AppLoader.vue';
import AppNotifications from './components/AppNotifications.vue';

const authStore = useAuthStore();

onMounted(async () => {
    // Solo verificar autenticación si hay un token guardado pero no hay usuario
    // Esto evita peticiones innecesarias si ya tenemos el usuario
    if (authStore.token && !authStore.user) {
        await authStore.checkAuth();
    }
});
</script>
