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
        <v-container fluid class="pa-4">
            <!-- Header Section -->
            <v-card class="mb-4" rounded="lg" elevation="1">
                <v-card-text class="pa-4">
                    <div class="d-flex flex-wrap align-center justify-space-between ga-4">
                        <div>
                            <h1 class="text-h5 font-weight-bold mb-2">Empresa</h1>
                            <p class="text-body-2 text-medium-emphasis mb-0">
                                Gestiona la información de la empresa.
                            </p>
                        </div>
                    </div>
                </v-card-text>
            </v-card>

            <!-- Main Content Card -->
            <v-card rounded="lg" elevation="2">
                <!-- Header con título y divider -->
                <v-card-title class="pa-4">
                    <span class="text-h6 font-weight-medium">Datos de la Empresa</span>
                </v-card-title>
                <v-divider />

                <!-- Loading State -->
                <v-card-text v-if="loading" class="text-center py-12">
                    <v-progress-circular
                        indeterminate
                        color="primary"
                        size="64"
                    />
                    <p class="mt-4 mb-0 text-body-2 text-medium-emphasis">Cargando...</p>
                </v-card-text>

                <!-- Form Content -->
                <v-card-text v-else class="pa-4">
                    <v-form @submit.prevent="handleSubmit">
                        <v-row>
                            <!-- Sección de Logos -->
                            <v-col cols="12" md="4">
                                <v-sheet
                                    variant="outlined"
                                    class="pa-4"
                                    rounded="md"
                                    color="surface"
                                >
                                    <label class="text-body-2 font-weight-medium mb-3 d-block">Logos de la Empresa</label>
                                    
                                    <!-- Logo Principal -->
                                    <div class="mb-6">
                                        <label class="text-caption text-medium-emphasis mb-2 d-block">Logo Principal</label>
                                        <div class="text-center mb-3">
                                            <v-img
                                                :key="`logo-${logoPreview}`"
                                                :src="logoPreview || '/images/sin_imagen.jpg'"
                                                alt="Logo"
                                                max-height="180"
                                                max-width="180"
                                                contain
                                                class="mx-auto"
                                                rounded="md"
                                                style="border: 1px solid rgba(0,0,0,0.12);"
                                            />
                                        </div>
                                        <v-file-input
                                            label="Examinar Logo"
                                            prepend-icon="mdi-image"
                                            accept="image/*"
                                            density="compact"
                                            variant="outlined"
                                            hide-details
                                            @change="logoUpload.handleChange"
                                        />
                                    </div>

                                    <v-divider class="my-4" />

                                    <!-- Logo para Facturas -->
                                    <div>
                                        <label class="text-caption text-medium-emphasis mb-2 d-block">Logo para Documentos</label>
                                        <div class="text-center mb-3">
                                            <v-img
                                                :key="`logo-factura-${logoFacturaPreview}`"
                                                :src="logoFacturaPreview || '/images/sin_imagen.jpg'"
                                                alt="Logo Factura"
                                                max-height="180"
                                                max-width="180"
                                                contain
                                                class="mx-auto"
                                                rounded="md"
                                                style="border: 1px solid rgba(0,0,0,0.12);"
                                            />
                                        </div>
                                        <v-file-input
                                            label="Examinar Logo"
                                            prepend-icon="mdi-image"
                                            accept="image/*"
                                            density="compact"
                                            variant="outlined"
                                            hide-details
                                            @change="logoFacturaUpload.handleChange"
                                        />
                                    </div>
                                </v-sheet>
                            </v-col>

                            <!-- Formulario de Datos -->
                            <v-col cols="12" md="8">
                                <v-row>
                                    <!-- Número RUC -->
                                    <v-col cols="12" md="4">
                                        <v-text-field
                                            v-model="empresaForm.numero_documento"
                                            label="Número RUC"
                                            prepend-inner-icon="mdi-file-document"
                                            :rules="[v => !!v || 'El RUC es obligatorio']"
                                            maxlength="20"
                                            required
                                            variant="outlined"
                                            density="compact"
                                        />
                                    </v-col>

                                    <!-- Razón Social -->
                                    <v-col cols="12" md="8">
                                        <v-text-field
                                            v-model="empresaForm.razon_social"
                                            label="Razón Social"
                                            prepend-inner-icon="mdi-office-building"
                                            :rules="[v => !!v || 'La razón social es obligatoria']"
                                            maxlength="200"
                                            required
                                            variant="outlined"
                                            density="compact"
                                        />
                                    </v-col>

                                    <!-- Nombre Comercial -->
                                    <v-col cols="12">
                                        <v-text-field
                                            v-model="empresaForm.nombre_comercial"
                                            label="Nombre Comercial"
                                            prepend-inner-icon="mdi-tag"
                                            :rules="[v => !!v || 'El nombre comercial es obligatorio']"
                                            maxlength="200"
                                            required
                                            variant="outlined"
                                            density="compact"
                                        />
                                    </v-col>

                                    <!-- Dirección -->
                                    <v-col cols="12">
                                        <v-text-field
                                            v-model="empresaForm.direccion"
                                            label="Dirección"
                                            prepend-inner-icon="mdi-home"
                                            :rules="[v => !!v || 'La dirección es obligatoria']"
                                            maxlength="200"
                                            required
                                            variant="outlined"
                                            density="compact"
                                        />
                                    </v-col>

                                    <!-- Teléfono -->
                                    <v-col cols="12" md="6">
                                        <v-text-field
                                            v-model="empresaForm.telefono"
                                            label="Teléfono"
                                            prepend-inner-icon="mdi-phone"
                                            :rules="[v => !!v || 'El teléfono es obligatorio']"
                                            maxlength="20"
                                            required
                                            variant="outlined"
                                            density="compact"
                                        />
                                    </v-col>

                                    <!-- Correo Electrónico -->
                                    <v-col cols="12" md="6">
                                        <v-text-field
                                            v-model="empresaForm.email"
                                            label="Correo Electrónico"
                                            prepend-inner-icon="mdi-email"
                                            type="email"
                                            :rules="[
                                                v => !!v || 'El correo es obligatorio',
                                                v => /.+@.+\..+/.test(v) || 'El correo debe ser válido'
                                            ]"
                                            maxlength="100"
                                            required
                                            variant="outlined"
                                            density="compact"
                                        />
                                    </v-col>
                                </v-row>
                            </v-col>
                        </v-row>

                        <!-- Divider antes del footer -->
                        <v-divider class="my-4" />

                        <!-- Footer con botones -->
                        <div class="d-flex justify-end ga-3">
                            <v-btn
                                type="submit"
                                color="primary"
                                variant="tonal"
                                :loading="saving"
                                :disabled="saving"
                                prepend-icon="mdi-content-save"
                                class="text-none"
                            >
                                {{ saving ? 'Guardando...' : 'Guardar' }}
                            </v-btn>
                        </div>
                    </v-form>
                </v-card-text>
            </v-card>
        </v-container>
    </AuthenticatedLayout>
</template>
