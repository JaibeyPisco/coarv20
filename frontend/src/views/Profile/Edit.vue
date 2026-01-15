<script setup lang="ts">
import AuthenticatedLayout from '@/components/Layouts/AuthenticatedLayout.vue';
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <v-container fluid class="py-0">
                <v-row align="center">
                    <v-col>
                        <h1 class="text-h5 mb-1">Perfil</h1>
                        <p class="text-subtitle-1 text-medium-emphasis mb-0">
                            Gestiona tu información personal
                        </p>
                    </v-col>
                </v-row>
            </v-container>
        </template>

        <v-row>
            <v-col cols="12">
                <v-card>
                    <v-card-text>
                        <p class="text-body-1">Página de perfil - En desarrollo</p>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </AuthenticatedLayout>
</template>
