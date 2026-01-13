<script setup lang="ts">
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
    <div class="card border-primary border-top border-3">
        <div class="card-body">
            <span style="font-size: 18px" class="mb-3"><b>Datos del apoderado</b></span>
            <div class="row mt-3">
                <div class="col-md-12">
                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>APELLIDOS</th>
                                <th>NOMBRES</th>
                                <th>DNI</th>
                                <th>NUMERO TELEFÓNICO</th>
                                <th>GRADO DE PARENTESCO</th>
                                <th>¿LEGALIZADO?</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(apoderado, index) in modelValue" :key="index">
                                <td>
                                    <input
                                        :value="apoderado.apellidos"
                                        type="text"
                                        class="form-control form-control-sm"
                                        @input="updateApoderado(index, 'apellidos', ($event.target as HTMLInputElement).value)"
                                    />
                                </td>
                                <td>
                                    <input
                                        :value="apoderado.nombres"
                                        type="text"
                                        class="form-control form-control-sm"
                                        @input="updateApoderado(index, 'nombres', ($event.target as HTMLInputElement).value)"
                                    />
                                </td>
                                <td>
                                    <input
                                        :value="apoderado.dni"
                                        type="number"
                                        class="form-control form-control-sm"
                                        @input="updateApoderado(index, 'dni', ($event.target as HTMLInputElement).value)"
                                    />
                                </td>
                                <td>
                                    <input
                                        :value="apoderado.numero_telefonico"
                                        type="number"
                                        class="form-control form-control-sm"
                                        @input="updateApoderado(index, 'numero_telefonico', ($event.target as HTMLInputElement).value)"
                                    />
                                </td>
                                <td>
                                    <input
                                        :value="apoderado.grado_parentesco"
                                        type="text"
                                        class="form-control form-control-sm"
                                        @input="updateApoderado(index, 'grado_parentesco', ($event.target as HTMLInputElement).value)"
                                    />
                                </td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input
                                            :checked="apoderado.legalizado"
                                            class="form-check-input"
                                            type="checkbox"
                                            @change="updateApoderado(index, 'legalizado', ($event.target as HTMLInputElement).checked)"
                                        />
                                    </div>
                                </td>
                                <td>
                                    <button
                                        type="button"
                                        class="btn btn-danger btn-sm"
                                        @click="removeApoderado(index)"
                                    >
                                        <i class="ti ti-x"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" align="center">
                                    <button type="button" class="btn btn-primary btn-sm" @click="addApoderado">
                                        <i class="ti ti-plus"></i> Agregar Apoderado
                                    </button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

