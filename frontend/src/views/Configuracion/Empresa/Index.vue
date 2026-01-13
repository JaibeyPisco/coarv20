<script setup lang="ts">
import AuthenticatedLayout from '@/components/Layouts/AuthenticatedLayout.vue';
import { ref, reactive, onMounted, computed } from 'vue';
import apiClient from '@/api/axios';
import { notificacion } from '@/utils/notificacion';
import { useImageUpload } from '@/composables/useImageUpload';

interface Empresa {
    id: number;
    numero_documento: string;
    razon_social: string;
    nombre_comercial: string;
    direccion: string;
    telefono: string;
    email: string;
    logo: string | null;
    logo_factura: string | null;
    logo_url: string | null;
    logo_factura_url: string | null;
}

const loading = ref(false);
const saving = ref(false);

const empresaForm = reactive({
    numero_documento: '',
    razon_social: '',
    nombre_comercial: '',
    direccion: '',
    telefono: '',
    email: '',
});

const logoUpload = useImageUpload();
const logoFacturaUpload = useImageUpload();
const logoAnterior = ref<string | null>(null);
const logoFacturaAnterior = ref<string | null>(null);

// Computed properties para el template
const logoPreview = computed(() => logoUpload.preview.value);
const logoFacturaPreview = computed(() => logoFacturaUpload.preview.value);

async function loadEmpresa() {
    loading.value = true;
    try {
        const response = await apiClient.get('/configuracion/empresa');
        const empresa: Empresa = response.data.data;

        empresaForm.numero_documento = empresa.numero_documento || '';
        empresaForm.razon_social = empresa.razon_social || '';
        empresaForm.nombre_comercial = empresa.nombre_comercial || '';
        empresaForm.direccion = empresa.direccion || '';
        empresaForm.telefono = empresa.telefono || '';
        empresaForm.email = empresa.email || '';

        // Cargar previews de imágenes si existen
        if (empresa.logo_url) {
            logoUpload.setPreview(empresa.logo_url);
        } else if (empresa.logo) {
            logoUpload.setPreview(`/storage/empresas/${empresa.logo}`);
        } else {
            logoUpload.setPreview('/images/sin_imagen.jpg');
        }
        
        if (empresa.logo_factura_url) {
            logoFacturaUpload.setPreview(empresa.logo_factura_url);
        } else if (empresa.logo_factura) {
            logoFacturaUpload.setPreview(`/storage/empresas/${empresa.logo_factura}`);
        } else {
            logoFacturaUpload.setPreview('/images/sin_imagen.jpg');
        }
        
        logoAnterior.value = empresa.logo;
        logoFacturaAnterior.value = empresa.logo_factura;
    } catch (error: any) {
        // El error ya se muestra automáticamente en el interceptor de axios
        console.error('Error cargando empresa:', error);
    } finally {
        loading.value = false;
    }
}

