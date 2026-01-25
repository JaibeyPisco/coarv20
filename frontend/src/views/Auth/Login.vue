<script setup lang="ts">
import { ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { notificacion } from '../../utils/notificacion';
import ApplicationLogo from '../../components/ApplicationLogo.vue';

 

import Carousel from '@/components/Auth/Carousel.vue';
import Slide from '@/components/Auth/Slide.vue';


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

const CarouselSlides = [
    'bg-1.jpg',
    'bg-2.jpg',
    'bg-3.jpg',

];

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
        notificacion(errorMessage.value, { type: 'danger', title: 'Error' });
    } finally {
        loading.value = false;
    }
};

const getImageUrl = (name:string) => {
  // Asegúrate de que la ruta relativa sea correcta desde tu archivo actual
  return new URL(`../../assets/${name}`, import.meta.url).href;
};
</script>

<template>
    <v-app>
        <v-main class="pa-0">
            <v-container fluid class="fill-height pa-0">
                <v-row no-gutters class="fill-height">
                    <v-col
                        cols="12"
                        lg="3"
                        xl="3"
                        class="d-flex flex-column justify-center bg-white border-top-wide border-primary"
                    >
                        <v-container class="px-7 px-sm-12 px-lg-10" style="max-width: 500px;">
                            <div class="text-center mb-6">
                                <router-link to="/" aria-label="Home" class="text-decoration-none">
                                    <ApplicationLogo class="mx-auto" style="width: 80px; height: 80px;" />
                                </router-link>
                            </div>

                            <h2 id="main-login" class="text-h4 text-center mb-6 font-weight-bold">
                                Inicia sesión en tu cuenta
                            </h2>
 

                            <v-form @submit.prevent="handleSubmit">
                                <v-text-field
                                    v-model="form.email"
                                    label="Correo electrónico"
                                    type="email"
                                    variant="outlined"
                                    placeholder="correo@ejemplo.com"
                                    :error="!!errorMessage"
                                    required
                                    autocomplete="username"
                                    autofocus
                                    class="mb-3"
                                    color="primary"
                                   base-color="grey-lighten-1"
                                    bg-color="white"
                                />

                                <v-text-field
                                    v-model="form.password"
                                    label="Contraseña"
                                    variant="outlined"
                                    :type="showPassword ? 'text' : 'password'"
                                    :error="!!errorMessage"
                                    placeholder="Tu contraseña"
                                    required
                                    autocomplete="current-password"
                                    class="mb-2"
                                >
                                    <template #append-inner>
                                        <v-icon 
                                            class="cursor-pointer"
                                            @click="togglePasswordVisibility"
                                        >
                                            {{ showPassword ? 'mdi-eye-off' : 'mdi-eye' }}
                                        </v-icon>
                                    </template>
                                </v-text-field>

                                <v-checkbox
                                    v-model="form.remember"
                                    label="Recordarme en este dispositivo"
                                    class="mb-4"
                                    hide-details
                                    color="primary"
                                />

                                <v-btn
                                    type="submit"
                                    color="primary"
                                    block
                                    size="x-large"
                                    :loading="loading"
                                    :disabled="loading"
                                    elevation="2"
                                >
                                    Iniciar sesión
                                </v-btn>
                            </v-form>
                        </v-container>
                    </v-col>

                    <v-col
                        cols="12"
                        lg="9"
                        xl="9"
                        class="d-none d-lg-block"
                    >
                    <!-- <v-carousel  class="fill-height">
                        <v-carousel-item
                            src="https://cdn.vuetifyjs.com/images/cards/docks.jpg"
                            cover
                        ></v-carousel-item>

                        <v-carousel-item
                            src="https://cdn.vuetifyjs.com/images/cards/hotel.jpg"
                            cover
                        ></v-carousel-item>

                        <v-carousel-item
                            src="https://cdn.vuetifyjs.com/images/cards/sunshine.jpg"
                            cover
                ></v-carousel-item>
                </v-carousel> -->
                        <!-- <v-img
                            src="https://images.alphacoders.com/134/thumb-1920-1340325.png"
                            cover
                            class="fill-height"
                            alt="Background illustration"
                        >
                            <div class="fill-height" style="background: rgba(0,0,0,0.1);"></div>
                        </v-img> -->

                        <Carousel class="fill-height" v-slot="{currentSlide}">
                            <Slide v-for="(slide, index) in CarouselSlides" >
                                    <div class="slide-info" v-show="currentSlide === index + 1">

                                            <v-img  
                                            :src="getImageUrl(slide)"
                                                cover
                                                class="fill-height image"
                                                alt="Background illustration"
                                            >
                                                <div class="fill-height" style="background: rgba(0,0,0,0.1);"></div>
                                            </v-img>
                                    </div>

                            </Slide>
                        </Carousel>
                    </v-col>
                </v-row>
            </v-container>
        </v-main>
    </v-app>
</template>

<style scoped>
/* Asegura que el contenedor de Vuetify no tenga límites de altura */
.fill-height {
    height: 100vh !important;
}

.cursor-pointer {
    cursor: pointer;
}
.courousel{
    position: relative;
}

.slide-info{
    position: absolute;
    width: 100%;
   
}

/* .image{
    min-width: ;
}
  */
</style>