<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="page-pretitle">Bienvenido</div>
                        <h2 class="page-title">Dashboard</h2>
                    </div>
                </div>
            </div>
        </template>

        <div class="row row-cards">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">Sistema de Gestión COAR</h3>
                        <p class="text-secondary">Bienvenido al sistema de gestión COAR</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from '../components/Layouts/AuthenticatedLayout.vue';
</script>


