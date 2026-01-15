<script setup lang="ts">
interface PadreForm {
    id: number | null;
    vive: boolean;
    vive_con_estudiante: boolean;
    apellidos: string;
    nombres: string;
    dni: string;
    grado_instruccion: string;
    telefono: string;
    correo_electronico: string;
    ocupacion_actual: string;
    motivo_no_vive_con_estudiante: string;
}

interface ApoderadoRolPadreForm {
    id: number | null;
    parentesco_estudiante: string;
    apellidos: string;
    nombres: string;
    dni: string;
    telefono: string;
    tipo_familia: string;
}

interface Props {
    padreForm: PadreForm;
    madreForm: PadreForm;
    apoderadoRolPadreForm: ApoderadoRolPadreForm;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:padreForm': [value: PadreForm];
    'update:madreForm': [value: PadreForm];
    'update:apoderadoRolPadreForm': [value: ApoderadoRolPadreForm];
}>();

const gradoInstruccionOptions = [
    { title: 'Seleccione...', value: '' },
    { title: 'Sin Instrucción', value: 'SIN_INSTRUCCION' },
    { title: 'Primaria Incompleta', value: 'PRIMARIA_INCOMPLETA' },
    { title: 'Primaria Completa', value: 'PRIMARIA_COMPLETA' },
    { title: 'Secundaria Incompleta', value: 'SECUNDARIA_INCOMPLETA' },
    { title: 'Secundaria Completa', value: 'SECUNDARIA_COMPLETA' },
    { title: 'Técnico Incompleto', value: 'TECNICO_INCOMPLETO' },
    { title: 'Técnico Completo', value: 'TECNICO_COMPLETO' },
    { title: 'Superior No Universitaria Incompleta', value: 'SUPERIOR_NO_UNIVERSITARIA_INCOMPLETA' },
    { title: 'Superior No Universitaria Completa', value: 'SUPERIOR_NO_UNIVERSITARIA_COMPLETA' },
    { title: 'Universitaria Incompleta', value: 'UNIVERSITARIA_INCOMPLETA' },
    { title: 'Universitaria Completa', value: 'UNIVERSITARIA_COMPLETA' },
    { title: 'Postgrado (Maestría)', value: 'POSTGRADO_MAESTRIA' },
    { title: 'Postgrado (Doctorado)', value: 'POSTGRADO_DOCTORADO' },
];

const tipoFamiliaOptions = [
    'FAMILA MONOPARENTAL',
    'FAMILIA NUCLEAR',
    'FAMILIA RECONSTITUÍDA',
    'FAMILIA EXTENSA',
];
</script>

