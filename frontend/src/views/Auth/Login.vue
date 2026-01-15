<script setup lang="ts">
import { ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { notificacion } from '../../utils/notificacion';
import ApplicationLogo from '../../components/ApplicationLogo.vue';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const form = ref({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);
const loading = ref(false);
const errorMessage = ref('');
const status = ref('');

if (route.query.status) {
    status.value = route.query.status as string;
}

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};

const handleSubmit = async () => {
    errorMessage.value = '';
    loading.value = true;

    try {
        await authStore.login(form.value.email, form.value.password, form.value.remember);
        
        notificacion('Sesión iniciada correctamente', { type: 'success', title: 'Éxito' });
        
        await router.push({ name: 'dashboard' });
    } catch (error: any) {
        errorMessage.value = error.message || 'Error al iniciar sesión';
        notificacion(errorMessage.value, { type: 'error', title: 'Error' });
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <div class="d-flex flex-column bg-white min-vh-100">
        <a href="#main-login" class="visually-hidden skip-link">
            Skip to main content
        </a>

        <v-row no-gutters class="flex-fill flex-grow-1">
            <v-col
                cols="12"
                lg="6"
                xl="4"
                class="d-flex flex-column justify-center border-top-wide border-primary"
            >
                <v-container class="container-tight my-5 px-lg-5">
                    <div class="text-center mb-4">
                        <router-link to="/" aria-label="Home" class="text-decoration-none">
                            <ApplicationLogo class="mx-auto" style="width: 80px; height: 80px;" />
                        </router-link>
                    </div>

                    <h2 id="main-login" class="text-h4 text-center mb-3">
                        Inicia sesión en tu cuenta
                    </h2>

                    <v-alert
                        v-if="status"
                        type="success"
                        variant="tonal"
                        class="mb-4"
                    >
                        {{ status }}
                    </v-alert>

                    <v-alert
                        v-if="errorMessage"
                        type="error"
                        variant="tonal"
                        class="mb-4"
                    >
                        <p class="mb-0">{{ errorMessage }}</p>
                    </v-alert>

                    <v-form @submit.prevent="handleSubmit">
                        <v-text-field
                            v-model="form.email"
                            label="Correo electrónico"
                            type="email"
                            placeholder="correo@ejemplo.com"
                            :error="!!errorMessage"
                            required
                            autocomplete="username"
                            autofocus
                            class="mb-3"
                        />

                        <v-text-field
                            v-model="form.password"
                            label="Contraseña"
                            :type="showPassword ? 'text' : 'password'"
                            :error="!!errorMessage"
                            placeholder="Tu contraseña"
                            required
                            autocomplete="current-password"
                            class="mb-2"
                        >
                            <template #append-inner>
                                <v-btn
                                    icon
                                    variant="text"
                                    size="small"
                                    @click="togglePasswordVisibility"
                                    :aria-pressed="showPassword"
                                >
                                    <v-icon>
                                        {{ showPassword ? 'mdi-eye-off' : 'mdi-eye' }}
                                    </v-icon>
                                    <span class="visually-hidden">
                                        {{ showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña' }}
                                    </span>
                                </v-btn>
                            </template>
                        </v-text-field>

                        <v-checkbox
                            v-model="form.remember"
                            label="Recordarme en este dispositivo"
                            class="mb-4"
                            hide-details
                        />

                        <v-btn
                            type="submit"
                            color="primary"
                            block
                            size="large"
                            :loading="loading"
                            :disabled="loading"
                        >
                            Iniciar sesión
                        </v-btn>
                    </v-form>
                </v-container>
            </v-col>

            <v-col
                cols="12"
                lg="6"
                xl="8"
                class="d-none d-lg-block"
            >
                <div
                    class="h-100 min-vh-100"
                    style="background-image: url('https://images.alphacoders.com/134/thumb-1920-1340325.png'); background-size: cover; background-position: center;"
                    role="img"
                    aria-label="Background illustration"
                />
            </v-col>
        </v-row>
    </div>
</template>

<style scoped>
.skip-link {
    position: absolute;
    top: -40px;
    left: 0;
    background: #000;
    color: #fff;
    padding: 8px;
    text-decoration: none;
    z-index: 100;
}

.skip-link:focus {
    top: 0;
}
</style>