async function handleSubmit() {
    saving.value = true;
    try {
        const formData = new FormData();
        formData.append('numero_documento', empresaForm.numero_documento);
        formData.append('razon_social', empresaForm.razon_social);
        formData.append('nombre_comercial', empresaForm.nombre_comercial);
        formData.append('direccion', empresaForm.direccion);
        formData.append('telefono', empresaForm.telefono);
        formData.append('email', empresaForm.email);

        if (logoUpload.file.value) {
            formData.append('logo', logoUpload.file.value);
        }
        if (logoFacturaUpload.file.value) {
            formData.append('logo_factura', logoFacturaUpload.file.value);
        }
        if (logoAnterior.value) {
            formData.append('logo_anterior', logoAnterior.value);
        }
        if (logoFacturaAnterior.value) {
            formData.append('logo_factura_anterior', logoFacturaAnterior.value);
        }

        const response = await apiClient.post('/configuracion/empresa', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        notificacion(response.data.message || 'Empresa actualizada correctamente.', {
            type: 'success',
            title: 'Éxito',
        });

        // Actualizar previews con los nuevos datos si hay respuesta
        if (response.data.data) {
            const empresaData = response.data.data;
            if (empresaData.logo_url) {
                logoUpload.setPreview(empresaData.logo_url);
            } else if (empresaData.logo) {
                logoUpload.setPreview(`/storage/empresas/${empresaData.logo}`);
            }
            if (empresaData.logo_factura_url) {
                logoFacturaUpload.setPreview(empresaData.logo_factura_url);
            } else if (empresaData.logo_factura) {
                logoFacturaUpload.setPreview(`/storage/empresas/${empresaData.logo_factura}`);
            }
            logoAnterior.value = empresaData.logo;
            logoFacturaAnterior.value = empresaData.logo_factura;
        }

        // Limpiar archivos seleccionados pero mantener previews
        logoUpload.file.value = null;
        logoFacturaUpload.file.value = null;
    } catch (error: any) {
        // El error ya se muestra automáticamente en el interceptor de axios
        console.error('Error actualizando empresa:', error);
    } finally {
        saving.value = false;
    }
}

onMounted(() => {
    loadEmpresa();
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="h2 mb-1">Configuración / Empresa</h1>
                    <p class="text-secondary mb-0">
                        Gestiona la información de la empresa.
                    </p>
                </div>
            </div>
        </template>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Datos de la Empresa</h3>
                    </div>
                    <div class="card-body" v-if="!loading">
                        <form @submit.prevent="handleSubmit">
                            <div class="row">
                                <div class="col-md-3" align="center">
                                    <div class="row">
                                        <div class="col-md-12 mb-3" align="center">
                                            <div>
                                                <img
                                                    :key="`logo-${logoPreview}`"
                                                    :src="logoPreview || '/images/sin_imagen.jpg'"
                                                    alt="Logo"
                                                    class="img-fluid rounded"
                                                    style="max-width: 100%; max-height: 200px; object-fit: cover; border: 1px solid #dee2e6;"
                                                    @error="(e) => { 
                                                        const img = e.target as HTMLImageElement;
                                                        if (img.src && !img.src.includes('data:') && img.src !== 'data:image/svg+xml') {
                                                            img.src = 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'200\' height=\'200\'%3E%3Crect fill=\'%23ddd\' width=\'200\' height=\'200\'/%3E%3Ctext fill=\'%23999\' font-family=\'sans-serif\' font-size=\'14\' dy=\'10.5\' font-weight=\'bold\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\'%3ESin Imagen%3C/text%3E%3C/svg%3E';
                                                        }
                                                    }"
                                                />
                                            </div>
                                            <div class="mt-2">
                                                <label
                                                    class="btn btn-default btn-sm"
                                                    style="width: 100%"
                                                >
                                                    <i class="ti ti-search me-1"></i>
                                                    Examinar Logo
                                                    <input
                                                        type="file"
                                                        accept="image/*"
                                                        style="display: none"
                                                        @change="logoUpload.handleChange"
                                                    />
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-12 mb-3" align="center">
                                            <div>
                                                <img
                                                    :key="`logo-factura-${logoFacturaPreview}`"
                                                    :src="logoFacturaPreview || '/images/sin_imagen.jpg'"
                                                    alt="Logo Factura"
                                                    class="img-fluid rounded"
                                                    style="max-width: 100%; max-height: 200px; object-fit: cover; border: 1px solid #dee2e6;"
                                                    @error="(e) => { 
                                                        const img = e.target as HTMLImageElement;
                                                        if (img.src && !img.src.includes('data:') && img.src !== 'data:image/svg+xml') {
                                                            img.src = 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'200\' height=\'200\'%3E%3Crect fill=\'%23ddd\' width=\'200\' height=\'200\'/%3E%3Ctext fill=\'%23999\' font-family=\'sans-serif\' font-size=\'14\' dy=\'10.5\' font-weight=\'bold\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\'%3ESin Imagen%3C/text%3E%3C/svg%3E';
                                                        }
                                                    }"
                                                />
                                            </div>
                                            <div class="mt-2">
                                                <label
                                                    class="btn btn-default btn-sm"
                                                    style="width: 100%"
                                                >
                                                    <i class="ti ti-search me-1"></i>
                                                    Logo para documentos
                                                    <input
                                                        type="file"
                                                        accept="image/*"
                                                        style="display: none"
                                                        @change="logoFacturaUpload.handleChange"
                                                    />
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label required">Número RUC</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="ti ti-file-text"></i>
                                                    </span>
                                                    <input
                                                        v-model="empresaForm.numero_documento"
                                                        type="text"
                                                        class="form-control"
                                                        required
                                                        maxlength="20"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <label class="form-label required">Razón Social</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="ti ti-building"></i>
                                                    </span>
                                                    <input
                                                        v-model="empresaForm.razon_social"
                                                        type="text"
                                                        class="form-control"
                                                        required
                                                        maxlength="200"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label required">Nombre Comercial</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="ti ti-tag"></i>
                                                    </span>
                                                    <input
                                                        v-model="empresaForm.nombre_comercial"
                                                        type="text"
                                                        class="form-control"
                                                        required
                                                        maxlength="200"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label required">Dirección</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="ti ti-home"></i>
                                                    </span>
                                                    <input
                                                        v-model="empresaForm.direccion"
                                                        type="text"
                                                        class="form-control"
                                                        required
                                                        maxlength="200"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label required">Teléfono</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="ti ti-phone"></i>
                                                    </span>
                                                    <input
                                                        v-model="empresaForm.telefono"
                                                        type="text"
                                                        class="form-control"
                                                        required
                                                        maxlength="20"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label required">Correo electrónico</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="ti ti-mail"></i>
                                                    </span>
                                                    <input
                                                        v-model="empresaForm.email"
                                                        type="email"
                                                        class="form-control"
                                                        required
                                                        maxlength="100"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-end justify-content-end">
                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                    :disabled="saving"
                                >
                                    <span
                                        v-if="saving"
                                        class="spinner-border spinner-border-sm me-2"
                                        role="status"
                                        aria-hidden="true"
                                    ></span>
                                    <i v-else class="ti ti-device-floppy me-2"></i>
                                    {{ saving ? 'Guardando...' : 'Guardar' }}
                                </button>
                            </div>
                        </form>
                    </div>
                    <div v-else class="card-body text-center py-5">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
