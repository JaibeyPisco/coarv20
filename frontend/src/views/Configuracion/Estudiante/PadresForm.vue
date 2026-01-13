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
    { value: '', label: 'Seleccione...' },
    { value: 'SIN_INSTRUCCION', label: 'Sin Instrucción' },
    { value: 'PRIMARIA_INCOMPLETA', label: 'Primaria Incompleta' },
    { value: 'PRIMARIA_COMPLETA', label: 'Primaria Completa' },
    { value: 'SECUNDARIA_INCOMPLETA', label: 'Secundaria Incompleta' },
    { value: 'SECUNDARIA_COMPLETA', label: 'Secundaria Completa' },
    { value: 'TECNICO_INCOMPLETO', label: 'Técnico Incompleto' },
    { value: 'TECNICO_COMPLETO', label: 'Técnico Completo' },
    { value: 'SUPERIOR_NO_UNIVERSITARIA_INCOMPLETA', label: 'Superior No Universitaria Incompleta' },
    { value: 'SUPERIOR_NO_UNIVERSITARIA_COMPLETA', label: 'Superior No Universitaria Completa' },
    { value: 'UNIVERSITARIA_INCOMPLETA', label: 'Universitaria Incompleta' },
    { value: 'UNIVERSITARIA_COMPLETA', label: 'Universitaria Completa' },
    { value: 'POSTGRADO_MAESTRIA', label: 'Postgrado (Maestría)' },
    { value: 'POSTGRADO_DOCTORADO', label: 'Postgrado (Doctorado)' },
];

const tipoFamiliaOptions = [
    'FAMILA MONOPARENTAL',
    'FAMILIA NUCLEAR',
    'FAMILIA RECONSTITUÍDA',
    'FAMILIA EXTENSA',
];
</script>

