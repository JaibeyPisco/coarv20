<script setup lang="ts">
import AuthenticatedLayout from '../../components/Layouts/AuthenticatedLayout.vue';
import { ref, onMounted } from 'vue';
import apiClient from '@/api/axios';
import { CurrentDateTime } from '@/utils/HelperDates';
import type { Data, Lugar, Root, TipoIncidencia, Area } from '@/types/operacion/Incidencia';
 

 
const initialData = ref<Data | null>(null);


const secuencia = ref('');
const currentDateTime = ref('');
// const tipoIncidencias = ref<TipoIncidencia[]>([]);
// const lugares = ref<Lugar[]>([]);
// const areas = ref<Area[]>([]);


onMounted(async () => {
    const { data } = await apiClient.get<Root>('operacion/incidencia/getInitial');

    initialData.value = data.data;

    secuencia.value = initialData.value.secuencia;

    // tipoIncidencias.value = initialData.value.tipoIncidencias;
    // lugares.value = initialData.value.lugares;
    // areas.value = initialData.value.areas;

    currentDateTime.value = CurrentDateTime();
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="h2 mb-1">Operación / Nueva incidencia</h1>
                    <p class="text-secondary mb-0">
                        Gestiona las áreas de la organización; activa, edita o elimina según
                        necesidad.
                    </p>
                </div>
            </div>
        </template>

        <div class="row row-cards">
            <div class="row row-cols-1 row-cols-md-12 row-cols-lg-12 row-cols-xl-12">
                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <form name="save-incidencia">
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <div class="form-group mb-3">
                                                <label class="form-label" for="numero"
                                                    >N° Inicidencia</label
                                                >
                                                <input
                                                    id="numero"
                                                    type="text"
                                                    data-name="numero"
                                                    class="form-control form-control-sm"
                                                    autocomplete="off"
                                                    readonly=""
                                                    v-model="secuencia"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <div class="form-group mb-3">
                                                <label class="form-label" for="numero"
                                                    >Fecha y hora</label
                                                >
                                                <input
                                                    type="datetime-local"
                                                    name="fecha_hora"
                                                    class="form-control form-control-sm"
                                                    autocomplete="off"
                                                    required=""
                                                    v-model="currentDateTime"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label class="form-label" for="id_tipo_incidencia"
                                                >Tipo Incidencia</label
                                            >
                                            <v-select
                                                :options="[{ label: 'Canada', code: 'ca' }]"
                                            ></v-select>

                                            <select
                                                name="id_tipo_incidencia"
                                                data-select="TIPOINCIDENCIA"
                                                class="form-control-sm form-control select2-hidden-accessible"
                                                autocomplete="off"
                                                required=""
                                                data-select2-id="6"
                                                tabindex="-1"
                                                aria-hidden="true"
                                            >
                                                <option value="" data-select2-id="8">
                                                    Seleccione...
                                                </option>
                                                <option
                                                    value="1"
                                                    data-json='{"id":"1","text":"Hábito por mejorar"}'
                                                >
                                                    Hábito por mejorar
                                                </option>
                                                <option
                                                    value="2"
                                                    data-json='{"id":"2","text":"Conducta inadecuada (Leve - Académico)"}'
                                                >
                                                    Conducta inadecuada (Leve - Académico)
                                                </option>
                                                <option
                                                    value="3"
                                                    data-json='{"id":"3","text":"Conducta inadecuada (Grave - Académico)"}'
                                                >
                                                    Conducta inadecuada (Grave - Académico)
                                                </option>
                                                <option
                                                    value="4"
                                                    data-json='{"id":"4","text":"Conducta inadecuada (Leve - ByDE)"}'
                                                >
                                                    Conducta inadecuada (Leve - ByDE)
                                                </option>
                                                <option
                                                    value="5"
                                                    data-json='{"id":"5","text":"Conducta inadecuada (Grave- ByDE)"}'
                                                >
                                                    Conducta inadecuada (Grave- ByDE)
                                                </option>
                                                <option
                                                    value="6"
                                                    data-json='{"id":"6","text":"Caso de Violencia"}'
                                                >
                                                    Caso de Violencia
                                                </option>
                                                <option
                                                    value="7"
                                                    data-json='{"id":"7","text":"Buenas practicas"}'
                                                >
                                                    Buenas practicas
                                                </option></select
                                            ><span
                                                class="select2 select2-container select2-container--default"
                                                dir="ltr"
                                                data-select2-id="7"
                                                style="width: 497px"
                                                ><span class="selection"
                                                    ><span
                                                        class="select2-selection select2-selection--single"
                                                        role="combobox"
                                                        aria-haspopup="true"
                                                        aria-expanded="false"
                                                        tabindex="0"
                                                        aria-labelledby="select2-id_tipo_incidencia-il-container"
                                                        ><span
                                                            class="select2-selection__rendered"
                                                            id="select2-id_tipo_incidencia-il-container"
                                                            role="textbox"
                                                            aria-readonly="true"
                                                            title="Seleccione..."
                                                            >Seleccione...</span
                                                        ><span
                                                            class="select2-selection__arrow"
                                                            role="presentation"
                                                            ><b
                                                                role="presentation"
                                                            ></b></span></span></span
                                                ><span
                                                    class="dropdown-wrapper"
                                                    aria-hidden="true"
                                                ></span
                                            ></span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Lugar de la Incidencia</label>
                                            <select
                                                name="id_lugar_incidencia"
                                                data-select="LUGAR"
                                                class="form-control-sm form-control select2-hidden-accessible"
                                                autocomplete="off"
                                                required=""
                                                data-select2-id="3"
                                                tabindex="-1"
                                                aria-hidden="true"
                                            >
                                                <option value="" data-select2-id="5">
                                                    Seleccione...
                                                </option>
                                                <option
                                                    value="1"
                                                    data-json='{"id":"1","text":"COMEDOR"}'
                                                >
                                                    COMEDOR
                                                </option>
                                                <option
                                                    value="2"
                                                    data-json='{"id":"2","text":"BIBLIOTECA"}'
                                                >
                                                    BIBLIOTECA
                                                </option>
                                                <option
                                                    value="3"
                                                    data-json='{"id":"3","text":"ARTES VISUALES"}'
                                                >
                                                    ARTES VISUALES
                                                </option>
                                                <option
                                                    value="4"
                                                    data-json='{"id":"4","text":"PATIO ACADEMICO"}'
                                                >
                                                    PATIO ACADEMICO
                                                </option>
                                                <option
                                                    value="5"
                                                    data-json='{"id":"5","text":"RESIDENCIA DE VARONES"}'
                                                >
                                                    RESIDENCIA DE VARONES
                                                </option>
                                                <option
                                                    value="6"
                                                    data-json='{"id":"6","text":"RESIDENCIA DE MUJERES"}'
                                                >
                                                    RESIDENCIA DE MUJERES
                                                </option>
                                                <option
                                                    value="7"
                                                    data-json='{"id":"7","text":"BAÑOS VARONES - PATIO ACADEMICO"}'
                                                >
                                                    BAÑOS VARONES - PATIO ACADEMICO
                                                </option>
                                                <option
                                                    value="8"
                                                    data-json='{"id":"8","text":"LOSA DEPORTIVA NUEVA"}'
                                                >
                                                    LOSA DEPORTIVA NUEVA
                                                </option>
                                                <option
                                                    value="9"
                                                    data-json='{"id":"9","text":"BAÑOS MUJERES - PATIO ACADEMICO"}'
                                                >
                                                    BAÑOS MUJERES - PATIO ACADEMICO
                                                </option>
                                                <option
                                                    value="10"
                                                    data-json='{"id":"10","text":"CANCHA DE FUTBOL"}'
                                                >
                                                    CANCHA DE FUTBOL
                                                </option>
                                                <option
                                                    value="11"
                                                    data-json='{"id":"11","text":"COMEDOR"}'
                                                >
                                                    COMEDOR
                                                </option>
                                                <option
                                                    value="12"
                                                    data-json='{"id":"12","text":"BIBLIOTECA"}'
                                                >
                                                    BIBLIOTECA
                                                </option>
                                                <option
                                                    value="13"
                                                    data-json='{"id":"13","text":"AULA 3A"}'
                                                >
                                                    AULA 3A
                                                </option>
                                                <option
                                                    value="14"
                                                    data-json='{"id":"14","text":"AULA 3B"}'
                                                >
                                                    AULA 3B
                                                </option>
                                                <option
                                                    value="15"
                                                    data-json='{"id":"15","text":"AULA 3C"}'
                                                >
                                                    AULA 3C
                                                </option>
                                                <option
                                                    value="16"
                                                    data-json='{"id":"16","text":"AULA 3D"}'
                                                >
                                                    AULA 3D
                                                </option>
                                                <option
                                                    value="17"
                                                    data-json='{"id":"17","text":"AULA 4A"}'
                                                >
                                                    AULA 4A
                                                </option>
                                                <option
                                                    value="18"
                                                    data-json='{"id":"18","text":"AULA 4B"}'
                                                >
                                                    AULA 4B
                                                </option>
                                                <option
                                                    value="19"
                                                    data-json='{"id":"19","text":"AULA 4C"}'
                                                >
                                                    AULA 4C
                                                </option>
                                                <option
                                                    value="20"
                                                    data-json='{"id":"20","text":"AULA 4D"}'
                                                >
                                                    AULA 4D
                                                </option>
                                                <option
                                                    value="21"
                                                    data-json='{"id":"21","text":"AULA 5A"}'
                                                >
                                                    AULA 5A
                                                </option>
                                                <option
                                                    value="22"
                                                    data-json='{"id":"22","text":"AULA 5B"}'
                                                >
                                                    AULA 5B
                                                </option>
                                                <option
                                                    value="23"
                                                    data-json='{"id":"23","text":"AULA 5C"}'
                                                >
                                                    AULA 5C
                                                </option>
                                                <option
                                                    value="24"
                                                    data-json='{"id":"24","text":"AULA 5D"}'
                                                >
                                                    AULA 5D
                                                </option>
                                                <option
                                                    value="25"
                                                    data-json='{"id":"25","text":"TALLER DE ARTES VISUALES"}'
                                                >
                                                    TALLER DE ARTES VISUALES
                                                </option>
                                                <option
                                                    value="26"
                                                    data-json='{"id":"26","text":"CAMPO DE FUTBOL"}'
                                                >
                                                    CAMPO DE FUTBOL
                                                </option>
                                                <option
                                                    value="27"
                                                    data-json='{"id":"27","text":"LOZA DEPORTIVA ANTIGUA"}'
                                                >
                                                    LOZA DEPORTIVA ANTIGUA
                                                </option>
                                                <option
                                                    value="28"
                                                    data-json='{"id":"28","text":"LOZA DEPORTIVA NUEVA"}'
                                                >
                                                    LOZA DEPORTIVA NUEVA
                                                </option>
                                                <option
                                                    value="29"
                                                    data-json='{"id":"29","text":"RESIDENCIA DE VARONES"}'
                                                >
                                                    RESIDENCIA DE VARONES
                                                </option>
                                                <option
                                                    value="30"
                                                    data-json='{"id":"30","text":"RESIDENCIA DE MUJERES"}'
                                                >
                                                    RESIDENCIA DE MUJERES
                                                </option>
                                                <option
                                                    value="31"
                                                    data-json='{"id":"31","text":"PATIO ACADEMICO"}'
                                                >
                                                    PATIO ACADEMICO
                                                </option>
                                                <option
                                                    value="32"
                                                    data-json='{"id":"32","text":"ESPALDAS DE DIRECION"}'
                                                >
                                                    ESPALDAS DE DIRECION
                                                </option>
                                                <option
                                                    value="33"
                                                    data-json='{"id":"33","text":"TÓPICO"}'
                                                >
                                                    TÓPICO
                                                </option>
                                                <option
                                                    value="34"
                                                    data-json='{"id":"34","text":"ESPACIO DE LAVANDERIA."}'
                                                >
                                                    ESPACIO DE LAVANDERIA.
                                                </option>
                                                <option
                                                    value="35"
                                                    data-json='{"id":"35","text":"PUERTA DE INGRESO"}'
                                                >
                                                    PUERTA DE INGRESO
                                                </option></select
                                            ><span
                                                class="select2 select2-container select2-container--default"
                                                dir="ltr"
                                                data-select2-id="4"
                                                style="width: 497px"
                                                ><span class="selection"
                                                    ><span
                                                        class="select2-selection select2-selection--single"
                                                        role="combobox"
                                                        aria-haspopup="true"
                                                        aria-expanded="false"
                                                        tabindex="0"
                                                        aria-labelledby="select2-id_lugar_incidencia-jm-container"
                                                        ><span
                                                            class="select2-selection__rendered"
                                                            id="select2-id_lugar_incidencia-jm-container"
                                                            role="textbox"
                                                            aria-readonly="true"
                                                            title="Seleccione..."
                                                            >Seleccione...</span
                                                        ><span
                                                            class="select2-selection__arrow"
                                                            role="presentation"
                                                            ><b
                                                                role="presentation"
                                                            ></b></span></span></span
                                                ><span
                                                    class="dropdown-wrapper"
                                                    aria-hidden="true"
                                                ></span
                                            ></span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Areá a reportar</label>
                                            <select
                                                name="id_area"
                                                data-select="AREA"
                                                class="form-control-sm form-control select2-hidden-accessible"
                                                autocomplete="off"
                                                data-select2-id="9"
                                                tabindex="-1"
                                                aria-hidden="true"
                                            >
                                                <option value="" data-select2-id="11">
                                                    Seleccione...
                                                </option>
                                                <option
                                                    value="1"
                                                    data-json='{"id":"1","text":"AREA ACADEMICA"}'
                                                >
                                                    AREA ACADEMICA
                                                </option>
                                                <option
                                                    value="2"
                                                    data-json='{"id":"2","text":"ADMINISTRATIVOS"}'
                                                >
                                                    ADMINISTRATIVOS
                                                </option>
                                                <option
                                                    value="3"
                                                    data-json='{"id":"3","text":"AREA BYDE"}'
                                                >
                                                    AREA BYDE
                                                </option></select
                                            ><span
                                                class="select2 select2-container select2-container--default"
                                                dir="ltr"
                                                data-select2-id="10"
                                                style="width: 497px"
                                                ><span class="selection"
                                                    ><span
                                                        class="select2-selection select2-selection--single"
                                                        role="combobox"
                                                        aria-haspopup="true"
                                                        aria-expanded="false"
                                                        tabindex="0"
                                                        aria-labelledby="select2-id_area-bo-container"
                                                        ><span
                                                            class="select2-selection__rendered"
                                                            id="select2-id_area-bo-container"
                                                            role="textbox"
                                                            aria-readonly="true"
                                                            title="Seleccione..."
                                                            >Seleccione...</span
                                                        ><span
                                                            class="select2-selection__arrow"
                                                            role="presentation"
                                                            ><b
                                                                role="presentation"
                                                            ></b></span></span></span
                                                ><span
                                                    class="dropdown-wrapper"
                                                    aria-hidden="true"
                                                ></span
                                            ></span>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Añadir estudiante</label>
                                            <div name="estudiantes">
                                                <div bis_skin_checked="1" data-codigo="btf3fmdb4rd">
                                                    <div class="row">
                                                        <div class="col-md-10">
                                                            <div
                                                                class="form-group mb-3"
                                                                bis_skin_checked="1"
                                                            >
                                                                <select
                                                                    name="id_estudiante"
                                                                    data-select="ESTUDIANTE"
                                                                    class="form-select select2-hidden-accessible"
                                                                    autocomplete="off"
                                                                    data-select2-id="1"
                                                                    tabindex="-1"
                                                                    aria-hidden="true"
                                                                ></select
                                                                ><span
                                                                    class="select2 select2-container select2-container--default"
                                                                    dir="ltr"
                                                                    data-select2-id="2"
                                                                    style="width: 1278.5px"
                                                                    ><span class="selection"
                                                                        ><span
                                                                            class="select2-selection select2-selection--single"
                                                                            role="combobox"
                                                                            aria-haspopup="true"
                                                                            aria-expanded="false"
                                                                            tabindex="0"
                                                                            aria-labelledby="select2-id_estudiante-0r-container"
                                                                            ><span
                                                                                class="select2-selection__rendered"
                                                                                id="select2-id_estudiante-0r-container"
                                                                                role="textbox"
                                                                                aria-readonly="true"
                                                                                ><span
                                                                                    class="select2-selection__placeholder"
                                                                                    >APELLIDO Y
                                                                                    NOMBRE - DNI -
                                                                                    GRADO Y
                                                                                    SECCIÓN</span
                                                                                ></span
                                                                            ><span
                                                                                class="select2-selection__arrow"
                                                                                role="presentation"
                                                                                ><b
                                                                                    role="presentation"
                                                                                ></b></span></span></span
                                                                    ><span
                                                                        class="dropdown-wrapper"
                                                                        aria-hidden="true"
                                                                    ></span
                                                                ></span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2" bis_skin_checked="1">
                                                            <div
                                                                class="form-group mb-3"
                                                                bis_skin_checked="1"
                                                            >
                                                                <button
                                                                    type="button"
                                                                    class="btn btn-warning px-4 btn-warning-dark btn-sm"
                                                                    style="font-size: 15px"
                                                                    data-codigo="btf3fmdb4rd"
                                                                    name="delete-item"
                                                                >
                                                                    <i
                                                                        class="lni lni-circle-minus"
                                                                        style="font-size: 15px"
                                                                    ></i
                                                                    >Quitar
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <button
                                                type="button"
                                                class="btn btn-success px-4 btn-success-dark btn-sm"
                                                style="font-size: 15px"
                                                name="nuevo_item"
                                            >
                                                <i
                                                    class="lni lni-circle-plus"
                                                    style="font-size: 15px"
                                                ></i
                                                >Agregar Alumno
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label" for="descripcion"
                                                >Describe la incidencia</label
                                            >
                                            <div
                                                id="descripcion"
                                                name="descripcion"
                                                style="display: none"
                                            ></div>
                                            <div
                                                class="ck ck-reset ck-editor ck-rounded-corners"
                                                role="application"
                                                dir="ltr"
                                                lang="en"
                                                aria-labelledby="ck-editor__label_e1a1aced3214c5e3e67e0155ecb02fc0e"
                                            >
                                                <label
                                                    class="ck ck-label ck-voice-label"
                                                    id="ck-editor__label_e1a1aced3214c5e3e67e0155ecb02fc0e"
                                                    >Rich Text Editor</label
                                                >
                                                <div
                                                    class="ck ck-editor__top ck-reset_all"
                                                    role="presentation"
                                                >
                                                    <div class="ck ck-sticky-panel">
                                                        <div
                                                            class="ck ck-sticky-panel__placeholder"
                                                            style="display: none"
                                                        ></div>
                                                        <div class="ck ck-sticky-panel__content">
                                                            <div
                                                                class="ck ck-toolbar ck-toolbar_grouping"
                                                                role="toolbar"
                                                                aria-label="Editor toolbar"
                                                                tabindex="-1"
                                                            >
                                                                <div class="ck ck-toolbar__items">
                                                                    <button
                                                                        class="ck ck-button ck-disabled ck-off"
                                                                        type="button"
                                                                        tabindex="-1"
                                                                        aria-labelledby="ck-editor__aria-label_ed6fd25bcb25deea6a25d862109e34da1"
                                                                        aria-disabled="true"
                                                                        data-cke-tooltip-text="Undo (Ctrl+Z)"
                                                                        data-cke-tooltip-position="s"
                                                                    >
                                                                        <svg
                                                                            class="ck ck-icon ck-reset_all-excluded ck-icon_inherit-color ck-button__icon"
                                                                            viewBox="0 0 20 20"
                                                                        >
                                                                            <path
                                                                                d="m5.042 9.367 2.189 1.837a.75.75 0 0 1-.965 1.149l-3.788-3.18a.747.747 0 0 1-.21-.284.75.75 0 0 1 .17-.945L6.23 4.762a.75.75 0 1 1 .964 1.15L4.863 7.866h8.917A.75.75 0 0 1 14 7.9a4 4 0 1 1-1.477 7.718l.344-1.489a2.5 2.5 0 1 0 1.094-4.73l.008-.032H5.042z"
                                                                            ></path></svg
                                                                        ><span
                                                                            class="ck ck-button__label"
                                                                            id="ck-editor__aria-label_ed6fd25bcb25deea6a25d862109e34da1"
                                                                            >Undo</span
                                                                        ></button
                                                                    ><button
                                                                        class="ck ck-button ck-disabled ck-off"
                                                                        type="button"
                                                                        tabindex="-1"
                                                                        aria-labelledby="ck-editor__aria-label_e44d85f09755ed5a27d43c0b91aaf55ef"
                                                                        aria-disabled="true"
                                                                        data-cke-tooltip-text="Redo (Ctrl+Y)"
                                                                        data-cke-tooltip-position="s"
                                                                    >
                                                                        <svg
                                                                            class="ck ck-icon ck-reset_all-excluded ck-icon_inherit-color ck-button__icon"
                                                                            viewBox="0 0 20 20"
                                                                        >
                                                                            <path
                                                                                d="m14.958 9.367-2.189 1.837a.75.75 0 0 0 .965 1.149l3.788-3.18a.747.747 0 0 0 .21-.284.75.75 0 0 0-.17-.945L13.77 4.762a.75.75 0 1 0-.964 1.15l2.331 1.955H6.22A.75.75 0 0 0 6 7.9a4 4 0 1 0 1.477 7.718l-.344-1.489A2.5 2.5 0 1 1 6.039 9.4l-.008-.032h8.927z"
                                                                            ></path></svg
                                                                        ><span
                                                                            class="ck ck-button__label"
                                                                            id="ck-editor__aria-label_e44d85f09755ed5a27d43c0b91aaf55ef"
                                                                            >Redo</span
                                                                        ></button
                                                                    ><span
                                                                        class="ck ck-toolbar__separator"
                                                                    ></span>
                                                                    <div
                                                                        class="ck ck-dropdown ck-heading-dropdown"
                                                                    >
                                                                        <button
                                                                            class="ck ck-button ck-off ck-button_with-text ck-dropdown__button"
                                                                            type="button"
                                                                            tabindex="-1"
                                                                            aria-label="Heading"
                                                                            data-cke-tooltip-text="Heading"
                                                                            data-cke-tooltip-position="s"
                                                                            aria-haspopup="true"
                                                                            aria-expanded="false"
                                                                        >
                                                                            <span
                                                                                class="ck ck-button__label"
                                                                                >Paragraph</span
                                                                            ><svg
                                                                                class="ck ck-icon ck-reset_all-excluded ck-icon_inherit-color ck-dropdown__arrow"
                                                                                viewBox="0 0 10 10"
                                                                            >
                                                                                <path
                                                                                    d="M.941 4.523a.75.75 0 1 1 1.06-1.06l3.006 3.005 3.005-3.005a.75.75 0 1 1 1.06 1.06l-3.549 3.55a.75.75 0 0 1-1.168-.136L.941 4.523z"
                                                                                ></path>
                                                                            </svg>
                                                                        </button>
                                                                        <div
                                                                            class="ck ck-reset ck-dropdown__panel ck-dropdown__panel_se"
                                                                            tabindex="-1"
                                                                        ></div>
                                                                    </div>
                                                                    <span
                                                                        class="ck ck-toolbar__separator"
                                                                    ></span
                                                                    ><button
                                                                        class="ck ck-button ck-off"
                                                                        type="button"
                                                                        tabindex="-1"
                                                                        aria-labelledby="ck-editor__aria-label_e1338a12265ffc4f32026f1c6827a4d43"
                                                                        aria-pressed="false"
                                                                        data-cke-tooltip-text="Bold (Ctrl+B)"
                                                                        data-cke-tooltip-position="s"
                                                                    >
                                                                        <svg
                                                                            class="ck ck-icon ck-reset_all-excluded ck-icon_inherit-color ck-button__icon"
                                                                            viewBox="0 0 20 20"
                                                                        >
                                                                            <path
                                                                                d="M10.187 17H5.773c-.637 0-1.092-.138-1.364-.415-.273-.277-.409-.718-.409-1.323V4.738c0-.617.14-1.062.419-1.332.279-.27.73-.406 1.354-.406h4.68c.69 0 1.288.041 1.793.124.506.083.96.242 1.36.478.341.197.644.447.906.75a3.262 3.262 0 0 1 .808 2.162c0 1.401-.722 2.426-2.167 3.075C15.05 10.175 16 11.315 16 13.01a3.756 3.756 0 0 1-2.296 3.504 6.1 6.1 0 0 1-1.517.377c-.571.073-1.238.11-2 .11zm-.217-6.217H7v4.087h3.069c1.977 0 2.965-.69 2.965-2.072 0-.707-.256-1.22-.768-1.537-.512-.319-1.277-.478-2.296-.478zM7 5.13v3.619h2.606c.729 0 1.292-.067 1.69-.2a1.6 1.6 0 0 0 .91-.765c.165-.267.247-.566.247-.897 0-.707-.26-1.176-.778-1.409-.519-.232-1.31-.348-2.375-.348H7z"
                                                                            ></path></svg
                                                                        ><span
                                                                            class="ck ck-button__label"
                                                                            id="ck-editor__aria-label_e1338a12265ffc4f32026f1c6827a4d43"
                                                                            >Bold</span
                                                                        ></button
                                                                    ><button
                                                                        class="ck ck-button ck-off"
                                                                        type="button"
                                                                        tabindex="-1"
                                                                        aria-labelledby="ck-editor__aria-label_e8fc1eb10c0c2bd67f0a45abe0915fe76"
                                                                        aria-pressed="false"
                                                                        data-cke-tooltip-text="Italic (Ctrl+I)"
                                                                        data-cke-tooltip-position="s"
                                                                    >
                                                                        <svg
                                                                            class="ck ck-icon ck-reset_all-excluded ck-icon_inherit-color ck-button__icon"
                                                                            viewBox="0 0 20 20"
                                                                        >
                                                                            <path
                                                                                d="m9.586 14.633.021.004c-.036.335.095.655.393.962.082.083.173.15.274.201h1.474a.6.6 0 1 1 0 1.2H5.304a.6.6 0 0 1 0-1.2h1.15c.474-.07.809-.182 1.005-.334.157-.122.291-.32.404-.597l2.416-9.55a1.053 1.053 0 0 0-.281-.823 1.12 1.12 0 0 0-.442-.296H8.15a.6.6 0 0 1 0-1.2h6.443a.6.6 0 1 1 0 1.2h-1.195c-.376.056-.65.155-.823.296-.215.175-.423.439-.623.79l-2.366 9.347z"
                                                                            ></path></svg
                                                                        ><span
                                                                            class="ck ck-button__label"
                                                                            id="ck-editor__aria-label_e8fc1eb10c0c2bd67f0a45abe0915fe76"
                                                                            >Italic</span
                                                                        ></button
                                                                    ><span
                                                                        class="ck ck-toolbar__separator"
                                                                    ></span
                                                                    ><button
                                                                        class="ck ck-button ck-off"
                                                                        type="button"
                                                                        tabindex="-1"
                                                                        aria-labelledby="ck-editor__aria-label_e6462b7b38f796755f3192f953b38d678"
                                                                        aria-pressed="false"
                                                                        data-cke-tooltip-text="Link (Ctrl+K)"
                                                                        data-cke-tooltip-position="s"
                                                                    >
                                                                        <svg
                                                                            class="ck ck-icon ck-reset_all-excluded ck-icon_inherit-color ck-button__icon"
                                                                            viewBox="0 0 20 20"
                                                                        >
                                                                            <path
                                                                                d="m11.077 15 .991-1.416a.75.75 0 1 1 1.229.86l-1.148 1.64a.748.748 0 0 1-.217.206 5.251 5.251 0 0 1-8.503-5.955.741.741 0 0 1 .12-.274l1.147-1.639a.75.75 0 1 1 1.228.86L4.933 10.7l.006.003a3.75 3.75 0 0 0 6.132 4.294l.006.004zm5.494-5.335a.748.748 0 0 1-.12.274l-1.147 1.639a.75.75 0 1 1-1.228-.86l.86-1.23a3.75 3.75 0 0 0-6.144-4.301l-.86 1.229a.75.75 0 0 1-1.229-.86l1.148-1.64a.748.748 0 0 1 .217-.206 5.251 5.251 0 0 1 8.503 5.955zm-4.563-2.532a.75.75 0 0 1 .184 1.045l-3.155 4.505a.75.75 0 1 1-1.229-.86l3.155-4.506a.75.75 0 0 1 1.045-.184z"
                                                                            ></path></svg
                                                                        ><span
                                                                            class="ck ck-button__label"
                                                                            id="ck-editor__aria-label_e6462b7b38f796755f3192f953b38d678"
                                                                            >Link</span
                                                                        ></button
                                                                    ><button
                                                                        class="ck ck-button ck-off ck-file-dialog-button"
                                                                        type="button"
                                                                        tabindex="-1"
                                                                        aria-labelledby="ck-editor__aria-label_e7cb5b5624eb77d4b8d33b6a8b7e41802"
                                                                        data-cke-tooltip-text="Upload image from computer"
                                                                        data-cke-tooltip-position="s"
                                                                    >
                                                                        <svg
                                                                            class="ck ck-icon ck-reset_all-excluded ck-icon_inherit-color ck-button__icon"
                                                                            viewBox="0 0 20 20"
                                                                        >
                                                                            <path
                                                                                d="M1.201 1C.538 1 0 1.47 0 2.1v14.363c0 .64.534 1.037 1.186 1.037h9.494a2.97 2.97 0 0 1-.414-.287 2.998 2.998 0 0 1-1.055-2.03 3.003 3.003 0 0 1 .693-2.185l.383-.455-.02.018-3.65-3.41a.695.695 0 0 0-.957-.034L1.5 13.6V2.5h15v5.535a2.97 2.97 0 0 1 1.412.932l.088.105V2.1c0-.63-.547-1.1-1.2-1.1H1.202Zm11.713 2.803a2.146 2.146 0 0 0-2.049 1.992 2.14 2.14 0 0 0 1.28 2.096 2.13 2.13 0 0 0 2.644-3.11 2.134 2.134 0 0 0-1.875-.978Z"
                                                                            ></path>
                                                                            <path
                                                                                d="M15.522 19.1a.79.79 0 0 0 .79-.79v-5.373l2.059 2.455a.79.79 0 1 0 1.211-1.015l-3.352-3.995a.79.79 0 0 0-.995-.179.784.784 0 0 0-.299.221l-3.35 3.99a.79.79 0 1 0 1.21 1.017l1.936-2.306v5.185c0 .436.353.79.79.79Z"
                                                                            ></path>
                                                                            <path
                                                                                d="M15.522 19.1a.79.79 0 0 0 .79-.79v-5.373l2.059 2.455a.79.79 0 1 0 1.211-1.015l-3.352-3.995a.79.79 0 0 0-.995-.179.784.784 0 0 0-.299.221l-3.35 3.99a.79.79 0 1 0 1.21 1.017l1.936-2.306v5.185c0 .436.353.79.79.79Z"
                                                                            ></path></svg
                                                                        ><span
                                                                            class="ck ck-button__label"
                                                                            id="ck-editor__aria-label_e7cb5b5624eb77d4b8d33b6a8b7e41802"
                                                                            >Upload image from
                                                                            computer</span
                                                                        ><input
                                                                            class="ck-hidden"
                                                                            type="file"
                                                                            tabindex="-1"
                                                                            accept="image/jpeg,image/png,image/gif,image/bmp,image/webp,image/tiff"
                                                                            multiple="true"
                                                                        />
                                                                    </button>
                                                                    <div class="ck ck-dropdown">
                                                                        <button
                                                                            class="ck ck-button ck-off ck-dropdown__button"
                                                                            type="button"
                                                                            tabindex="-1"
                                                                            aria-labelledby="ck-editor__aria-label_e2f6998c067928d775abd2d54b5e28c4c"
                                                                            data-cke-tooltip-text="Insert table"
                                                                            data-cke-tooltip-position="s"
                                                                            aria-haspopup="true"
                                                                            aria-expanded="false"
                                                                        >
                                                                            <svg
                                                                                class="ck ck-icon ck-reset_all-excluded ck-icon_inherit-color ck-button__icon"
                                                                                viewBox="0 0 20 20"
                                                                            >
                                                                                <path
                                                                                    d="M3 5.5v3h4v-3H3Zm0 4v3h4v-3H3Zm0 4v3h4v-3H3Zm5 3h4v-3H8v3Zm5 0h4v-3h-4v3Zm4-4v-3h-4v3h4Zm0-4v-3h-4v3h4Zm1.5 8A1.5 1.5 0 0 1 17 18H3a1.5 1.5 0 0 1-1.5-1.5V3c.222-.863 1.068-1.5 2-1.5h13c.932 0 1.778.637 2 1.5v13.5Zm-6.5-4v-3H8v3h4Zm0-4v-3H8v3h4Z"
                                                                                ></path></svg
                                                                            ><span
                                                                                class="ck ck-button__label"
                                                                                id="ck-editor__aria-label_e2f6998c067928d775abd2d54b5e28c4c"
                                                                                >Insert table</span
                                                                            ><svg
                                                                                class="ck ck-icon ck-reset_all-excluded ck-icon_inherit-color ck-dropdown__arrow"
                                                                                viewBox="0 0 10 10"
                                                                            >
                                                                                <path
                                                                                    d="M.941 4.523a.75.75 0 1 1 1.06-1.06l3.006 3.005 3.005-3.005a.75.75 0 1 1 1.06 1.06l-3.549 3.55a.75.75 0 0 1-1.168-.136L.941 4.523z"
                                                                                ></path>
                                                                            </svg>
                                                                        </button>
                                                                        <div
                                                                            class="ck ck-reset ck-dropdown__panel ck-dropdown__panel_se"
                                                                            tabindex="-1"
                                                                        ></div>
                                                                    </div>
                                                                    <button
                                                                        class="ck ck-button ck-off"
                                                                        type="button"
                                                                        tabindex="-1"
                                                                        aria-labelledby="ck-editor__aria-label_e8acfdec337c63b76e53171f4b8bf445d"
                                                                        aria-pressed="false"
                                                                        data-cke-tooltip-text="Block quote"
                                                                        data-cke-tooltip-position="s"
                                                                    >
                                                                        <svg
                                                                            class="ck ck-icon ck-reset_all-excluded ck-icon_inherit-color ck-button__icon"
                                                                            viewBox="0 0 20 20"
                                                                        >
                                                                            <path
                                                                                d="M3 10.423a6.5 6.5 0 0 1 6.056-6.408l.038.67C6.448 5.423 5.354 7.663 5.22 10H9c.552 0 .5.432.5.986v4.511c0 .554-.448.503-1 .503h-5c-.552 0-.5-.449-.5-1.003v-4.574zm8 0a6.5 6.5 0 0 1 6.056-6.408l.038.67c-2.646.739-3.74 2.979-3.873 5.315H17c.552 0 .5.432.5.986v4.511c0 .554-.448.503-1 .503h-5c-.552 0-.5-.449-.5-1.003v-4.574z"
                                                                            ></path></svg
                                                                        ><span
                                                                            class="ck ck-button__label"
                                                                            id="ck-editor__aria-label_e8acfdec337c63b76e53171f4b8bf445d"
                                                                            >Block quote</span
                                                                        >
                                                                    </button>
                                                                    <div class="ck ck-dropdown">
                                                                        <button
                                                                            class="ck ck-button ck-off ck-dropdown__button"
                                                                            type="button"
                                                                            tabindex="-1"
                                                                            aria-labelledby="ck-editor__aria-label_e8537ad9e6a900dd534fc52f744748d4e"
                                                                            data-cke-tooltip-text="Insert media"
                                                                            data-cke-tooltip-position="s"
                                                                            aria-haspopup="true"
                                                                            aria-expanded="false"
                                                                        >
                                                                            <svg
                                                                                class="ck ck-icon ck-reset_all-excluded ck-icon_inherit-color ck-button__icon"
                                                                                viewBox="0 0 22 20"
                                                                            >
                                                                                <path
                                                                                    d="M1.587 1.5c-.612 0-.601-.029-.601.551v14.84c0 .59-.01.559.591.559h18.846c.602 0 .591.03.591-.56V2.052c0-.58.01-.55-.591-.55H1.587Zm.701.971h1.003v1H2.288v-1Zm16.448 0h1.003v1h-1.003v-1Zm-14.24 1h13.008v12H4.467l.029-12Zm-2.208 1h1.003v1H2.288v-1Zm16.448 0h1.003v1h-1.003v-1Zm-16.448 2h1.003v1H2.288v-1Zm16.448 0h1.003v1h-1.003v-1Zm-16.448 2h1.003v1H2.288v-1Zm16.448 0h1.003v1h-1.003v-1Zm-16.448 2h1.003v1H2.288v-1Zm16.448 0h1.003v1h-1.003v-1Zm-16.448 2h1.003l-.029 1h-.974v-1Zm16.448 0h1.003v1h-1.003v-1Zm-16.448 2h.974v1h-.974v-1Zm16.448 0h1.003v1h-1.003v-1Z"
                                                                                ></path>
                                                                                <path
                                                                                    d="M8.374 6.648a.399.399 0 0 1 .395-.4.402.402 0 0 1 .2.049l5.148 2.824a.4.4 0 0 1 0 .7l-5.148 2.824a.403.403 0 0 1-.595-.35V6.648Z"
                                                                                ></path></svg
                                                                            ><span
                                                                                class="ck ck-button__label"
                                                                                id="ck-editor__aria-label_e8537ad9e6a900dd534fc52f744748d4e"
                                                                                >Insert media</span
                                                                            ><svg
                                                                                class="ck ck-icon ck-reset_all-excluded ck-icon_inherit-color ck-dropdown__arrow"
                                                                                viewBox="0 0 10 10"
                                                                            >
                                                                                <path
                                                                                    d="M.941 4.523a.75.75 0 1 1 1.06-1.06l3.006 3.005 3.005-3.005a.75.75 0 1 1 1.06 1.06l-3.549 3.55a.75.75 0 0 1-1.168-.136L.941 4.523z"
                                                                                ></path>
                                                                            </svg>
                                                                        </button>
                                                                        <div
                                                                            class="ck ck-reset ck-dropdown__panel ck-dropdown__panel_se"
                                                                            tabindex="-1"
                                                                        ></div>
                                                                    </div>
                                                                    <span
                                                                        class="ck ck-toolbar__separator"
                                                                    ></span
                                                                    ><button
                                                                        class="ck ck-button ck-off"
                                                                        type="button"
                                                                        tabindex="-1"
                                                                        aria-labelledby="ck-editor__aria-label_e5aa397be3fac005cffb54a39dea522ba"
                                                                        aria-pressed="false"
                                                                        data-cke-tooltip-text="Bulleted List"
                                                                        data-cke-tooltip-position="s"
                                                                    >
                                                                        <svg
                                                                            class="ck ck-icon ck-reset_all-excluded ck-icon_inherit-color ck-button__icon"
                                                                            viewBox="0 0 20 20"
                                                                        >
                                                                            <path
                                                                                d="M7 5.75c0 .414.336.75.75.75h9.5a.75.75 0 1 0 0-1.5h-9.5a.75.75 0 0 0-.75.75zm-6 0C1 4.784 1.777 4 2.75 4c.966 0 1.75.777 1.75 1.75 0 .966-.777 1.75-1.75 1.75C1.784 7.5 1 6.723 1 5.75zm6 9c0 .414.336.75.75.75h9.5a.75.75 0 1 0 0-1.5h-9.5a.75.75 0 0 0-.75.75zm-6 0c0-.966.777-1.75 1.75-1.75.966 0 1.75.777 1.75 1.75 0 .966-.777 1.75-1.75 1.75-.966 0-1.75-.777-1.75-1.75z"
                                                                            ></path></svg
                                                                        ><span
                                                                            class="ck ck-button__label"
                                                                            id="ck-editor__aria-label_e5aa397be3fac005cffb54a39dea522ba"
                                                                            >Bulleted List</span
                                                                        ></button
                                                                    ><button
                                                                        class="ck ck-button ck-off"
                                                                        type="button"
                                                                        tabindex="-1"
                                                                        aria-labelledby="ck-editor__aria-label_e4332963d1b3e162a065ab60bdd70baee"
                                                                        aria-pressed="false"
                                                                        data-cke-tooltip-text="Numbered List"
                                                                        data-cke-tooltip-position="s"
                                                                    >
                                                                        <svg
                                                                            class="ck ck-icon ck-reset_all-excluded ck-icon_inherit-color ck-button__icon"
                                                                            viewBox="0 0 20 20"
                                                                        >
                                                                            <path
                                                                                d="M7 5.75c0 .414.336.75.75.75h9.5a.75.75 0 1 0 0-1.5h-9.5a.75.75 0 0 0-.75.75zM3.5 3v5H2V3.7H1v-1h2.5V3zM.343 17.857l2.59-3.257H2.92a.6.6 0 1 0-1.04 0H.302a2 2 0 1 1 3.995 0h-.001c-.048.405-.16.734-.333.988-.175.254-.59.692-1.244 1.312H4.3v1h-4l.043-.043zM7 14.75a.75.75 0 0 1 .75-.75h9.5a.75.75 0 1 1 0 1.5h-9.5a.75.75 0 0 1-.75-.75z"
                                                                            ></path></svg
                                                                        ><span
                                                                            class="ck ck-button__label"
                                                                            id="ck-editor__aria-label_e4332963d1b3e162a065ab60bdd70baee"
                                                                            >Numbered List</span
                                                                        ></button
                                                                    ><button
                                                                        class="ck ck-button ck-disabled ck-off"
                                                                        type="button"
                                                                        tabindex="-1"
                                                                        aria-labelledby="ck-editor__aria-label_e917b66792693ea0fff1eadcf4df8d14b"
                                                                        aria-disabled="true"
                                                                        data-cke-tooltip-text="Decrease indent"
                                                                        data-cke-tooltip-position="s"
                                                                    >
                                                                        <svg
                                                                            class="ck ck-icon ck-reset_all-excluded ck-icon_inherit-color ck-button__icon"
                                                                            viewBox="0 0 20 20"
                                                                        >
                                                                            <path
                                                                                d="M2 3.75c0 .414.336.75.75.75h14.5a.75.75 0 1 0 0-1.5H2.75a.75.75 0 0 0-.75.75zm5 6c0 .414.336.75.75.75h9.5a.75.75 0 1 0 0-1.5h-9.5a.75.75 0 0 0-.75.75zM2.75 16.5h14.5a.75.75 0 1 0 0-1.5H2.75a.75.75 0 1 0 0 1.5zm1.618-9.55L.98 9.358a.4.4 0 0 0 .013.661l3.39 2.207A.4.4 0 0 0 5 11.892V7.275a.4.4 0 0 0-.632-.326z"
                                                                            ></path></svg
                                                                        ><span
                                                                            class="ck ck-button__label"
                                                                            id="ck-editor__aria-label_e917b66792693ea0fff1eadcf4df8d14b"
                                                                            >Decrease indent</span
                                                                        ></button
                                                                    ><button
                                                                        class="ck ck-button ck-disabled ck-off"
                                                                        type="button"
                                                                        tabindex="-1"
                                                                        aria-labelledby="ck-editor__aria-label_ee33bf7821452a46d3edfa2dfc08e1c69"
                                                                        aria-disabled="true"
                                                                        data-cke-tooltip-text="Increase indent"
                                                                        data-cke-tooltip-position="s"
                                                                    >
                                                                        <svg
                                                                            class="ck ck-icon ck-reset_all-excluded ck-icon_inherit-color ck-button__icon"
                                                                            viewBox="0 0 20 20"
                                                                        >
                                                                            <path
                                                                                d="M2 3.75c0 .414.336.75.75.75h14.5a.75.75 0 1 0 0-1.5H2.75a.75.75 0 0 0-.75.75zm5 6c0 .414.336.75.75.75h9.5a.75.75 0 1 0 0-1.5h-9.5a.75.75 0 0 0-.75.75zM2.75 16.5h14.5a.75.75 0 1 0 0-1.5H2.75a.75.75 0 1 0 0 1.5zM1.632 6.95 5.02 9.358a.4.4 0 0 1-.013.661l-3.39 2.207A.4.4 0 0 1 1 11.892V7.275a.4.4 0 0 1 .632-.326z"
                                                                            ></path></svg
                                                                        ><span
                                                                            class="ck ck-button__label"
                                                                            id="ck-editor__aria-label_ee33bf7821452a46d3edfa2dfc08e1c69"
                                                                            >Increase indent</span
                                                                        >
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="ck ck-editor__main" role="presentation">
                                                    <div
                                                        class="ck-blurred ck ck-content ck-editor__editable ck-rounded-corners ck-editor__editable_inline"
                                                        lang="en"
                                                        dir="ltr"
                                                        role="textbox"
                                                        aria-label="Editor editing area: main"
                                                        contenteditable="true"
                                                    >
                                                        <p><br data-cke-filler="true" /></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <foto-component>
                                            <table
                                                class="table"
                                                style="width: 100%; margin-top: 20px"
                                            >
                                                <thead>
                                                    <tr>
                                                        <th>FOTO</th>
                                                        <th></th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody name="detalle_foto"></tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th style="text-align: center" colspan="12">
                                                            <button
                                                                type="button"
                                                                name="btn-agregar_foto"
                                                                class="btn btn-success btn-sm"
                                                                style="width: 100%"
                                                            >
                                                                <i class="fa fa-plus"></i> Agregar
                                                                Nuevo Img
                                                            </button>
                                                        </th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </foto-component>
                                    </div>
                                </div>

                                <div class="modal-footer" align="center" style="display: block">
                                    <button
                                        type="submit"
                                        class="btn btn-primary btn-primary-dark btn-sm"
                                        style="width: 100%"
                                    >
                                        Guardar
                                    </button>
                                </div>
                            </form>
                            <!-- </div>        -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Estilos para la vista de nueva incidencia */
</style>
