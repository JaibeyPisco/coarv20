<script setup lang="ts">
import { ref } from 'vue';

interface Apoderado {
    id?: number | null;
    apellidos: string;
    nombres: string;
    dni: string;
    numero_telefonico: string;
    grado_parentesco: string;
    legalizado: boolean;
}

interface Props {
    modelValue: Apoderado[];
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:modelValue': [value: Apoderado[]];
}>();

function addApoderado() {
    const newApoderados = [...props.modelValue];
    newApoderados.push({
        id: null,
        apellidos: '',
        nombres: '',
        dni: '',
        numero_telefonico: '',
        grado_parentesco: '',
        legalizado: false,
    });
    emit('update:modelValue', newApoderados);
}

function removeApoderado(index: number) {
    const newApoderados = [...props.modelValue];
    newApoderados.splice(index, 1);
    emit('update:modelValue', newApoderados);
}

function updateApoderado(index: number, field: keyof Apoderado, value: string | boolean) {
    const newApoderados = [...props.modelValue];
    const current = newApoderados[index];
    if (!current) return;
    
    // Actualizar solo el campo específico
    newApoderados[index] = {
        ...current,
        [field]: value,
    };
    emit('update:modelValue', newApoderados);
}
</script>

<template>
    <v-container fluid class="pa-4">
        <v-card rounded="lg" elevation="1" class="border-primary border-opacity-75" style="border-top-width: 3px !important;">
            <v-card-title class="text-h6 font-weight-bold pa-4 pb-2">
                Datos del Apoderado
            </v-card-title>
            <v-card-text class="pa-4">
                <div v-if="modelValue.length === 0" class="text-center py-8">
                    <v-icon icon="mdi-account-plus" size="64" color="grey-lighten-1" class="mb-4" />
                    <p class="text-body-1 text-medium-emphasis mb-4">
                        No hay apoderados registrados. Haz clic en el botón para agregar uno.
                    </p>
                    <v-btn
                        color="primary"
                        variant="flat"
                        prepend-icon="mdi-plus"
                        @click="addApoderado"
                        class="text-none"
                    >
                        Agregar Apoderado
                    </v-btn>
                </div>

                <div v-else>
                    <v-card
                        v-for="(apoderado, index) in modelValue"
                        :key="index"
                        class="mb-4"
                        variant="outlined"
                        rounded="lg"
                    >
                        <v-card-text class="pa-4">
                            <div class="d-flex justify-space-between align-center mb-3">
                                <h6 class="text-subtitle-1 font-weight-bold">
                                    Apoderado {{ index + 1 }}
                                </h6>
                                <v-btn
                                    icon="mdi-delete"
                                    size="small"
                                    color="error"
                                    variant="text"
                                    @click="removeApoderado(index)"
                                />
                            </div>

                            <v-row>
                                <v-col cols="12" md="6">
                                    <v-text-field
                                        :model-value="apoderado.apellidos"
                                        label="Apellidos"
                                        variant="outlined"
                                        density="compact"
                                        @update:model-value="updateApoderado(index, 'apellidos', $event)"
                                    />
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-text-field
                                        :model-value="apoderado.nombres"
                                        label="Nombres"
                                        variant="outlined"
                                        density="compact"
                                        @update:model-value="updateApoderado(index, 'nombres', $event)"
                                    />
                                </v-col>
                                <v-col cols="12" md="4">
                                    <v-text-field
                                        :model-value="apoderado.dni"
                                        label="DNI"
                                        variant="outlined"
                                        density="compact"
                                        type="number"
                                        @update:model-value="updateApoderado(index, 'dni', $event)"
                                    />
                                </v-col>
                                <v-col cols="12" md="4">
                                    <v-text-field
                                        :model-value="apoderado.numero_telefonico"
                                        label="Número Telefónico"
                                        variant="outlined"
                                        density="compact"
                                        type="tel"
                                        @update:model-value="updateApoderado(index, 'numero_telefonico', $event)"
                                    />
                                </v-col>
                                <v-col cols="12" md="4">
                                    <v-text-field
                                        :model-value="apoderado.grado_parentesco"
                                        label="Grado de Parentesco"
                                        variant="outlined"
                                        density="compact"
                                        @update:model-value="updateApoderado(index, 'grado_parentesco', $event)"
                                    />
                                </v-col>
                                <v-col cols="12">
                                    <v-switch
                                        :model-value="apoderado.legalizado"
                                        label="¿Legalizado?"
                                        color="primary"
                                        hide-details
                                        @update:model-value="updateApoderado(index, 'legalizado', $event)"
                                    />
                                </v-col>
                            </v-row>
                        </v-card-text>
                    </v-card>

                    <div class="text-center mt-4">
                        <v-btn
                            color="primary"
                            variant="flat"
                            prepend-icon="mdi-plus"
                            @click="addApoderado"
                            class="text-none"
                        >
                            Agregar Apoderado
                        </v-btn>
                    </div>
                </div>
            </v-card-text>
        </v-card>
    </v-container>
</template>

<style scoped>
.border-primary {
    border-color: rgb(var(--v-theme-primary)) !important;
}
</style>