<template>
    <div class="row">
        <!-- DATOS MADRE -->
        <div class="col-md-6">
            <div class="card border-primary border-top border-3">
                <div class="card-body">
                    <span style="font-size: 18px" class="mb-3"><b>Datos de la madre</b></span>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mt-3">
                                <div class="form-check form-switch">
                                    <input
                                        id="madre_viva"
                                        :checked="madreForm.vive"
                                        class="form-check-input"
                                        name="madre_viva"
                                        type="checkbox"
                                        @change="emit('update:madreForm', { ...madreForm, vive: ($event.target as HTMLInputElement).checked })"
                                    />
                                    <label for="madre_viva" class="form-label form-check-label"
                                        >¿La madre está viva?</label
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mt-3">
                                <div class="form-check form-switch">
                                    <input
                                        id="madre_con_estudiante"
                                        :checked="madreForm.vive_con_estudiante"
                                        class="form-check-input"
                                        name="madre_con_estudiante"
                                        type="checkbox"
                                        @change="emit('update:madreForm', { ...madreForm, vive_con_estudiante: ($event.target as HTMLInputElement).checked })"
                                    />
                                    <label for="madre_con_estudiante" class="form-label form-check-label"
                                        >¿La madre vive con el estudiante?</label
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mt-2 mb-3">
                                <label for="apellidos_madre" class="form-label"
                                    >Apellidos y de la madre<span class="text-red"></span></label
                                >
                                <input
                                    id="apellidos_madre"
                                    :value="madreForm.apellidos"
                                    type="text"
                                    name="apellidos_madre"
                                    class="form-control form-control-sm"
                                    autocomplete="off"
                                    @input="emit('update:madreForm', { ...madreForm, apellidos: ($event.target as HTMLInputElement).value })"
                                />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mt-2 mb-3">
                                <label for="nombres_madre" class="form-label"
                                    >Nombres de la madre<span class="text-red"></span></label
                                >
                                <input
                                    id="nombres_madre"
                                    :value="madreForm.nombres"
                                    type="text"
                                    name="nombres_madre"
                                    class="form-control form-control-sm"
                                    autocomplete="off"
                                    @input="emit('update:madreForm', { ...madreForm, nombres: ($event.target as HTMLInputElement).value })"
                                />
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="dni_madre" class="form-label"
                                            >N° de dni de la madre <span class="text-red"></span></label
                                        >
                                        <input
                                            id="dni_madre"
                                            :value="madreForm.dni"
                                            type="number"
                                            name="dni_madre"
                                            class="form-control form-control-sm"
                                            autocomplete="off"
                                            @input="emit('update:madreForm', { ...madreForm, dni: ($event.target as HTMLInputElement).value })"
                                        />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="grado_instruccion_madre" class="form-label"
                                            >Grado de instrucción de la madre</label
                                        >
                                        <select
                                            :value="madreForm.grado_instruccion"
                                            name="grado_instruccion_madre"
                                            class="form-control form-select-sm"
                                            @change="emit('update:madreForm', { ...madreForm, grado_instruccion: ($event.target as HTMLSelectElement).value })"
                                        >
                                            <option
                                                v-for="option in gradoInstruccionOptions"
                                                :key="option.value"
                                                :value="option.value"
                                            >
                                                {{ option.label }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="num_celular_madre" class="form-label"
                                            >N° de teléfono celular de la madre</label
                                        >
                                        <input
                                            id="num_celular_madre"
                                            :value="madreForm.telefono"
                                            type="text"
                                            name="num_celular_madre"
                                            class="form-control form-control-sm"
                                            autocomplete="off"
                                            @input="emit('update:madreForm', { ...madreForm, telefono: ($event.target as HTMLInputElement).value })"
                                        />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="correo_electronico_madre" class="form-label"
                                            >Correo electrónico de la madre</label
                                        >
                                        <input
                                            id="correo_electronico_madre"
                                            :value="madreForm.correo_electronico"
                                            type="text"
                                            name="correo_electronico_madre"
                                            class="form-control form-control-sm"
                                            autocomplete="off"
                                            @input="emit('update:madreForm', { ...madreForm, correo_electronico: ($event.target as HTMLInputElement).value })"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label for="ocupacion_actual_madre" class="form-label"
                                    >Ocupación actual de la madre</label
                                >
                                <input
                                    id="ocupacion_actual_madre"
                                    :value="madreForm.ocupacion_actual"
                                    type="text"
                                    name="ocupacion_actual_madre"
                                    class="form-control form-control-sm"
                                    autocomplete="off"
                                    @input="emit('update:madreForm', { ...madreForm, ocupacion_actual: ($event.target as HTMLInputElement).value })"
                                />
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="motivo_madre_no_vive_con_estudiante" class="form-label"
                                    >Motivo por el cual la madre no vive con el estudiante</label
                                >
                                <textarea
                                    id="motivo_madre_no_vive_con_estudiante"
                                    :value="madreForm.motivo_no_vive_con_estudiante"
                                    name="motivo_madre_no_vive_con_estudiante"
                                    class="form-control form-control-sm"
                                    autocomplete="off"
                                    @input="emit('update:madreForm', { ...madreForm, motivo_no_vive_con_estudiante: ($event.target as HTMLTextAreaElement).value })"
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- DATOS PADRE -->
        <div class="col-md-6">
            <div class="card border-primary border-top border-3">
                <div class="card-body">
                    <span style="font-size: 18px" class="mb-3"><b>Datos del padre</b></span>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mt-3">
                                <div class="form-check form-switch">
                                    <input
                                        id="padre_vivo"
                                        :checked="padreForm.vive"
                                        class="form-check-input"
                                        name="padre_vivo"
                                        type="checkbox"
                                        @change="emit('update:padreForm', { ...padreForm, vive: ($event.target as HTMLInputElement).checked })"
                                    />
                                    <label for="padre_vivo" class="form-label form-check-label"
                                        >¿El padre está vivo?</label
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mt-3">
                                <div class="form-check form-switch">
                                    <input
                                        id="padre_con_estudiante"
                                        :checked="padreForm.vive_con_estudiante"
                                        class="form-check-input"
                                        name="padre_con_estudiante"
                                        type="checkbox"
                                        @change="emit('update:padreForm', { ...padreForm, vive_con_estudiante: ($event.target as HTMLInputElement).checked })"
                                    />
                                    <label for="padre_con_estudiante" class="form-label form-check-label"
                                        >¿El padre vive con el estudiante?</label
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mt-2 mb-3">
                                <label for="apellidos_padre" class="form-label"
                                    >Apellidos del padre<span class="text-red"></span></label
                                >
                                <input
                                    id="apellidos_padre"
                                    :value="padreForm.apellidos"
                                    type="text"
                                    name="apellidos_padre"
                                    class="form-control form-control-sm"
                                    autocomplete="off"
                                    @input="emit('update:padreForm', { ...padreForm, apellidos: ($event.target as HTMLInputElement).value })"
                                />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mt-2 mb-3">
                                <label for="nombres_padre" class="form-label"
                                    >Nombres del padre<span class="text-red"></span></label
                                >
                                <input
                                    id="nombres_padre"
                                    :value="padreForm.nombres"
                                    type="text"
                                    name="nombres_padre"
                                    class="form-control form-control-sm"
                                    autocomplete="off"
                                    @input="emit('update:padreForm', { ...padreForm, nombres: ($event.target as HTMLInputElement).value })"
                                />
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="dni_padre" class="form-label"
                                            >N° de dni del padre <span class="text-red"></span></label
                                        >
                                        <input
                                            id="dni_padre"
                                            :value="padreForm.dni"
                                            type="number"
                                            name="dni_padre"
                                            class="form-control form-control-sm"
                                            autocomplete="off"
                                            @input="emit('update:padreForm', { ...padreForm, dni: ($event.target as HTMLInputElement).value })"
                                        />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="grado_instruccion_padre" class="form-label"
                                            >Grado de instrucción del padre</label
                                        >
                                        <select
                                            :value="padreForm.grado_instruccion"
                                            name="grado_instruccion_padre"
                                            class="form-control form-select-sm"
                                            @change="emit('update:padreForm', { ...padreForm, grado_instruccion: ($event.target as HTMLSelectElement).value })"
                                        >
                                            <option
                                                v-for="option in gradoInstruccionOptions"
                                                :key="option.value"
                                                :value="option.value"
                                            >
                                                {{ option.label }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="num_celular_padre" class="form-label"
                                            >N° de teléfono celular del padre</label
                                        >
                                        <input
                                            id="num_celular_padre"
                                            :value="padreForm.telefono"
                                            type="text"
                                            name="num_celular_padre"
                                            class="form-control form-control-sm"
                                            autocomplete="off"
                                            @input="emit('update:padreForm', { ...padreForm, telefono: ($event.target as HTMLInputElement).value })"
                                        />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="correo_electronico_padre" class="form-label"
                                            >Correo electrónico del padre</label
                                        >
                                        <input
                                            id="correo_electronico_padre"
                                            :value="padreForm.correo_electronico"
                                            type="text"
                                            name="correo_electronico_padre"
                                            class="form-control form-control-sm"
                                            autocomplete="off"
                                            @input="emit('update:padreForm', { ...padreForm, correo_electronico: ($event.target as HTMLInputElement).value })"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label for="ocupacion_actual_padre" class="form-label"
                                    >Ocupación actual del padre</label
                                >
                                <input
                                    id="ocupacion_actual_padre"
                                    :value="padreForm.ocupacion_actual"
                                    type="text"
                                    name="ocupacion_actual_padre"
                                    class="form-control form-control-sm"
                                    autocomplete="off"
                                    @input="emit('update:padreForm', { ...padreForm, ocupacion_actual: ($event.target as HTMLInputElement).value })"
                                />
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="motivo_padre_no_vive_con_estudiante" class="form-label"
                                    >Motivo por el cual el padre no vive con el estudiante</label
                                >
                                <textarea
                                    id="motivo_padre_no_vive_con_estudiante"
                                    :value="padreForm.motivo_no_vive_con_estudiante"
                                    name="motivo_padre_no_vive_con_estudiante"
                                    class="form-control form-control-sm"
                                    autocomplete="off"
                                    @input="emit('update:padreForm', { ...padreForm, motivo_no_vive_con_estudiante: ($event.target as HTMLTextAreaElement).value })"
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- DATOS APODERADO ROL PADRE/MADRE -->
        <div class="col-md-12">
            <div class="card border-primary border-top border-3">
                <div class="card-body">
                    <span style="font-size: 18px" class="mb-3"
                        ><b>Datos del apoderado que cumple el rol de padre o madre</b></span
                    >
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mt-3 mb-3">
                                <label for="parentesco_con_apoderado" class="form-label"
                                    >Parentesco con tu apoderado<span class="text-red"></span></label
                                >
                                <input
                                    id="parentesco_con_apoderado"
                                    :value="apoderadoRolPadreForm.parentesco_estudiante"
                                    type="text"
                                    name="parentesco_con_apoderado"
                                    class="form-control form-control-sm"
                                    autocomplete="off"
                                    @input="emit('update:apoderadoRolPadreForm', { ...apoderadoRolPadreForm, parentesco_estudiante: ($event.target as HTMLInputElement).value })"
                                />
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label for="apellidos_apoderado" class="form-label"
                                    >Apellidos del apoderado (a) <span class="text-red"></span></label
                                >
                                <input
                                    id="apellidos_apoderado"
                                    :value="apoderadoRolPadreForm.apellidos"
                                    type="text"
                                    name="apellidos_apoderado"
                                    class="form-control form-control-sm"
                                    autocomplete="off"
                                    @input="emit('update:apoderadoRolPadreForm', { ...apoderadoRolPadreForm, apellidos: ($event.target as HTMLInputElement).value })"
                                />
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label for="nombres_apoderado" class="form-label"
                                    >Nombres del apoderado (a) <span class="text-red"></span></label
                                >
                                <input
                                    id="nombres_apoderado"
                                    :value="apoderadoRolPadreForm.nombres"
                                    type="text"
                                    name="nombres_apoderado"
                                    class="form-control form-control-sm"
                                    autocomplete="off"
                                    @input="emit('update:apoderadoRolPadreForm', { ...apoderadoRolPadreForm, nombres: ($event.target as HTMLInputElement).value })"
                                />
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="dni_apoderado" class="form-label"
                                            >N° de dni del apoderado <span class="text-red"></span></label
                                        >
                                        <input
                                            id="dni_apoderado"
                                            :value="apoderadoRolPadreForm.dni"
                                            type="text"
                                            name="dni_apoderado"
                                            class="form-control form-control-sm"
                                            autocomplete="off"
                                            @input="emit('update:apoderadoRolPadreForm', { ...apoderadoRolPadreForm, dni: ($event.target as HTMLInputElement).value })"
                                        />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="num_celular_apoderado" class="form-label"
                                            >N° de teléfono celular del apoderado</label
                                        >
                                        <input
                                            id="num_celular_apoderado"
                                            :value="apoderadoRolPadreForm.telefono"
                                            type="text"
                                            name="num_celular_apoderado"
                                            class="form-control form-control-sm"
                                            autocomplete="off"
                                            @input="emit('update:apoderadoRolPadreForm', { ...apoderadoRolPadreForm, telefono: ($event.target as HTMLInputElement).value })"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label for="tipo_familia" class="form-label">Elige tu tipo de familia</label>
                                <input
                                    id="tipo_familia"
                                    :value="apoderadoRolPadreForm.tipo_familia"
                                    class="form-control form-control-sm"
                                    list="datalistOptions"
                                    name="tipo_familia"
                                    placeholder="Elige tu tipo de familia..."
                                    @input="emit('update:apoderadoRolPadreForm', { ...apoderadoRolPadreForm, tipo_familia: ($event.target as HTMLInputElement).value })"
                                />
                                <datalist id="datalistOptions">
                                    <option
                                        v-for="option in tipoFamiliaOptions"
                                        :key="option"
                                        :value="option"
                                    ></option>
                                </datalist>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>



