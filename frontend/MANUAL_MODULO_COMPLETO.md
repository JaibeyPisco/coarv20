# 📘 Manual Completo: Creación de Módulo con Tabla y Modales

**Versión**: 1.0  
**Framework**: Vue 3 + Vuetify 3 + TypeScript  
**Patrón**: Material Design 3

---

## 📋 Índice

1. [Estructura del Módulo](#estructura-del-módulo)
2. [Paso 1: Configurar Tipos TypeScript](#paso-1-configurar-tipos-typescript)
3. [Paso 2: Crear el Componente Principal](#paso-2-crear-el-componente-principal)
4. [Paso 3: Configurar la Tabla](#paso-3-configurar-la-tabla)
5. [Paso 4: Configurar Modales CRUD](#paso-4-configurar-modales-crud)
6. [Paso 5: Implementar Funciones de API](#paso-5-implementar-funciones-de-api)
7. [Paso 6: Diseño Material Design 3](#paso-6-diseño-material-design-3)
8. [Ejemplo Completo](#ejemplo-completo)
9. [Checklist de Verificación](#checklist-de-verificación)

---

## 🏗️ Estructura del Módulo

```
frontend/src/views/Configuracion/
└── MiModulo/
    └── Index.vue          # Componente principal con tabla y modales
```

**Archivos relacionados:**
- `frontend/src/types/configuracion/index.ts` - Tipos TypeScript
- `frontend/src/composables/useVuetifyTable.ts` - Composable para tablas
- `frontend/src/composables/useCrudModal.ts` - Composable para modales CRUD
- `frontend/src/components/Partial/AppModal.vue` - Componente modal base
- `frontend/src/components/Table/VDataTableCard.vue` - Wrapper de tabla

---

## 📝 Paso 1: Configurar Tipos TypeScript

### 1.1 Definir la Entidad Principal

En `frontend/src/types/configuracion/index.ts`:

```typescript
// Entidad principal
export interface MiModulo {
    id: number;
    nombre: string;
    descripcion?: string;
    estado: number;
    created_at?: string;
    updated_at?: string;
}

// DTOs para crear y actualizar
export interface CreateMiModuloDto {
    nombre: string;
    descripcion?: string;
}

export interface UpdateMiModuloDto extends CreateMiModuloDto {
    id: number;
}
```

---

## 🎯 Paso 2: Crear el Componente Principal

### 2.1 Estructura Básica del Script

```vue
<script setup lang="ts">
import AuthenticatedLayout from '@/components/Layouts/AuthenticatedLayout.vue';
import { reactive, onMounted } from 'vue';
import * as XLSX from 'xlsx';
import type { MiModulo, CreateMiModuloDto, UpdateMiModuloDto } from '@/types/configuracion';
import AppModal from '@/components/Partial/AppModal.vue';
import VDataTableCard from '@/components/Table/VDataTableCard.vue';
import { useVuetifyTable } from '@/composables/useVuetifyTable';
import { useCrudModal } from '@/composables/useCrudModal';
import apiClient from '@/api/axios';
import { formatStatusChip } from '@/utils/vuetifyTableHelpers';

// Form reactivo
const saveForm = reactive({
    nombre: '',
    descripcion: '',
});

// Headers de la tabla
const headers = [
    {
        title: 'ACCIONES',
        key: 'actions',
        sortable: false,
        width: '150px',
    },
    {
        title: 'NOMBRE',
        key: 'nombre',
        sortable: true,
    },
    {
        title: 'DESCRIPCIÓN',
        key: 'descripcion',
        sortable: true,
    },
    {
        title: 'ESTADO',
        key: 'estado',
        sortable: true,
        align: 'center' as const,
        width: '120px',
    },
];

// Composable de tabla
const table = useVuetifyTable<MiModulo>({
    apiURL: '/configuracion/mi-modulo',
    searchFields: ['nombre', 'descripcion'],
    serverSidePagination: false,
    serverSideSorting: false,
    serverSideSearch: false,
});

// Inicializar menú de columnas
table.updateColumnMenu(headers);

// Composable de CRUD
const crudModal = useCrudModal<MiModulo>({
    entityName: 'módulo',
    getPayload: (form): CreateMiModuloDto => ({
        nombre: form.nombre.trim(),
        descripcion: form.descripcion?.trim() || '',
    }),
    validateForm: (form) => {
        if (!form.nombre.trim()) {
            return 'El nombre es obligatorio.';
        }
        return null;
    },
    onCreate: async (data: CreateMiModuloDto) => {
        const response = await apiClient.post<MiModulo>('/configuracion/mi-modulo', data);
        return response.data;
    },
    onUpdate: async (id: number, data: CreateMiModuloDto) => {
        const response = await apiClient.post<MiModulo>(`/configuracion/mi-modulo/${id}`, {
            ...data,
            id,
        } as UpdateMiModuloDto);
        return response.data;
    },
    onDeleteCustom: async (id: number) => {
        await apiClient.delete(`/configuracion/mi-modulo/${id}`);
    },
    onEdit: (item: MiModulo) => {
        saveForm.nombre = item.nombre;
        saveForm.descripcion = item.descripcion || '';
    },
    resetForm: () => {
        saveForm.nombre = '';
        saveForm.descripcion = '';
    },
});

// Funciones auxiliares
const updateSearchValue = (value: string) => {
    table.searchQuery.value = value;
    table.applySearch(value);
};

const downloadExcel = () => {
    table.downloadExcel('mi-modulo.xlsx', 'Mi Módulo');
};

const toggleColumnVisibility = (key: string) => {
    table.toggleColumnVisibility(key);
};

// Lifecycle
onMounted(async () => {
    (window as any).XLSX = XLSX;
    await table.loadItems({
        page: 1,
        itemsPerPage: 10,
    });
});
</script>
```

---

## 📊 Paso 3: Configurar la Tabla

### 3.1 Template de la Tabla

```vue
<template>
    <AuthenticatedLayout>
        <v-container fluid class="pa-4">
            <!-- Header Section -->
            <v-card class="mb-4" rounded="lg" elevation="1">
                <v-card-text class="pa-4">
                    <div class="d-flex flex-wrap align-center justify-space-between ga-4">
                        <div>
                            <h1 class="text-h5 font-weight-bold mb-2">Mi Módulo</h1>
                            <p class="text-body-2 text-medium-emphasis mb-0">
                                Gestiona los elementos del módulo; crea, edita o elimina según necesidad.
                            </p>
                        </div>
                        <v-btn
                            color="primary"
                            prepend-icon="mdi-plus"
                            variant="flat"
                            size="default"
                            @click="crudModal.openCreateModal"
                            aria-label="Crear nuevo elemento"
                            class="text-none"
                        >
                            Nuevo Elemento
                        </v-btn>
                    </div>
                </v-card-text>
            </v-card>

            <!-- Table Section -->
            <v-card rounded="lg" elevation="1">
                <VDataTableCard
                    :loading="table.loading.value"
                    :column-menu="table.columnMenu.value"
                    :search-value="table.searchQuery.value"
                    search-placeholder="Buscar elemento..."
                    @print="table.printTable"
                    @export="downloadExcel"
                    @toggle-column="toggleColumnVisibility"
                    @update:search="updateSearchValue"
                >
                    <v-data-table-server
                        v-model:page="table.page.value"
                        v-model:items-per-page="table.itemsPerPage.value"
                        v-model:sort-by="table.sortBy.value"
                        :headers="headers.filter(h => table.columnMenu.value.find(c => c.key === h.key)?.visible !== false)"
                        :items="table.items.value"
                        :loading="table.loading.value"
                        :items-length="table.totalItems.value"
                        :density="'compact'"
                        :fixed-header="true"
                        height="450"
                        @update:options="table.loadItems"
                        class="elevation-0"
                    >
                        <!-- Columna de Acciones -->
                        <template #item.actions="{ item }">
                            <div class="d-flex align-center ga-1">
                                <v-btn
                                    icon="mdi-pencil"
                                    size="small"
                                    color="primary"
                                    variant="flat"
                                    @click="crudModal.openEditModal(item)"
                                />
                                <v-menu>
                                    <template #activator="{ props: menuProps }">
                                        <v-btn
                                            v-bind="menuProps"
                                            icon="mdi-dots-vertical"
                                            size="small"
                                            color="grey-darken-1"
                                            variant="text"
                                        />
                                    </template>
                                    <v-list density="compact">
                                        <v-list-item
                                            prepend-icon="mdi-delete"
                                            title="Eliminar"
                                            class="text-error"
                                            @click="crudModal.openDeleteModal(item)"
                                        />
                                    </v-list>
                                </v-menu>
                            </div>
                        </template>

                        <!-- Columna de Estado -->
                        <template #item.estado="{ value }">
                            <v-chip
                                :color="formatStatusChip(value).color"
                                size="small"
                                variant="flat"
                                class="text-uppercase font-weight-medium"
                            >
                                {{ formatStatusChip(value).label }}
                            </v-chip>
                        </template>
                    </v-data-table-server>

                    <template #footer-left>
                        <span class="text-body-2 text-medium-emphasis">{{ table.recordSummary.value }}</span>
                    </template>
                    <template #footer-right>
                        <span class="text-body-2 text-medium-emphasis">Actualizado automáticamente</span>
                    </template>
                </VDataTableCard>
            </v-card>
        </v-container>
    </AuthenticatedLayout>
</template>
```

---

## 🎨 Paso 4: Configurar Modales CRUD

### 4.1 Modal de Crear/Editar

```vue
<!-- Save/Edit Modal -->
<AppModal
    v-model:open="crudModal.showSaveModal.value"
    :title="crudModal.saveModalTitle.value"
    size="md"
>
    <template #body>
        <v-form @submit.prevent>
            <v-row>
                <!-- Campo Nombre -->
                <v-col cols="12">
                    <v-text-field
                        v-model="saveForm.nombre"
                        label="Nombre"
                        :rules="[(v) => !!v || 'El nombre es obligatorio']"
                        counter="100"
                        maxlength="100"
                        placeholder="Ingrese el nombre"
                        required
                        variant="outlined"
                        density="compact"
                    />
                </v-col>

                <!-- Campo Descripción -->
                <v-col cols="12">
                    <v-textarea
                        v-model="saveForm.descripcion"
                        label="Descripción"
                        counter="500"
                        maxlength="500"
                        rows="3"
                        placeholder="Ingrese una descripción (opcional)"
                        variant="outlined"
                        density="compact"
                    />
                </v-col>
            </v-row>
        </v-form>
    </template>
    <template #footer>
        <v-btn
            variant="text"
            @click="crudModal.closeSaveModal"
            :disabled="crudModal.saving.value"
            class="text-none"
        >
            Cancelar
        </v-btn>
        <v-btn
            color="primary"
            variant="tonal"
            @click="() => crudModal.handleSaveSubmit(saveForm, table.reloadTable)"
            :loading="crudModal.saving.value"
            class="text-none"
        >
            {{ crudModal.editingId.value ? 'Actualizar' : 'Guardar' }}
        </v-btn>
    </template>
</AppModal>
```

### 4.2 Modal de Eliminar

```vue
<!-- Delete Modal -->
<AppModal
    v-model:open="crudModal.showDeleteModal.value"
    title="Eliminar Elemento"
    size="sm"
>
    <template #body>
        <v-container fluid class="pa-4">
            <div class="text-center mb-4">
                <v-icon icon="mdi-alert-circle" size="64" color="error" />
            </div>
            <p class="text-body-1 text-center">
                ¿Está seguro que desea eliminar el elemento
                <strong class="text-error">{{ crudModal.deleteTarget.value?.nombre }}</strong>?
            </p>
            <p class="text-body-2 text-medium-emphasis text-center mt-2">
                Esta acción no se puede deshacer.
            </p>
        </v-container>
    </template>
    <template #footer>
        <v-btn
            variant="text"
            @click="crudModal.closeDeleteModal"
            :disabled="crudModal.deleting.value"
            class="text-none"
        >
            Cancelar
        </v-btn>
        <v-btn
            color="error"
            variant="flat"
            @click="() => crudModal.handleDeleteConfirm(table.reloadTable)"
            :loading="crudModal.deleting.value"
            class="text-none"
        >
            Eliminar
        </v-btn>
    </template>
</AppModal>
```

---

## 🔌 Paso 5: Implementar Funciones de API

### 5.1 Endpoints Requeridos en el Backend

El backend debe proporcionar los siguientes endpoints:

```php
// GET /api/configuracion/mi-modulo
// Retorna: { data: MiModulo[], total: number }

// POST /api/configuracion/mi-modulo
// Body: CreateMiModuloDto
// Retorna: { data: MiModulo, message: string }

// POST /api/configuracion/mi-modulo/{id}
// Body: UpdateMiModuloDto
// Retorna: { data: MiModulo, message: string }

// DELETE /api/configuracion/mi-modulo/{id}
// Retorna: { message: string }
```

### 5.2 Configuración del Composable useCrudModal

```typescript
const crudModal = useCrudModal<MiModulo>({
    // Nombre de la entidad (para mensajes)
    entityName: 'módulo',
    
    // Función que transforma el formulario en el payload para la API
    getPayload: (form): CreateMiModuloDto => ({
        nombre: form.nombre.trim(),
        descripcion: form.descripcion?.trim() || '',
    }),
    
    // Validación del formulario
    validateForm: (form) => {
        if (!form.nombre.trim()) {
            return 'El nombre es obligatorio.';
        }
        return null; // null = válido
    },
    
    // Función para crear
    onCreate: async (data: CreateMiModuloDto) => {
        const response = await apiClient.post<MiModulo>('/configuracion/mi-modulo', data);
        return response.data;
    },
    
    // Función para actualizar
    onUpdate: async (id: number, data: CreateMiModuloDto) => {
        const response = await apiClient.post<MiModulo>(`/configuracion/mi-modulo/${id}`, {
            ...data,
            id,
        } as UpdateMiModuloDto);
        return response.data;
    },
    
    // Función para eliminar
    onDeleteCustom: async (id: number) => {
        await apiClient.delete(`/configuracion/mi-modulo/${id}`);
    },
    
    // Función que carga los datos en el formulario al editar
    onEdit: (item: MiModulo) => {
        saveForm.nombre = item.nombre;
        saveForm.descripcion = item.descripcion || '';
    },
    
    // Función que resetea el formulario
    resetForm: () => {
        saveForm.nombre = '';
        saveForm.descripcion = '';
    },
});
```

---

## 🎨 Paso 6: Diseño Material Design 3

### 6.1 Reglas de Diseño

#### Header Section
- Usar `v-card` con `rounded="lg"` y `elevation="1"`
- Título con `text-h5 font-weight-bold`
- Subtítulo con `text-body-2 text-medium-emphasis`
- Botón de acción con `variant="flat"` y `color="primary"`

#### Table Section
- Usar `v-card` con `rounded="lg"` y `elevation="1"`
- Tabla con `density="compact"`, `fixed-header`, `height="450"`
- Chips de estado con `font-weight-medium` (no `font-weight-bold`)
- Botones de acción: `variant="flat"` para editar, `variant="text"` para menú

#### Modals
- Usar `AppModal` con `size="md"` (600px)
- Campos con `variant="outlined"` y `density="compact"`
- Botones del footer: `variant="text"` para cancelar, `variant="tonal"` para guardar
- Footer con `d-flex justify-end ga-3`

#### Cards Internos (v-sheet)
- Usar `rounded="md"` para cards secundarios
- Mantener `variant="outlined"` y `color="surface"`
- Padding consistente (`pa-4`)

### 6.2 Valores de `rounded` por Contexto

Vuetify 3 soporta los siguientes valores para la prop `rounded`:

- `rounded="sm"` - Pequeño (4px)
- `rounded="md"` - Mediano (8px) - **Para cards internos**
- `rounded="lg"` - Grande (12px) - **Para cards principales**
- `rounded="xl"` - Extra grande (16px) - **Para cards destacados**

**Recomendación:**
- Cards principales: `rounded="lg"`
- Cards internos (v-sheet): `rounded="md"`
- Modales destacados: `rounded="xl"`

### 6.3 Sin Estilos CSS Personalizados

**NO usar estilos CSS personalizados para border-radius.** Vuetify maneja esto con props nativas:

```vue
<!-- ✅ CORRECTO -->
<v-card rounded="lg" elevation="1">

<!-- ❌ INCORRECTO -->
            <v-card rounded="lg" elevation="1">
<style scoped>
.rounded-xl { border-radius: 16px !important; }
</style>
```

---

## 📦 Ejemplo Completo

### Archivo: `frontend/src/views/Configuracion/MiModulo/Index.vue`

```vue
<script setup lang="ts">
import AuthenticatedLayout from '@/components/Layouts/AuthenticatedLayout.vue';
import { reactive, onMounted } from 'vue';
import * as XLSX from 'xlsx';
import type { MiModulo, CreateMiModuloDto, UpdateMiModuloDto } from '@/types/configuracion';
import AppModal from '@/components/Partial/AppModal.vue';
import VDataTableCard from '@/components/Table/VDataTableCard.vue';
import { useVuetifyTable } from '@/composables/useVuetifyTable';
import { useCrudModal } from '@/composables/useCrudModal';
import apiClient from '@/api/axios';
import { formatStatusChip } from '@/utils/vuetifyTableHelpers';

// Form reactivo
const saveForm = reactive({
    nombre: '',
    descripcion: '',
});

// Headers de la tabla
const headers = [
    {
        title: 'ACCIONES',
        key: 'actions',
        sortable: false,
        width: '150px',
    },
    {
        title: 'NOMBRE',
        key: 'nombre',
        sortable: true,
    },
    {
        title: 'DESCRIPCIÓN',
        key: 'descripcion',
        sortable: true,
    },
    {
        title: 'ESTADO',
        key: 'estado',
        sortable: true,
        align: 'center' as const,
        width: '120px',
    },
];

// Composable de tabla
const table = useVuetifyTable<MiModulo>({
    apiURL: '/configuracion/mi-modulo',
    searchFields: ['nombre', 'descripcion'],
    serverSidePagination: false,
    serverSideSorting: false,
    serverSideSearch: false,
});

table.updateColumnMenu(headers);

// Composable de CRUD
const crudModal = useCrudModal<MiModulo>({
    entityName: 'módulo',
    getPayload: (form): CreateMiModuloDto => ({
        nombre: form.nombre.trim(),
        descripcion: form.descripcion?.trim() || '',
    }),
    validateForm: (form) => {
        if (!form.nombre.trim()) {
            return 'El nombre es obligatorio.';
        }
        return null;
    },
    onCreate: async (data: CreateMiModuloDto) => {
        const response = await apiClient.post<MiModulo>('/configuracion/mi-modulo', data);
        return response.data;
    },
    onUpdate: async (id: number, data: CreateMiModuloDto) => {
        const response = await apiClient.post<MiModulo>(`/configuracion/mi-modulo/${id}`, {
            ...data,
            id,
        } as UpdateMiModuloDto);
        return response.data;
    },
    onDeleteCustom: async (id: number) => {
        await apiClient.delete(`/configuracion/mi-modulo/${id}`);
    },
    onEdit: (item: MiModulo) => {
        saveForm.nombre = item.nombre;
        saveForm.descripcion = item.descripcion || '';
    },
    resetForm: () => {
        saveForm.nombre = '';
        saveForm.descripcion = '';
    },
});

// Funciones auxiliares
const updateSearchValue = (value: string) => {
    table.searchQuery.value = value;
    table.applySearch(value);
};

const downloadExcel = () => {
    table.downloadExcel('mi-modulo.xlsx', 'Mi Módulo');
};

const toggleColumnVisibility = (key: string) => {
    table.toggleColumnVisibility(key);
};

// Lifecycle
onMounted(async () => {
    (window as any).XLSX = XLSX;
    await table.loadItems({
        page: 1,
        itemsPerPage: 10,
    });
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
                            <h1 class="text-h5 font-weight-bold mb-2">Mi Módulo</h1>
                            <p class="text-body-2 text-medium-emphasis mb-0">
                                Gestiona los elementos del módulo; crea, edita o elimina según necesidad.
                            </p>
                        </div>
                        <v-btn
                            color="primary"
                            prepend-icon="mdi-plus"
                            variant="flat"
                            size="default"
                            @click="crudModal.openCreateModal"
                            aria-label="Crear nuevo elemento"
                            class="text-none"
                        >
                            Nuevo Elemento
                        </v-btn>
                    </div>
                </v-card-text>
            </v-card>

            <!-- Table Section -->
            <v-card rounded="lg" elevation="1">
                <VDataTableCard
                    :loading="table.loading.value"
                    :column-menu="table.columnMenu.value"
                    :search-value="table.searchQuery.value"
                    search-placeholder="Buscar elemento..."
                    @print="table.printTable"
                    @export="downloadExcel"
                    @toggle-column="toggleColumnVisibility"
                    @update:search="updateSearchValue"
                >
                    <v-data-table-server
                        v-model:page="table.page.value"
                        v-model:items-per-page="table.itemsPerPage.value"
                        v-model:sort-by="table.sortBy.value"
                        :headers="headers.filter(h => table.columnMenu.value.find(c => c.key === h.key)?.visible !== false)"
                        :items="table.items.value"
                        :loading="table.loading.value"
                        :items-length="table.totalItems.value"
                        :density="'compact'"
                        :fixed-header="true"
                        height="450"
                        @update:options="table.loadItems"
                        class="elevation-0"
                    >
                        <template #item.actions="{ item }">
                            <div class="d-flex align-center ga-1">
                                <v-btn
                                    icon="mdi-pencil"
                                    size="small"
                                    color="primary"
                                    variant="flat"
                                    @click="crudModal.openEditModal(item)"
                                />
                                <v-menu>
                                    <template #activator="{ props: menuProps }">
                                        <v-btn
                                            v-bind="menuProps"
                                            icon="mdi-dots-vertical"
                                            size="small"
                                            color="grey-darken-1"
                                            variant="text"
                                        />
                                    </template>
                                    <v-list density="compact">
                                        <v-list-item
                                            prepend-icon="mdi-delete"
                                            title="Eliminar"
                                            class="text-error"
                                            @click="crudModal.openDeleteModal(item)"
                                        />
                                    </v-list>
                                </v-menu>
                            </div>
                        </template>

                        <template #item.estado="{ value }">
                            <v-chip
                                :color="formatStatusChip(value).color"
                                size="small"
                                variant="flat"
                                class="text-uppercase font-weight-medium"
                            >
                                {{ formatStatusChip(value).label }}
                            </v-chip>
                        </template>
                    </v-data-table-server>

                    <template #footer-left>
                        <span class="text-body-2 text-medium-emphasis">{{ table.recordSummary.value }}</span>
                    </template>
                    <template #footer-right>
                        <span class="text-body-2 text-medium-emphasis">Actualizado automáticamente</span>
                    </template>
                </VDataTableCard>
            </v-card>

            <!-- Save/Edit Modal -->
            <AppModal
                v-model:open="crudModal.showSaveModal.value"
                :title="crudModal.saveModalTitle.value"
                size="md"
            >
                <template #body>
                    <v-form @submit.prevent>
                        <v-row>
                            <v-col cols="12">
                                <v-text-field
                                    v-model="saveForm.nombre"
                                    label="Nombre"
                                    :rules="[(v) => !!v || 'El nombre es obligatorio']"
                                    counter="100"
                                    maxlength="100"
                                    placeholder="Ingrese el nombre"
                                    required
                                    variant="outlined"
                                    density="compact"
                                />
                            </v-col>
                            <v-col cols="12">
                                <v-textarea
                                    v-model="saveForm.descripcion"
                                    label="Descripción"
                                    counter="500"
                                    maxlength="500"
                                    rows="3"
                                    placeholder="Ingrese una descripción (opcional)"
                                    variant="outlined"
                                    density="compact"
                                />
                            </v-col>
                        </v-row>
                    </v-form>
                </template>
                <template #footer>
                    <v-btn
                        variant="text"
                        @click="crudModal.closeSaveModal"
                        :disabled="crudModal.saving.value"
                        class="text-none"
                    >
                        Cancelar
                    </v-btn>
                    <v-btn
                        color="primary"
                        variant="tonal"
                        @click="() => crudModal.handleSaveSubmit(saveForm, table.reloadTable)"
                        :loading="crudModal.saving.value"
                        class="text-none"
                    >
                        {{ crudModal.editingId.value ? 'Actualizar' : 'Guardar' }}
                    </v-btn>
                </template>
            </AppModal>

            <!-- Delete Modal -->
            <AppModal
                v-model:open="crudModal.showDeleteModal.value"
                title="Eliminar Elemento"
                size="sm"
            >
                <template #body>
                    <v-container fluid class="pa-4">
                        <div class="text-center mb-4">
                            <v-icon icon="mdi-alert-circle" size="64" color="error" />
                        </div>
                        <p class="text-body-1 text-center">
                            ¿Está seguro que desea eliminar el elemento
                            <strong class="text-error">{{ crudModal.deleteTarget.value?.nombre }}</strong>?
                        </p>
                        <p class="text-body-2 text-medium-emphasis text-center mt-2">
                            Esta acción no se puede deshacer.
                        </p>
                    </v-container>
                </template>
                <template #footer>
                    <v-btn
                        variant="text"
                        @click="crudModal.closeDeleteModal"
                        :disabled="crudModal.deleting.value"
                        class="text-none"
                    >
                        Cancelar
                    </v-btn>
                    <v-btn
                        color="error"
                        variant="flat"
                        @click="() => crudModal.handleDeleteConfirm(table.reloadTable)"
                        :loading="crudModal.deleting.value"
                        class="text-none"
                    >
                        Eliminar
                    </v-btn>
                </template>
            </AppModal>
        </v-container>
    </AuthenticatedLayout>
</template>

<!-- Sin estilos CSS personalizados - Vuetify maneja border-radius con props nativas -->
```

---

## ✅ Checklist de Verificación

### Tipos TypeScript
- [ ] Entidad principal definida (`MiModulo`)
- [ ] DTOs de creación y actualización definidos
- [ ] Tipos exportados correctamente

### Componente Principal
- [ ] Form reactivo con todos los campos necesarios
- [ ] Headers de tabla definidos correctamente
- [ ] Composable `useVuetifyTable` configurado
- [ ] Composable `useCrudModal` configurado con todas las funciones
- [ ] Funciones auxiliares implementadas
- [ ] Lifecycle `onMounted` configurado

### Tabla
- [ ] Header section con título y botón de acción
- [ ] Tabla con `VDataTableCard` y `v-data-table-server`
- [ ] Columna de acciones con botones de editar y menú
- [ ] Templates para columnas especiales (chips, etc.)
- [ ] Footer con resumen de registros

### Modales
- [ ] Modal de crear/editar con formulario completo
- [ ] Modal de eliminar con confirmación
- [ ] Validaciones en formularios
- [ ] Botones con estados de carga
- [ ] Manejo de errores

### Diseño Material Design 3
- [ ] Cards con `rounded="lg"` (props nativas de Vuetify) y `elevation` apropiado
- [ ] Tipografías consistentes (`text-h5`, `text-body-2`)
- [ ] Campos con `variant="outlined"` y `density="compact"`
- [ ] Botones con variantes correctas (`flat`, `tonal`, `text`)
- [ ] Chips con `font-weight-medium`
- [ ] Espaciado consistente (`pa-4`, `mb-4`, `ga-3`)

### Funcionalidad
- [ ] Crear elementos funciona correctamente
- [ ] Editar elementos funciona correctamente
- [ ] Eliminar elementos funciona correctamente
- [ ] Búsqueda funciona correctamente
- [ ] Exportar a Excel funciona correctamente
- [ ] Imprimir tabla funciona correctamente
- [ ] Paginación funciona correctamente
- [ ] Ordenamiento funciona correctamente

---

## 🔧 Casos Especiales

### Módulo con Imágenes

Si tu módulo necesita manejar imágenes:

```typescript
import { useImageUpload } from '@/composables/useImageUpload';

const imagenUpload = useImageUpload('/images/sin_imagen.jpg');

// En el formulario
const formData = new FormData();
if (imagenUpload.file.value) {
    formData.append('imagen', imagenUpload.file.value);
}
```

### Módulo con Selects Dinámicos

```typescript
const opciones = ref<{ id: number; text: string }[]>([]);

async function loadOpciones() {
    const response = await apiClient.get('/configuracion/selects/opciones');
    opciones.value = response.data;
}

// En el template
<v-select
    v-model="saveForm.id_opcion"
    label="Opción"
    :items="opciones.map(o => ({ title: o.text, value: String(o.id) }))"
    variant="outlined"
    density="compact"
/>
```

### Módulo con Validaciones Complejas

```typescript
validateForm: (form) => {
    if (!form.nombre.trim()) {
        return 'El nombre es obligatorio.';
    }
    if (form.nombre.length < 3) {
        return 'El nombre debe tener al menos 3 caracteres.';
    }
    if (form.email && !/.+@.+\..+/.test(form.email)) {
        return 'El correo electrónico no es válido.';
    }
    return null;
},
```

---

## 📚 Recursos Adicionales

- **Composables**: `frontend/src/composables/`
- **Componentes**: `frontend/src/components/`
- **Tipos**: `frontend/src/types/`
- **Utilidades**: `frontend/src/utils/`
- **Ejemplos**: Ver módulos existentes en `frontend/src/views/Configuracion/`

---

**Última actualización**: 2026-01-15  
**Versión del manual**: 1.0
