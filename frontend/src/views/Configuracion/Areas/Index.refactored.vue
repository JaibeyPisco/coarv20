<script setup lang="ts">
import CrudView from '@/components/Crud/CrudView.vue';
import type { Area } from '@/types/configuracion';
import type { CreateAreaDto } from '@/types/configuracion';
import { formatStatusChip } from '@/utils/vuetifyTableHelpers';

const config = {
    entityName: 'área',
    title: 'Áreas',
    description: 'Gestiona las áreas de la organización; activa, edita o elimina según necesidad.',
    apiEndpoint: '/configuracion/areas',
    searchFields: ['nombre', 'descripcion'] as (keyof Area)[],
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
            key: 'descripcion',
            title: 'DESCRIPCIÓN',
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
            descripcion: '',
        },
        getPayload: (form): CreateAreaDto => ({
            nombre: String(form.nombre).trim(),
            descripcion: form.descripcion ? String(form.descripcion).trim() : null,
        }),
        validate: (form) => {
            if (!String(form.nombre).trim()) {
                return 'El nombre del área es obligatorio.';
            }
            return null;
        },
        populateForm: (item: Area, form: Record<string, unknown>) => {
            form.nombre = item.nombre;
            form.descripcion = item.descripcion ?? '';
        },
        resetForm: (form: Record<string, unknown>) => {
            form.nombre = '';
            form.descripcion = '';
        },
    },
};
</script>

<template>
    <CrudView :config="config">
        <template #form="{ form }">
            <v-container fluid class="pa-4">
                <v-form @submit.prevent>
                    <v-text-field
                        v-model="form.nombre"
                        label="Nombre del Área"
                        :rules="[(v) => !!v || 'El nombre es obligatorio']"
                        counter="100"
                        maxlength="100"
                        placeholder="Ingrese el nombre del área"
                        required
                        variant="outlined"
                        density="compact"
                        class="mb-4"
                    />
                    <v-textarea
                        v-model="form.descripcion"
                        label="Descripción"
                        counter="255"
                        maxlength="255"
                        rows="3"
                        placeholder="Ingrese una descripción (opcional)"
                        variant="outlined"
                        density="compact"
                    />
                </v-form>
            </v-container>
        </template>

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

        <template #item-descripcion="{ value }">
            <div class="text-body-2" style="max-width: 400px; word-wrap: break-word; overflow-wrap: break-word; white-space: normal; line-height: 1.4;">
                {{ value || '—' }}
            </div>
        </template>
    </CrudView>
</template>
