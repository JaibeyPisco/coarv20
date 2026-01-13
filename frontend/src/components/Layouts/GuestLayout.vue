<script setup lang="ts">
import ApplicationLogo from '../ApplicationLogo.vue';
import { RouterLink } from 'vue-router';
</script>

<template>
    <v-container fluid class="fill-height">
        <v-row align="center" justify="center" class="fill-height">
            <v-col cols="12" sm="8" md="6" lg="4">
                <div class="text-center mb-6">
                    <RouterLink to="/" class="text-decoration-none">
                        <ApplicationLogo
                            class="mx-auto"
                            style="width: 80px; height: 80px"
                        />
                    </RouterLink>
                </div>

                <v-card elevation="2">
                    <v-card-text class="pa-6">
                        <slot />
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>
