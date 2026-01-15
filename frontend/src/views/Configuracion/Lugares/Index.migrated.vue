<script setup lang="ts">
import CrudView from '@/components/Crud/CrudView.vue';
import type { Lugar } from '@/types/configuracion';
import { formatStatusChip } from '@/utils/vuetifyTableHelpers';

const config = {
    entityName: 'lugar',
    title: 'Lugares',
    description: 'Administra los lugares disponibles; crea, edita o elimina según necesidad.',
    apiEndpoint: '/configuracion/lugares',
    searchFields: ['nombre', 'referencia'] as (keyof Lugar)[],
    columns: [
        {
            key: 'actions',
            title: 'ACCIONES',
            sortable: false,
            width: '150px',
        },
        {
            key: 'nombre',
            title: 'NOMBRE',
            sortable: true,
        },
        {
            key: 'referencia',
            title: 'REFERENCIA',
            sortable: true,
        },
        {
            key: 'estado',
            title: 'ESTADO',
            sortable: true,
            align: 'center' as const,
            width: '120px',
        },
    ],
    formConfig: {
        initialValues: {
            nombre: '',
            referencia: '',
        },
        getPayload: (form) => ({
            nombre: String(form.nombre).trim(),
            referencia: form.referencia ? String(form.referencia).trim() : null,
        }),
        validate: (form) => {
            if (!String(form.nombre).trim()) {
                return 'El nombre del lugar es obligatorio.';
            }
            return null;
        },
        populateForm: (item: Lugar, form: Record<string, unknown>) => {
            form.nombre = item.nombre;
            form.referencia = item.referencia ?? '';
        },
        resetForm: (form: Record<string, unknown>) => {
            form.nombre = '';
            form.referencia = '';
        },
    },
};
</script>

<template>
    <CrudView :config="config">
        <!-- Formulario personalizado -->
        <template #form="{ form }">
            <v-container fluid class="pa-4">
                <v-form @submit.prevent>
                    <v-text-field
                        v-model="form.nombre"
                        label="Nombre del Lugar"
                        :rules="[(v) => !!v || 'El nombre es obligatorio']"
                        counter="100"
                        maxlength="100"
                        placeholder="Ingrese el nombre del lugar"
                        required
                        variant="outlined"
                        density="compact"
                        class="mb-4"
                    />
                    <v-text-field
                        v-model="form.referencia"
                        label="Referencia"
                        counter="100"
                        maxlength="100"
                        placeholder="Ingrese una referencia (opcional)"
                        variant="outlined"
                        density="compact"
                    />
                </v-form>
            </v-container>
        </template>

        <!-- Personalización de celdas -->
        <template #item-estado="{ item }">
            <v-chip
                :color="formatStatusChip(item.estado).color"
                size="small"
                variant="flat"
                class="text-uppercase font-weight-medium"
            >
                {{ formatStatusChip(item.estado).label }}
            </v-chip>
        </template>
    </CrudView>
</template>