<template>
    <v-container fluid class="pa-4">
        <v-row>
            <!-- DATOS DE LA MADRE -->
            <v-col cols="12" md="6">
                <v-card class="mb-4" rounded="lg" elevation="1" style="border-top: 3px solid rgb(var(--v-theme-primary)) !important;">
                    <v-card-title class="text-h6 font-weight-bold pa-4 pb-2">
                        <v-icon class="mr-2">mdi-account-heart</v-icon>
                        Datos de la Madre
                    </v-card-title>
                    <v-card-text class="pa-4">
                        <!-- Estado y convivencia -->
                        <v-row class="mb-2">
                            <v-col cols="12" md="6">
                                <v-switch
                                    :model-value="madreForm.vive"
                                    label="¿La madre está viva?"
                                    color="primary"
                                    hide-details
                                    @update:model-value="emit('update:madreForm', { ...madreForm, vive: $event })"
                                />
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-switch
                                    :model-value="madreForm.vive_con_estudiante"
                                    label="¿Vive con el estudiante?"
                                    color="primary"
                                    hide-details
                                    @update:model-value="emit('update:madreForm', { ...madreForm, vive_con_estudiante: $event })"
                                />
                            </v-col>
                        </v-row>

                        <v-divider class="my-4" />

                        <!-- Información Personal -->
                        <div class="text-subtitle-2 font-weight-medium mb-3 text-primary">Información Personal</div>
                        <v-row>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    :model-value="madreForm.apellidos"
                                    label="Apellidos"
                                    variant="outlined"
                                    density="compact"
                                    @update:model-value="emit('update:madreForm', { ...madreForm, apellidos: $event })"
                                />
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    :model-value="madreForm.nombres"
                                    label="Nombres"
                                    variant="outlined"
                                    density="compact"
                                    @update:model-value="emit('update:madreForm', { ...madreForm, nombres: $event })"
                                />
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    :model-value="madreForm.dni"
                                    label="N° de DNI"
                                    variant="outlined"
                                    density="compact"
                                    type="number"
                                    @update:model-value="emit('update:madreForm', { ...madreForm, dni: $event })"
                                />
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-select
                                    :model-value="madreForm.grado_instruccion"
                                    label="Grado de Instrucción"
                                    variant="outlined"
                                    density="compact"
                                    :items="gradoInstruccionOptions"
                                    @update:model-value="emit('update:madreForm', { ...madreForm, grado_instruccion: $event })"
                                />
                            </v-col>
                        </v-row>

                        <v-divider class="my-4" />

                        <!-- Información de Contacto -->
                        <div class="text-subtitle-2 font-weight-medium mb-3 text-primary">Información de Contacto</div>
                        <v-row>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    :model-value="madreForm.telefono"
                                    label="N° de Teléfono Celular"
                                    variant="outlined"
                                    density="compact"
                                    type="tel"
                                    @update:model-value="emit('update:madreForm', { ...madreForm, telefono: $event })"
                                />
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    :model-value="madreForm.correo_electronico"
                                    label="Correo Electrónico"
                                    variant="outlined"
                                    density="compact"
                                    type="email"
                                    @update:model-value="emit('update:madreForm', { ...madreForm, correo_electronico: $event })"
                                />
                            </v-col>
                        </v-row>

                        <v-divider class="my-4" />

                        <!-- Información Profesional -->
                        <div class="text-subtitle-2 font-weight-medium mb-3 text-primary">Información Profesional</div>
                        <v-row>
                            <v-col cols="12">
                                <v-text-field
                                    :model-value="madreForm.ocupacion_actual"
                                    label="Ocupación Actual"
                                    variant="outlined"
                                    density="compact"
                                    @update:model-value="emit('update:madreForm', { ...madreForm, ocupacion_actual: $event })"
                                />
                            </v-col>
                            <v-col cols="12">
                                <v-textarea
                                    :model-value="madreForm.motivo_no_vive_con_estudiante"
                                    label="Motivo por el cual no vive con el estudiante"
                                    variant="outlined"
                                    density="compact"
                                    rows="2"
                                    :disabled="madreForm.vive_con_estudiante"
                                    @update:model-value="emit('update:madreForm', { ...madreForm, motivo_no_vive_con_estudiante: $event })"
                                />
                            </v-col>
                        </v-row>
                    </v-card-text>
                </v-card>
            </v-col>

            <!-- DATOS DEL PADRE -->
            <v-col cols="12" md="6">
                <v-card class="mb-4" rounded="lg" elevation="1" style="border-top: 3px solid rgb(var(--v-theme-primary)) !important;">
                    <v-card-title class="text-h6 font-weight-bold pa-4 pb-2">
                        <v-icon class="mr-2">mdi-account</v-icon>
                        Datos del Padre
                    </v-card-title>
                    <v-card-text class="pa-4">
                        <!-- Estado y convivencia -->
                        <v-row class="mb-2">
                            <v-col cols="12" md="6">
                                <v-switch
                                    :model-value="padreForm.vive"
                                    label="¿El padre está vivo?"
                                    color="primary"
                                    hide-details
                                    @update:model-value="emit('update:padreForm', { ...padreForm, vive: $event })"
                                />
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-switch
                                    :model-value="padreForm.vive_con_estudiante"
                                    label="¿Vive con el estudiante?"
                                    color="primary"
                                    hide-details
                                    @update:model-value="emit('update:padreForm', { ...padreForm, vive_con_estudiante: $event })"
                                />
                            </v-col>
                        </v-row>

                        <v-divider class="my-4" />

                        <!-- Información Personal -->
                        <div class="text-subtitle-2 font-weight-medium mb-3 text-primary">Información Personal</div>
                        <v-row>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    :model-value="padreForm.apellidos"
                                    label="Apellidos"
                                    variant="outlined"
                                    density="compact"
                                    @update:model-value="emit('update:padreForm', { ...padreForm, apellidos: $event })"
                                />
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    :model-value="padreForm.nombres"
                                    label="Nombres"
                                    variant="outlined"
                                    density="compact"
                                    @update:model-value="emit('update:padreForm', { ...padreForm, nombres: $event })"
                                />
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    :model-value="padreForm.dni"
                                    label="N° de DNI"
                                    variant="outlined"
                                    density="compact"
                                    type="number"
                                    @update:model-value="emit('update:padreForm', { ...padreForm, dni: $event })"
                                />
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-select
                                    :model-value="padreForm.grado_instruccion"
                                    label="Grado de Instrucción"
                                    variant="outlined"
                                    density="compact"
                                    :items="gradoInstruccionOptions"
                                    @update:model-value="emit('update:padreForm', { ...padreForm, grado_instruccion: $event })"
                                />
                            </v-col>
                        </v-row>

                        <v-divider class="my-4" />

                        <!-- Información de Contacto -->
                        <div class="text-subtitle-2 font-weight-medium mb-3 text-primary">Información de Contacto</div>
                        <v-row>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    :model-value="padreForm.telefono"
                                    label="N° de Teléfono Celular"
                                    variant="outlined"
                                    density="compact"
                                    type="tel"
                                    @update:model-value="emit('update:padreForm', { ...padreForm, telefono: $event })"
                                />
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    :model-value="padreForm.correo_electronico"
                                    label="Correo Electrónico"
                                    variant="outlined"
                                    density="compact"
                                    type="email"
                                    @update:model-value="emit('update:padreForm', { ...padreForm, correo_electronico: $event })"
                                />
                            </v-col>
                        </v-row>

                        <v-divider class="my-4" />

                        <!-- Información Profesional -->
                        <div class="text-subtitle-2 font-weight-medium mb-3 text-primary">Información Profesional</div>
                        <v-row>
                            <v-col cols="12">
                                <v-text-field
                                    :model-value="padreForm.ocupacion_actual"
                                    label="Ocupación Actual"
                                    variant="outlined"
                                    density="compact"
                                    @update:model-value="emit('update:padreForm', { ...padreForm, ocupacion_actual: $event })"
                                />
                            </v-col>
                            <v-col cols="12">
                                <v-textarea
                                    :model-value="padreForm.motivo_no_vive_con_estudiante"
                                    label="Motivo por el cual no vive con el estudiante"
                                    variant="outlined"
                                    density="compact"
                                    rows="2"
                                    :disabled="padreForm.vive_con_estudiante"
                                    @update:model-value="emit('update:padreForm', { ...padreForm, motivo_no_vive_con_estudiante: $event })"
                                />
                            </v-col>
                        </v-row>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <!-- DATOS DEL APODERADO QUE CUMPLE EL ROL DE PADRE O MADRE -->
        <v-row>
            <v-col cols="12">
                <v-card class="mb-4" rounded="lg" elevation="1" style="border-top: 3px solid rgb(var(--v-theme-primary)) !important;">
                    <v-card-title class="text-h6 font-weight-bold pa-4 pb-2">
                        <v-icon class="mr-2">mdi-account-supervisor-circle</v-icon>
                        Datos del Apoderado que cumple el rol de Padre o Madre
                    </v-card-title>
                    <v-card-text class="pa-4">
                        <v-row>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    :model-value="apoderadoRolPadreForm.parentesco_estudiante"
                                    label="Parentesco con el estudiante"
                                    variant="outlined"
                                    density="compact"
                                    @update:model-value="emit('update:apoderadoRolPadreForm', { ...apoderadoRolPadreForm, parentesco_estudiante: $event })"
                                />
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-autocomplete
                                    :model-value="apoderadoRolPadreForm.tipo_familia"
                                    label="Tipo de Familia"
                                    variant="outlined"
                                    density="compact"
                                    :items="tipoFamiliaOptions"
                                    @update:model-value="emit('update:apoderadoRolPadreForm', { ...apoderadoRolPadreForm, tipo_familia: $event })"
                                />
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    :model-value="apoderadoRolPadreForm.apellidos"
                                    label="Apellidos"
                                    variant="outlined"
                                    density="compact"
                                    @update:model-value="emit('update:apoderadoRolPadreForm', { ...apoderadoRolPadreForm, apellidos: $event })"
                                />
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    :model-value="apoderadoRolPadreForm.nombres"
                                    label="Nombres"
                                    variant="outlined"
                                    density="compact"
                                    @update:model-value="emit('update:apoderadoRolPadreForm', { ...apoderadoRolPadreForm, nombres: $event })"
                                />
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    :model-value="apoderadoRolPadreForm.dni"
                                    label="N° de DNI"
                                    variant="outlined"
                                    density="compact"
                                    type="number"
                                    @update:model-value="emit('update:apoderadoRolPadreForm', { ...apoderadoRolPadreForm, dni: $event })"
                                />
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    :model-value="apoderadoRolPadreForm.telefono"
                                    label="N° de Teléfono Celular"
                                    variant="outlined"
                                    density="compact"
                                    type="tel"
                                    @update:model-value="emit('update:apoderadoRolPadreForm', { ...apoderadoRolPadreForm, telefono: $event })"
                                />
                            </v-col>
                        </v-row>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>
