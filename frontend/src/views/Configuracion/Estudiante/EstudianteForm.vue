<script setup lang="ts">
interface Props {
    modelValue: {
        dni: string;
        nombres: string;
        apellidos: string;
        grado: string;
        seccion: string;
        sexo: string;
        correo_electronico: string;
        fecha_nacimiento: string;
        codigo_estudiante: string;
        condicion_estudiante: string;
        obsv: string;
        lugar_nacimiento: string;
        fecha_caducidad_dni: string;
        num_telefonico: string;
        religion: string;
        region_domicilio_actual: string;
        provincia_domicilio_actual: string;
        distrito_domicilio_actual: string;
        direccion_domicilio_actual: string;
        referencia_domicilio_actual: string;
        lav: string;
        llaves: string;
        pabellon: string;
        ala: string;
        banos: string;
        monitor_acompana: string;
        cama_ropero: string;
        duchas: string;
        urinarios: string;
    };
    fotoPreview: string;
    editingId: number | null;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:modelValue': [value: Props['modelValue']];
    'foto-change': [file: File];
    'generate-codigo': [];
}>();

function handleFotoChange(event: Event) {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        emit('foto-change', file);
    }
}

function handleInput(field: keyof Props['modelValue'], value: string) {
    emit('update:modelValue', { ...props.modelValue, [field]: value });
}
</script>

