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
    <div>
        <div class="row">
            <div class="col-md-3">
                <div class="mb-3" align="center">
                    <div class="mb-2">
                        <img
                            :key="`foto-${editingId || 'new'}-${fotoPreview}`"
                            :src="fotoPreview || '/images/sin_imagen.jpg'"
                            alt="Foto"
                            class="img-fluid rounded"
                            style="
                                max-width: 100%;
                                max-height: 200px;
                                object-fit: cover;
                                border: 1px solid #dee2e6;
                            "
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
                    </div>
                    <label class="btn btn-default btn-sm w-100">
                        <i class="ti ti-upload me-1"></i> Imagen de Perfil
                        <input
                            type="file"
                            accept="image/*"
                            style="display: none"
                            @change="handleFotoChange"
                        />
                    </label>
                </div>
            </div>
            <div class="col-md-9">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Apellidos</label>
                        <input
                            :value="modelValue.apellidos"
                            type="text"
                            class="form-control"
                            maxlength="200"
                            required
                            @input="handleInput('apellidos', ($event.target as HTMLInputElement).value)"
                        />
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Nombres</label>
                        <input
                            :value="modelValue.nombres"
                            type="text"
                            class="form-control"
                            maxlength="200"
                            required
                            @input="handleInput('nombres', ($event.target as HTMLInputElement).value)"
                        />
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label required">DNI</label>
                        <input
                            :value="modelValue.dni"
                            type="text"
                            class="form-control"
                            maxlength="8"
                            minlength="8"
                            required
                            @input="handleInput('dni', ($event.target as HTMLInputElement).value)"
                        />
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label required">Sexo</label>
                        <select
                            :value="modelValue.sexo"
                            class="form-select"
                            required
                            @change="handleInput('sexo', ($event.target as HTMLSelectElement).value)"
                        >
                            <option value="">Seleccionar...</option>
                            <option value="MASCULINO">MASCULINO</option>
                            <option value="FEMENINO">FEMENINO</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label required">Grado</label>
                        <select
                            :value="modelValue.grado"
                            class="form-select"
                            required
                            @change="handleInput('grado', ($event.target as HTMLSelectElement).value)"
                        >
                            <option value="">Seleccionar...</option>
                            <option value="3">TERCERO</option>
                            <option value="4">CUARTO</option>
                            <option value="5">QUINTO</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label required">Sección</label>
                        <select
                            :value="modelValue.seccion"
                            class="form-select"
                            required
                            @change="handleInput('seccion', ($event.target as HTMLSelectElement).value)"
                        >
                            <option value="">Seleccionar...</option>
                            <option
                                v-for="letra in 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('')"
                                :key="letra"
                                :value="letra"
                            >
                                {{ letra }}
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label required">Correo Electrónico</label>
                        <input
                            :value="modelValue.correo_electronico"
                            type="email"
                            class="form-control"
                            maxlength="100"
                            required
                            @input="handleInput('correo_electronico', ($event.target as HTMLInputElement).value)"
                        />
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Fecha de Nacimiento</label>
                        <input
                            :value="modelValue.fecha_nacimiento"
                            type="date"
                            class="form-control"
                            @input="handleInput('fecha_nacimiento', ($event.target as HTMLInputElement).value)"
                        />
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Código de acceso padre</label>
                        <div class="input-group">
                            <input
                                :value="modelValue.codigo_estudiante"
                                type="text"
                                class="form-control"
                                readonly
                                placeholder="Se generará automáticamente"
                            />
                            <button
                                class="btn btn-primary"
                                type="button"
                                @click="emit('generate-codigo')"
                            >
                                <i class="ti ti-refresh"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Condición</label>
                        <input
                            :value="modelValue.condicion_estudiante"
                            type="text"
                            class="form-control"
                            required
                            @input="handleInput('condicion_estudiante', ($event.target as HTMLInputElement).value)"
                        />
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">OBVS</label>
                        <textarea
                            :value="modelValue.obsv"
                            class="form-control"
                            rows="2"
                            maxlength="500"
                            @input="handleInput('obsv', ($event.target as HTMLTextAreaElement).value)"
                        ></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Residencia -->
        <div class="row mt-4">
            <div class="col-md-12">
                <h5 class="mb-3">Residencia</h5>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Lav</label>
                <input
                    :value="modelValue.lav"
                    type="text"
                    class="form-control"
                    maxlength="50"
                    @input="handleInput('lav', ($event.target as HTMLInputElement).value)"
                />
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Llaves</label>
                <input
                    :value="modelValue.llaves"
                    type="text"
                    class="form-control"
                    maxlength="50"
                    @input="handleInput('llaves', ($event.target as HTMLInputElement).value)"
                />
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Pabellón</label>
                <input
                    :value="modelValue.pabellon"
                    type="text"
                    class="form-control"
                    maxlength="50"
                    @input="handleInput('pabellon', ($event.target as HTMLInputElement).value)"
                />
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Ala</label>
                <input
                    :value="modelValue.ala"
                    type="text"
                    class="form-control"
                    maxlength="50"
                    @input="handleInput('ala', ($event.target as HTMLInputElement).value)"
                />
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Baños</label>
                <input
                    :value="modelValue.banos"
                    type="text"
                    class="form-control"
                    maxlength="50"
                    @input="handleInput('banos', ($event.target as HTMLInputElement).value)"
                />
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Monitor que acompaña</label>
                <input
                    :value="modelValue.monitor_acompana"
                    type="text"
                    class="form-control"
                    maxlength="100"
                    @input="handleInput('monitor_acompana', ($event.target as HTMLInputElement).value)"
                />
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Ropero y cama</label>
                <input
                    :value="modelValue.cama_ropero"
                    type="text"
                    class="form-control"
                    maxlength="50"
                    @input="handleInput('cama_ropero', ($event.target as HTMLInputElement).value)"
                />
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Duchas</label>
                <input
                    :value="modelValue.duchas"
                    type="text"
                    class="form-control"
                    maxlength="50"
                    @input="handleInput('duchas', ($event.target as HTMLInputElement).value)"
                />
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Urinarios</label>
                <input
                    :value="modelValue.urinarios"
                    type="text"
                    class="form-control"
                    maxlength="50"
                    @input="handleInput('urinarios', ($event.target as HTMLInputElement).value)"
                />
            </div>
        </div>

        <!-- Datos complementarios -->
        <div class="row mt-4">
            <div class="col-md-12">
                <h5 class="mb-3">Datos complementarios</h5>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Lugar de nacimiento (Región/Provincia/Distrito)</label>
                <textarea
                    :value="modelValue.lugar_nacimiento"
                    class="form-control"
                    rows="2"
                    maxlength="255"
                    @input="handleInput('lugar_nacimiento', ($event.target as HTMLTextAreaElement).value)"
                ></textarea>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Fecha de caducidad DNI</label>
                <input
                    :value="modelValue.fecha_caducidad_dni"
                    type="date"
                    class="form-control"
                    @input="handleInput('fecha_caducidad_dni', ($event.target as HTMLInputElement).value)"
                />
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Número telefónico del estudiante</label>
                <textarea
                    :value="modelValue.num_telefonico"
                    class="form-control"
                    rows="2"
                    maxlength="100"
                    @input="handleInput('num_telefonico', ($event.target as HTMLTextAreaElement).value)"
                ></textarea>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Religión que profesa el estudiante</label>
                <textarea
                    :value="modelValue.religion"
                    class="form-control"
                    rows="2"
                    maxlength="100"
                    @input="handleInput('religion', ($event.target as HTMLTextAreaElement).value)"
                ></textarea>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Región de domicilio actual (no el COAR)</label>
                <textarea
                    :value="modelValue.region_domicilio_actual"
                    class="form-control"
                    rows="2"
                    maxlength="100"
                    @input="handleInput('region_domicilio_actual', ($event.target as HTMLTextAreaElement).value)"
                ></textarea>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Provincia de domicilio actual (no el COAR)</label>
                <textarea
                    :value="modelValue.provincia_domicilio_actual"
                    class="form-control"
                    rows="2"
                    maxlength="100"
                    @input="handleInput('provincia_domicilio_actual', ($event.target as HTMLTextAreaElement).value)"
                ></textarea>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Distrito de domicilio actual (no el COAR)</label>
                <textarea
                    :value="modelValue.distrito_domicilio_actual"
                    class="form-control"
                    rows="2"
                    maxlength="100"
                    @input="handleInput('distrito_domicilio_actual', ($event.target as HTMLTextAreaElement).value)"
                ></textarea>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Dirección de domicilio actual (no el COAR)</label>
                <textarea
                    :value="modelValue.direccion_domicilio_actual"
                    class="form-control"
                    rows="2"
                    maxlength="255"
                    @input="handleInput('direccion_domicilio_actual', ($event.target as HTMLTextAreaElement).value)"
                ></textarea>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Referencia de cómo llegar a su domicilio</label>
                <textarea
                    :value="modelValue.referencia_domicilio_actual"
                    class="form-control"
                    rows="2"
                    maxlength="255"
                    @input="handleInput('referencia_domicilio_actual', ($event.target as HTMLTextAreaElement).value)"
                ></textarea>
            </div>
        </div>
    </div>
</template>









