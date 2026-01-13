<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex align-center justify-space-between flex-wrap ga-3">
                <div>
                    <div class="text-caption text-medium-emphasis mb-1">Bienvenido</div>
                    <h1 class="text-h4 font-weight-bold">Dashboard</h1>
                </div>
            </div>
        </template>

        <v-row>
            <v-col cols="12">
                <v-card elevation="1">
                    <v-card-title>Sistema de Gestión COAR</v-card-title>
                    <v-card-text>
                        <p class="text-medium-emphasis mb-0">
                            Bienvenido al sistema de gestión COAR
                        </p>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from '../components/Layouts/AuthenticatedLayout.vue';
</script>