<template>
    <v-container fluid class="pa-4">
        <!-- Sección: Información Personal -->
        <v-card class="mb-4" rounded="lg" elevation="1">
            <v-card-title class="text-h6 font-weight-bold pa-4 pb-2">
                Información Personal
            </v-card-title>
            <v-card-text class="pa-4">
                <v-row>
                    <!-- Foto de Perfil -->
                    <v-col cols="12" md="3">
                        <div class="d-flex flex-column align-center">
                            <v-avatar size="150" class="mb-3" rounded="lg">
                                <v-img
                                    :src="fotoPreview || '/images/sin_imagen.jpg'"
                                    :alt="'Foto de ' + modelValue.nombres"
                                    cover
                                    @error="
                                        (e: Event) => {
                                            const img = e.target as HTMLImageElement;
                                            if (
                                                img.src &&
                                                !img.src.includes('data:') &&
                                                img.src !== 'data:image/svg+xml'
                                            ) {
                                                img.src =
                                                    'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'200\' height=\'200\'%3E%3Crect fill=\'%23ddd\' width=\'200\' height=\'200\'/%3E%3Ctext fill=\'%23999\' font-family=\'sans-serif\' font-size=\'14\' dy=\'10.5\' font-weight=\'bold\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\'%3ESin Imagen%3C/text%3E%3C/svg%3E';
                                            }
                                        }
                                    "
                                />
                            </v-avatar>
                            <v-btn
                                color="primary"
                                variant="outlined"
                                size="small"
                                prepend-icon="mdi-upload"
                                class="text-none"
                            >
                                Cambiar Foto
                                <input
                                    type="file"
                                    accept="image/*"
                                    style="position: absolute; opacity: 0; width: 100%; height: 100%; cursor: pointer;"
                                    @change="handleFotoChange"
                                />
                            </v-btn>
                        </div>
                    </v-col>

                    <!-- Campos principales -->
                    <v-col cols="12" md="9">
                        <v-row>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    :model-value="modelValue.apellidos"
                                    label="Apellidos"
                                    variant="outlined"
                                    density="compact"
                                    maxlength="200"
                                    required
                                    @update:model-value="handleInput('apellidos', $event)"
                                />
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    :model-value="modelValue.nombres"
                                    label="Nombres"
                                    variant="outlined"
                                    density="compact"
                                    maxlength="200"
                                    required
                                    @update:model-value="handleInput('nombres', $event)"
                                />
                            </v-col>
                            <v-col cols="12" md="4">
                                <v-text-field
                                    :model-value="modelValue.dni"
                                    label="DNI"
                                    variant="outlined"
                                    density="compact"
                                    maxlength="8"
                                    minlength="8"
                                    required
                                    @update:model-value="handleInput('dni', $event)"
                                />
                            </v-col>
                            <v-col cols="12" md="4">
                                <v-select
                                    :model-value="modelValue.sexo"
                                    label="Sexo"
                                    variant="outlined"
                                    density="compact"
                                    :items="[
                                        { title: 'MASCULINO', value: 'MASCULINO' },
                                        { title: 'FEMENINO', value: 'FEMENINO' },
                                    ]"
                                    required
                                    @update:model-value="handleInput('sexo', $event)"
                                />
                            </v-col>
                            <v-col cols="12" md="4">
                                <v-text-field
                                    :model-value="modelValue.fecha_nacimiento"
                                    label="Fecha de Nacimiento"
                                    type="date"
                                    variant="outlined"
                                    density="compact"
                                    @update:model-value="handleInput('fecha_nacimiento', $event)"
                                />
                            </v-col>
                        </v-row>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Sección: Información Académica -->
        <v-card class="mb-4" rounded="lg" elevation="1">
            <v-card-title class="text-h6 font-weight-bold pa-4 pb-2">
                Información Académica
            </v-card-title>
            <v-card-text class="pa-4">
                <v-row>
                    <v-col cols="12" md="4">
                        <v-select
                            :model-value="modelValue.grado"
                            label="Grado"
                            variant="outlined"
                            density="compact"
                            :items="[
                                { title: 'TERCERO', value: '3' },
                                { title: 'CUARTO', value: '4' },
                                { title: 'QUINTO', value: '5' },
                            ]"
                            required
                            @update:model-value="handleInput('grado', $event)"
                        />
                    </v-col>
                    <v-col cols="12" md="4">
                        <v-select
                            :model-value="modelValue.seccion"
                            label="Sección"
                            variant="outlined"
                            density="compact"
                            :items="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('').map(l => ({ title: l, value: l }))"
                            required
                            @update:model-value="handleInput('seccion', $event)"
                        />
                    </v-col>
                    <v-col cols="12" md="4">
                        <v-text-field
                            :model-value="modelValue.correo_electronico"
                            label="Correo Electrónico"
                            type="email"
                            variant="outlined"
                            density="compact"
                            maxlength="100"
                            required
                            @update:model-value="handleInput('correo_electronico', $event)"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            :model-value="modelValue.codigo_estudiante"
                            label="Código de acceso padre"
                            variant="outlined"
                            density="compact"
                            readonly
                            placeholder="Se generará automáticamente"
                            append-inner-icon="mdi-refresh"
                            @click:append-inner="emit('generate-codigo')"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-select
                            :model-value="modelValue.condicion_estudiante"
                            label="Condición"
                            variant="outlined"
                            density="compact"
                            :items="[
                                { title: 'ESTUDIANTE', value: 'ESTUDIANTE' },
                                { title: 'EGRESADO', value: 'EGRESADO' },
                            ]"
                            required
                            @update:model-value="handleInput('condicion_estudiante', $event)"
                        />
                    </v-col>
                    <v-col cols="12">
                        <v-textarea
                            :model-value="modelValue.obsv"
                            label="Observaciones"
                            variant="outlined"
                            density="compact"
                            rows="3"
                            maxlength="500"
                            counter
                            @update:model-value="handleInput('obsv', $event)"
                        />
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Sección: Información de Contacto -->
        <v-card class="mb-4" rounded="lg" elevation="1">
            <v-card-title class="text-h6 font-weight-bold pa-4 pb-2">
                Información de Contacto
            </v-card-title>
            <v-card-text class="pa-4">
                <v-row>
                    <v-col cols="12" md="4">
                        <v-text-field
                            :model-value="modelValue.num_telefonico"
                            label="Número Telefónico"
                            variant="outlined"
                            density="compact"
                            maxlength="100"
                            @update:model-value="handleInput('num_telefonico', $event)"
                        />
                    </v-col>
                    <v-col cols="12" md="4">
                        <v-text-field
                            :model-value="modelValue.fecha_caducidad_dni"
                            label="Fecha de Caducidad DNI"
                            type="date"
                            variant="outlined"
                            density="compact"
                            @update:model-value="handleInput('fecha_caducidad_dni', $event)"
                        />
                    </v-col>
                    <v-col cols="12" md="4">
                        <v-text-field
                            :model-value="modelValue.religion"
                            label="Religión"
                            variant="outlined"
                            density="compact"
                            maxlength="100"
                            @update:model-value="handleInput('religion', $event)"
                        />
                    </v-col>
                    <v-col cols="12">
                        <v-textarea
                            :model-value="modelValue.lugar_nacimiento"
                            label="Lugar de Nacimiento (Región/Provincia/Distrito)"
                            variant="outlined"
                            density="compact"
                            rows="2"
                            maxlength="255"
                            counter
                            @update:model-value="handleInput('lugar_nacimiento', $event)"
                        />
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Sección: Domicilio Actual -->
        <v-card class="mb-4" rounded="lg" elevation="1">
            <v-card-title class="text-h6 font-weight-bold pa-4 pb-2">
                Domicilio Actual (no el COAR)
            </v-card-title>
            <v-card-text class="pa-4">
                <v-row>
                    <v-col cols="12" md="4">
                        <v-text-field
                            :model-value="modelValue.region_domicilio_actual"
                            label="Región"
                            variant="outlined"
                            density="compact"
                            maxlength="100"
                            @update:model-value="handleInput('region_domicilio_actual', $event)"
                        />
                    </v-col>
                    <v-col cols="12" md="4">
                        <v-text-field
                            :model-value="modelValue.provincia_domicilio_actual"
                            label="Provincia"
                            variant="outlined"
                            density="compact"
                            maxlength="100"
                            @update:model-value="handleInput('provincia_domicilio_actual', $event)"
                        />
                    </v-col>
                    <v-col cols="12" md="4">
                        <v-text-field
                            :model-value="modelValue.distrito_domicilio_actual"
                            label="Distrito"
                            variant="outlined"
                            density="compact"
                            maxlength="100"
                            @update:model-value="handleInput('distrito_domicilio_actual', $event)"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-textarea
                            :model-value="modelValue.direccion_domicilio_actual"
                            label="Dirección"
                            variant="outlined"
                            density="compact"
                            rows="2"
                            maxlength="255"
                            counter
                            @update:model-value="handleInput('direccion_domicilio_actual', $event)"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-textarea
                            :model-value="modelValue.referencia_domicilio_actual"
                            label="Referencia de cómo llegar"
                            variant="outlined"
                            density="compact"
                            rows="2"
                            maxlength="255"
                            counter
                            @update:model-value="handleInput('referencia_domicilio_actual', $event)"
                        />
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Sección: Residencia en el COAR -->
        <v-card class="mb-4" rounded="lg" elevation="1">
            <v-card-title class="text-h6 font-weight-bold pa-4 pb-2">
                Residencia en el COAR
            </v-card-title>
            <v-card-text class="pa-4">
                <v-row>
                    <v-col cols="12" md="3">
                        <v-text-field
                            :model-value="modelValue.lav"
                            label="Lav"
                            variant="outlined"
                            density="compact"
                            maxlength="50"
                            @update:model-value="handleInput('lav', $event)"
                        />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-text-field
                            :model-value="modelValue.llaves"
                            label="Llaves"
                            variant="outlined"
                            density="compact"
                            maxlength="50"
                            @update:model-value="handleInput('llaves', $event)"
                        />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-text-field
                            :model-value="modelValue.pabellon"
                            label="Pabellón"
                            variant="outlined"
                            density="compact"
                            maxlength="50"
                            @update:model-value="handleInput('pabellon', $event)"
                        />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-text-field
                            :model-value="modelValue.ala"
                            label="Ala"
                            variant="outlined"
                            density="compact"
                            maxlength="50"
                            @update:model-value="handleInput('ala', $event)"
                        />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-text-field
                            :model-value="modelValue.banos"
                            label="Baños"
                            variant="outlined"
                            density="compact"
                            maxlength="50"
                            @update:model-value="handleInput('banos', $event)"
                        />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-text-field
                            :model-value="modelValue.monitor_acompana"
                            label="Monitor que acompaña"
                            variant="outlined"
                            density="compact"
                            maxlength="100"
                            @update:model-value="handleInput('monitor_acompana', $event)"
                        />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-text-field
                            :model-value="modelValue.cama_ropero"
                            label="Ropero y cama"
                            variant="outlined"
                            density="compact"
                            maxlength="50"
                            @update:model-value="handleInput('cama_ropero', $event)"
                        />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-text-field
                            :model-value="modelValue.duchas"
                            label="Duchas"
                            variant="outlined"
                            density="compact"
                            maxlength="50"
                            @update:model-value="handleInput('duchas', $event)"
                        />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-text-field
                            :model-value="modelValue.urinarios"
                            label="Urinarios"
                            variant="outlined"
                            density="compact"
                            maxlength="50"
                            @update:model-value="handleInput('urinarios', $event)"
                        />
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>
    </v-container>
</template>
