<template>
    <div id="content" class="d-flex flex-column bg-white min-vh-100">
        <a href="#main-login" class="visually-hidden skip-link">
            Skip to main content
        </a>

        <div class="row g-0 flex-fill flex-grow-1">
            <div
                class="col-12 col-lg-6 col-xl-4 border-top-wide border-primary d-flex flex-column justify-content-center"
            >
                <div class="container container-tight my-5 px-lg-5">
                    <div class="text-center mb-4">
                        <router-link to="/" aria-label="Home" class="navbar-brand navbar-brand-autodark">
                            <ApplicationLogo class="navbar-brand-image" />
                        </router-link>
                    </div>

                    <h2 id="main-login" class="h3 text-center mb-3">
                        Inicia sesión en tu cuenta
                    </h2>

                    <div v-if="status" class="alert alert-success" role="status">
                        {{ status }}
                    </div>

                    <div
                        v-if="errorMessage"
                        class="alert alert-danger"
                        role="alert"
                    >
                        <p class="mb-0">{{ errorMessage }}</p>
                    </div>

                    <form autocomplete="off" novalidate @submit.prevent="handleSubmit">
                        <div class="mb-3">
                            <label class="form-label" for="email">Correo electrónico</label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                :class="['form-control', errorMessage ? 'is-invalid' : '']"
                                placeholder="correo@ejemplo.com"
                                required
                                autocomplete="username"
                                autofocus
                            />
                        </div>

                        <div class="mb-2">
                            <label class="form-label" for="password">
                                Contraseña
                            </label>
                            <div class="input-group input-group-flat">
                                <input
                                    id="password"
                                    ref="passwordField"
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    :class="['form-control', errorMessage ? 'is-invalid' : '']"
                                    placeholder="Your password"
                                    required
                                    autocomplete="current-password"
                                />
                                <span class="input-group-text">
                                    <button
                                        type="button"
                                        class="btn btn-link p-0 text-decoration-none link-secondary"
                                        :aria-pressed="showPassword"
                                        @click="togglePasswordVisibility"
                                    >
                                        <span class="visually-hidden">
                                            {{ showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña' }}
                                        </span>
                                        <svg
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
                                            class="icon icon-1"
                                        >
                                            <path
                                                d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"
                                            />
                                            <path
                                                d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"
                                            />
                                        </svg>
                                    </button>
                                </span>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-check">
                                <input
                                    id="remember"
                                    v-model="form.remember"
                                    type="checkbox"
                                    class="form-check-input"
                                />
                                <span class="form-check-label">
                                    Recordarme en este dispositivo
                                </span>
                            </label>
                        </div>

                        <div class="form-footer">
                            <button
                                type="submit"
                                class="btn btn-primary w-100"
                                :disabled="loading"
                            >
                                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                Iniciar sesión
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-12 col-lg-6 col-xl-8 d-none d-lg-block">
                <div
                    class="bg-cover h-100 min-vh-100"
                    style="background-image: url('https://images.alphacoders.com/134/thumb-1920-1340325.png')"
                    role="img"
                    aria-label="Background illustration"
                ></div>
            </div>
        </div>
    </div>
</template>

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
const passwordField = ref<HTMLInputElement | null>(null);
const loading = ref(false);
const errorMessage = ref('');
const status = ref('');

// Verificar si hay mensaje de estado en la query
if (route.query.status) {
    status.value = route.query.status as string;
}

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
    if (passwordField.value) {
        passwordField.value.focus({ preventScroll: true });
    }
};

const handleSubmit = async () => {
    errorMessage.value = '';
    loading.value = true;

    try {
        await authStore.login(form.value.email, form.value.password, form.value.remember);
        
        notificacion('Sesión iniciada correctamente', { type: 'success', title: 'Éxito' });
        
        // Redirigir directamente al dashboard después del login exitoso
        // El router guard verificará la autenticación automáticamente
        await router.push({ name: 'dashboard' });
    } catch (error: any) {
        errorMessage.value = error.message || 'Error al iniciar sesión';
        notificacion(errorMessage.value, { type: 'danger', title: 'Error' });
    } finally {
        loading.value = false;
    }
};
</script>


