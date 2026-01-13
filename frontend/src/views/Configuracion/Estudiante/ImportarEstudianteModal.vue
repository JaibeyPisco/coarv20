<script setup lang="ts">
import { ref } from 'vue';
import AppModal from '@/components/Partial/AppModal.vue';
import apiClient from '@/api/axios';
import { notificacion } from '@/utils/notificacion';

interface Props {
    open: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    close: [];
    imported: [];
}>();

const fileInput = ref<HTMLInputElement | null>(null);
const importing = ref(false);

function handleFileChange(event: Event) {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        const extension = file.name.split('.').pop()?.toLowerCase();
        if (extension !== 'xlsx' && extension !== 'xls') {
            notificacion('El archivo debe ser Excel (.xlsx o .xls)', {
                type: 'warning',
                title: 'Formato inválido',
            });
            if (fileInput.value) {
                fileInput.value.value = '';
            }
        }
    }
}

async function handleSubmit() {
    if (!fileInput.value?.files?.[0]) {
        notificacion('Por favor, selecciona un archivo Excel.', {
            type: 'warning',
            title: 'Archivo requerido',
        });
        return;
    }

    const file = fileInput.value.files[0];
    const extension = file.name.split('.').pop()?.toLowerCase();
    if (extension !== 'xlsx' && extension !== 'xls') {
        notificacion('El archivo debe ser Excel (.xlsx o .xls)', {
            type: 'warning',
            title: 'Formato inválido',
        });
        return;
    }

    importing.value = true;

    try {
        const formData = new FormData();
        formData.append('fileexportar', file);

        await apiClient.post('/configuracion/estudiante/importar', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        notificacion('Estudiantes importados correctamente.', {
            type: 'success',
            title: 'Importación exitosa',
        });

        emit('imported');
        closeModal();
    } catch (error: any) {
        const message =
            error.response?.data?.message || 'Ocurrió un inconveniente al importar los estudiantes.';
        notificacion(message, { type: 'danger', title: 'Error' });
    } finally {
        importing.value = false;
    }
}

function closeModal() {
    if (importing.value) return;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
    emit('close');
}
</script>

<template>
    <AppModal :open="open" title="Importar estudiantes" size="md" @close="closeModal">
        <template #body>
            <form @submit.prevent="handleSubmit">
                <div class="row">
                    <div class="col-md-12 mb-4" align="center">
                        <i class="ti ti-cloud-upload text-primary" style="font-size: 100px"></i>
                        <br />
                        <a
                            class="btn btn-outline-primary px-5 mt-3"
                            href="/formatos/formato_exportacion_estudiantes.xlsx"
                            target="_blank"
                        >
                            <i class="ti ti-download"></i>
                            Descargar formato
                        </a>
                    </div>
                    <div class="col-md-12" align="center" style="padding-top: 10px">
                        <input
                            ref="fileInput"
                            type="file"
                            accept=".xlsx,.xls"
                            class="form-control"
                            @change="handleFileChange"
                        />
                    </div>
                </div>
            </form>
        </template>
        <template #footer>
            <div class="d-flex justify-content-between w-100">
                <button
                    type="button"
                    class="btn btn-default btn-sm pull-left"
                    @click="closeModal"
                    :disabled="importing"
                >
                    <i class="fa fa-times"></i> Cerrar
                </button>
                <button
                    type="button"
                    class="btn btn-success btn-sm"
                    :disabled="importing"
                    @click="handleSubmit"
                >
                    <span v-if="importing" class="spinner-border spinner-border-sm me-2"></span>
                    Procesar
                </button>
            </div>
        </template>
    </AppModal>
</template>

