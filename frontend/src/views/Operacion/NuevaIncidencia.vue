<script setup lang="ts">
import AuthenticatedLayout from '../../components/Layouts/AuthenticatedLayout.vue';
import { ref, reactive, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import apiClient from '@/api/axios';
import { CurrentDateTime } from '@/utils/HelperDates';
import type { Data, Lugar, Root, TipoIncidencia, Area } from '@/types/operacion/Incidencia';
import { notificacion } from '@/utils/notificacion';

const router = useRouter();

const initialData = ref<Data | null>(null);
const secuencia = ref('');
const currentDateTime = ref(CurrentDateTime());
const saving = ref(false);

const form = reactive({
    numero: '',
    fecha_hora: '',
    id_tipo_incidencia: '',
    id_lugar_incidencia: '',
    id_area: '',
    descripcion: '',
    estudiantes: [] as Array<{ id_estudiante: string }>,
});

const tipoIncidencias = ref<TipoIncidencia[]>([]);
const lugares = ref<Lugar[]>([]);
const areas = ref<Area[]>([]);

// Computed properties para mapear los items correctamente
const tipoIncidenciasItems = computed(() => {
    return tipoIncidencias.value.map(t => ({
        title: t.text || '',
        value: String(t.id)
    }));
});

const lugaresItems = computed(() => {
    return lugares.value.map(l => ({
        title: l.text || '',
        value: String(l.id)
    }));
});

const areasItems = computed(() => {
    return areas.value.map(a => ({
        title: a.text || '',
        value: String(a.id)
    }));
});

onMounted(async () => {
    try {
        const { data } = await apiClient.get<Root>('operacion/incidencia/getInitial');
        initialData.value = data.data;
        secuencia.value = initialData.value.secuencia;
        tipoIncidencias.value = initialData.value.tipoIncidencias || [];
        lugares.value = initialData.value.lugares || [];
        areas.value = initialData.value.areas || [];
        form.numero = secuencia.value;
        form.fecha_hora = currentDateTime.value;
    } catch (error: any) {
        console.error('Error cargando datos iniciales:', error);
        notificacion('Error al cargar los datos iniciales.', { type: 'error', title: 'Error' });
    }
});

function agregarEstudiante() {
    form.estudiantes.push({ id_estudiante: '' });
}

function quitarEstudiante(index: number) {
    form.estudiantes.splice(index, 1);
}

async function handleSubmit() {
    if (!form.id_tipo_incidencia) {
        notificacion('El tipo de incidencia es obligatorio.', { type: 'error', title: 'Validación' });
        return;
    }

    if (!form.id_lugar_incidencia) {
        notificacion('El lugar de la incidencia es obligatorio.', { type: 'error', title: 'Validación' });
        return;
    }

    if (!form.id_area) {
        notificacion('El área a reportar es obligatoria.', { type: 'error', title: 'Validación' });
        return;
    }

    if (!form.descripcion.trim()) {
        notificacion('La descripción de la incidencia es obligatoria.', { type: 'error', title: 'Validación' });
        return;
    }

    saving.value = true;

    try {
        const formData = new FormData();
        formData.append('numero', form.numero);
        formData.append('fecha_hora', form.fecha_hora);
        formData.append('id_tipo_incidencia', form.id_tipo_incidencia);
        formData.append('id_lugar_incidencia', form.id_lugar_incidencia);
        formData.append('id_area', form.id_area);
        formData.append('descripcion', form.descripcion);
        
        form.estudiantes.forEach((estudiante, index) => {
            if (estudiante.id_estudiante) {
                formData.append(`estudiantes[${index}][id_estudiante]`, estudiante.id_estudiante);
            }
        });

        await apiClient.post('/operacion/incidencia/save', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        notificacion('Incidencia registrada correctamente.', { type: 'success' });
        
        // Reset form
        form.numero = secuencia.value;
        form.fecha_hora = currentDateTime.value;
        form.id_tipo_incidencia = '';
        form.id_lugar_incidencia = '';
        form.id_area = '';
        form.descripcion = '';
        form.estudiantes = [];
    } catch (error: any) {
        const message = error.response?.data?.message || 'Ocurrió un inconveniente al guardar la incidencia.';
        notificacion(message, { type: 'error', title: 'Error' });
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <v-container fluid class="pa-4">
            <v-card rounded="xl" elevation="2" class="pa-6">
                <!-- Título y subtítulo -->
                <div class="mb-4">
                    <h1 class="text-h5 font-weight-medium mb-2">Nueva Incidencia</h1>
                    <p class="text-body-2 text-medium-emphasis mb-0">
                        Registra una nueva incidencia en el sistema. Completa todos los campos requeridos para proceder.
                    </p>
                    <v-divider class="mt-4" />
                </div>

                <v-form @submit.prevent="handleSubmit">
                    <!-- Información básica -->
                    <v-row class="mb-2">
                        <v-col cols="12" md="3">
                            <v-text-field
                                v-model="form.numero"
                                label="N° Incidencia"
                                readonly
                                density="compact"
                                variant="outlined"
                                prepend-inner-icon="mdi-numeric"
                            />
                        </v-col>
                        <v-col cols="12" md="3">
                            <v-text-field
                                v-model="form.fecha_hora"
                                label="Fecha y hora"
                                type="datetime-local"
                                required
                                density="compact"
                                variant="outlined"
                                prepend-inner-icon="mdi-calendar-clock"
                            />
                        </v-col>
                        <v-col cols="12" md="3">
                            <v-select
                                v-model="form.id_tipo_incidencia"
                                label="Tipo de Incidencia"
                                :items="tipoIncidenciasItems"
                                required
                                density="compact"
                                variant="outlined"
                                clearable
                                prepend-inner-icon="mdi-alert-circle"
                                item-title="title"
                                item-value="value"
                            />
                        </v-col>
                        <v-col cols="12" md="3">
                            <v-select
                                v-model="form.id_lugar_incidencia"
                                label="Lugar de la Incidencia"
                                :items="lugaresItems"
                                required
                                density="compact"
                                variant="outlined"
                                clearable
                                prepend-inner-icon="mdi-map-marker"
                                item-title="title"
                                item-value="value"
                            />
                        </v-col>
                    </v-row>

                    <v-row class="mb-4">
                        <v-col cols="12" md="6">
                            <v-select
                                v-model="form.id_area"
                                label="Área a Reportar"
                                :items="areasItems"
                                density="compact"
                                variant="outlined"
                                clearable
                                prepend-inner-icon="mdi-office-building"
                                item-title="title"
                                item-value="value"
                            />
                        </v-col>
                    </v-row>

                    <!-- Descripción -->
                    <v-sheet class="pa-4 mb-4 rounded-lg elevation-1">
                        <div class="text-subtitle-1 font-weight-medium mb-3">
                            <v-icon size="small" class="mr-2">mdi-text-box</v-icon>
                            Descripción de la Incidencia
                        </div>
                        <v-textarea
                            v-model="form.descripcion"
                            label="Describe detalladamente la incidencia"
                            required
                            rows="5"
                            density="compact"
                            variant="outlined"
                            counter
                            auto-grow
                        />
                        <div class="text-caption text-medium-emphasis mt-2">
                            <v-icon size="x-small" class="mr-1">mdi-information</v-icon>
                            Proporciona todos los detalles relevantes para una mejor comprensión del evento.
                        </div>
                    </v-sheet>

                    <!-- Estudiantes involucrados -->
                    <v-sheet class="pa-4 mb-4 rounded-lg elevation-1">
                        <div class="text-subtitle-1 font-weight-medium mb-3">
                            <v-icon size="small" class="mr-2">mdi-account-group</v-icon>
                            Estudiantes Involucrados
                        </div>
                        
                        <div v-if="form.estudiantes.length === 0" class="text-center py-6">
                            <v-icon icon="mdi-account-plus" size="48" color="grey-lighten-1" class="mb-2" />
                            <p class="text-body-2 text-medium-emphasis mb-4">
                                No hay estudiantes agregados. Haz clic en el botón para agregar uno.
                            </p>
                        </div>

                        <div v-for="(estudiante, index) in form.estudiantes" :key="index" class="mb-3">
                            <v-card variant="outlined" class="pa-3 rounded-lg">
                                <v-row align="center">
                                    <v-col cols="12" md="10">
                                        <v-select
                                            v-model="estudiante.id_estudiante"
                                            label="Estudiante"
                                            :items="[]"
                                            density="compact"
                                            variant="outlined"
                                            prepend-inner-icon="mdi-account"
                                            item-title="title"
                                            item-value="value"
                                        />
                                    </v-col>
                                    <v-col cols="12" md="2" class="text-center">
                                        <v-btn
                                            color="error"
                                            icon
                                            variant="text"
                                            size="default"
                                            @click="quitarEstudiante(index)"
                                            aria-label="Eliminar estudiante"
                                        >
                                            <v-icon>mdi-delete</v-icon>
                                        </v-btn>
                                    </v-col>
                                </v-row>
                            </v-card>
                        </div>

                        <v-btn
                            color="success"
                            prepend-icon="mdi-plus"
                            variant="flat"
                            size="default"
                            @click="agregarEstudiante"
                            class="text-none"
                        >
                            Agregar Estudiante
                        </v-btn>
                    </v-sheet>

                    <!-- Fotos de la incidencia -->
                    <v-sheet class="pa-4 mb-4 rounded-lg elevation-1">
                        <div class="text-subtitle-1 font-weight-medium mb-3">
                            <v-icon size="small" class="mr-2">mdi-camera</v-icon>
                            Fotos de la Incidencia
                        </div>
                        <div class="text-center py-6">
                            <v-icon icon="mdi-image-plus" size="48" color="grey-lighten-1" class="mb-2" />
                            <p class="text-body-2 text-medium-emphasis">
                                El componente de carga de fotos se puede integrar aquí.
                            </p>
                        </div>
                    </v-sheet>

                    <!-- Divider antes del footer -->
                    <v-divider class="mb-4" />

                    <!-- Botones de acción -->
                    <div class="d-flex justify-end ga-2">
                        <v-btn
                            type="button"
                            variant="text"
                            size="default"
                            @click="router.back()"
                            :disabled="saving"
                            class="text-none"
                        >
                            <v-icon start>mdi-close</v-icon>
                            Cancelar
                        </v-btn>
                        <v-btn
                            type="submit"
                            color="primary"
                            variant="flat"
                            size="default"
                            :loading="saving"
                            :disabled="saving"
                            prepend-icon="mdi-content-save"
                            class="text-none"
                        >
                            Guardar Incidencia
                        </v-btn>
                    </div>
                </v-form>
            </v-card>
        </v-container>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Estilos para la vista de nueva incidencia */
</style>
