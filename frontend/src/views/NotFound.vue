<script setup lang="ts">
import { RouterLink } from 'vue-router';
import AuthenticatedLayout from '@/components/Layouts/AuthenticatedLayout.vue';
</script>

<template>
    <AuthenticatedLayout>
        <v-container class="fill-height d-flex align-center justify-center">
            <v-row justify="center">
                <v-col cols="12" sm="8" md="6" lg="4">
                    <div class="text-center">
                        <h1 class="text-h1 font-weight-bold mb-4">404</h1>
                        <h2 class="text-h4 mb-2">Página no encontrada</h2>
                        <p class="text-body-1 text-medium-emphasis mb-6">
                            La página que estás buscando no existe.
                        </p>
                        <v-btn
                            color="primary"
                            size="large"
                            prepend-icon="mdi-arrow-left"
                            :to="{ name: 'dashboard' }"
                        >
                            Volver al inicio
                        </v-btn>
                    </div>
                </v-col>
            </v-row>
        </v-container>
    </AuthenticatedLayout>
</template>
