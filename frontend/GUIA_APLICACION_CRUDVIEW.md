# 📘 GUÍA PRÁCTICA: Cómo Aplicar CrudView

## 🎯 Objetivo

Migrar vistas CRUD existentes al componente genérico `CrudView` para reducir código duplicado de ~300-500 líneas a ~50-100 líneas.

---

## 📋 PASO A PASO: Migración de una Vista

### Ejemplo: Migrar `Areas/Index.vue`

#### ANTES (335 líneas)

```vue
<script setup lang="ts">
import AuthenticatedLayout from '@/components/Layouts/AuthenticatedLayout.vue';
import { reactive, onMounted } from 'vue';
import type { Area } from '@/types/configuracion';
import type { CreateAreaDto } from '@/types/configuracion';
import AppModal from '@/components/Partial/AppModal.vue';
import VDataTableCard from '@/components/Table/VDataTableCard.vue';
import { useVuetifyTable } from '@/composables/useVuetifyTable';
import { useCrudModal } from '@/composables/useCrudModal';
import apiClient from '@/api/axios';
import type { UpdateAreaDto } from '@/types/configuracion';
import { formatStatusChip } from '@/utils/vuetifyTableHelpers';

// Form
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
const table = useVuetifyTable<Area>({
    apiURL: '/configuracion/areas',
    searchFields: ['nombre', 'descripcion'],
    serverSidePagination: false,
    serverSideSorting: false,
    serverSideSearch: false,
});

// Inicializar menú de columnas
table.updateColumnMenu(headers);

// Composable de CRUD
const crudModal = useCrudModal<Area>({
    entityName: 'área',
    getPayload: (form): CreateAreaDto => ({
        nombre: form.nombre.trim(),
        descripcion: form.descripcion.trim() || null,
    }),
    validateForm: (form) => {
        if (!form.nombre.trim()) {
            return 'El nombre del área es obligatorio.';
        }
        return null;
    },
    onCreate: async (data: CreateAreaDto) => {
        const response = await apiClient.post<Area>('/configuracion/areas', data);
        return response.data;
    },
    onUpdate: async (id: number, data: CreateAreaDto) => {
        const response = await apiClient.post<Area>(`/configuracion/areas/${id}`, {
            ...data,
            id,
        } as UpdateAreaDto);
        return response.data;
    },
    onDeleteCustom: async (id: number) => {
        await apiClient.delete(`/configuracion/areas/${id}`);
    },
    onEdit: (area: Area) => {
        saveForm.nombre = area.nombre;
        saveForm.descripcion = area.descripcion ?? '';
    },
    resetForm: () => {
        saveForm.nombre = '';
        saveForm.descripcion = '';
    },
});

// Funciones
const updateSearchValue = (value: string) => {
    table.searchQuery.value = value;
    table.applySearch(value);
};

const downloadExcel = () => {
    table.downloadExcel('areas.xlsx', 'Áreas');
};

const toggleColumnVisibility = (key: string) => {
    table.toggleColumnVisibility(key);
};

// Lifecycle
onMounted(async () => {
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
                            <h1 class="text-h5 font-weight-bold mb-2">Áreas</h1>
                            <p class="text-body-2 text-medium-emphasis mb-0">
                                Gestiona las áreas de la organización; activa, edita o elimina según necesidad.
                            </p>
                        </div>
                        <v-btn
                            color="primary"
                            prepend-icon="mdi-plus"
                            variant="flat"
                            size="default"
                            @click="crudModal.openCreateModal"
                            aria-label="Crear nueva área"
                            class="text-none"
                        >
                            Nueva Área
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
                    search-placeholder="Buscar área..."
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
                        :items-per-page-options="[]"
                        hide-default-footer
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

                        <template #item.descripcion="{ value }">
                            <div class="text-body-2" style="max-width: 400px; word-wrap: break-word; overflow-wrap: break-word; white-space: normal; line-height: 1.4;">
                                {{ value || '—' }}
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
            >
                <template #body>
                    <v-container fluid class="pa-4">
                        <v-form @submit.prevent>
                            <v-text-field
                                v-model="saveForm.nombre"
                                label="Nombre del Área"
                                :rules="[(v) => !!v || 'El nombre es obligatorio']"
                                counter="100"
                                maxlength="100"
                                placeholder="Ingrese el nombre del área"
                                required
                                variant="outlined"
                                density="compact"
                                class="mb-4"
                            />
                            <v-textarea
                                v-model="saveForm.descripcion"
                                label="Descripción"
                                counter="255"
                                maxlength="255"
                                rows="3"
                                placeholder="Ingrese una descripción (opcional)"
                                variant="outlined"
                                density="compact"
                            />
                        </v-form>
                    </v-container>
                </template>
                <template #footer>
                    <div class="d-flex justify-end ga-2">
                        <v-btn
                            variant="outlined"
                            @click="crudModal.closeSaveModal"
                            :disabled="crudModal.saving.value"
                            class="text-none"
                        >
                            Cancelar
                        </v-btn>
                        <v-btn
                            color="primary"
                            variant="flat"
                            @click="() => crudModal.handleSaveSubmit(saveForm, table.reloadTable)"
                            :loading="crudModal.saving.value"
                            class="text-none"
                        >
                            {{ crudModal.editingId.value ? 'Actualizar' : 'Guardar' }}
                        </v-btn>
                    </div>
                </template>
            </AppModal>

            <!-- Delete Modal -->
            <AppModal
                v-model:open="crudModal.showDeleteModal.value"
                title="Eliminar Área"
                size="sm"
            >
                <template #body>
                    <v-container fluid class="pa-4">
                        <div class="text-center mb-4">
                            <v-icon icon="mdi-alert-circle" size="64" color="error" />
                        </div>
                        <p class="text-body-1 text-center">
                            ¿Está seguro que desea eliminar el área
                            <strong class="text-error">{{ crudModal.deleteTarget.value?.nombre }}</strong>?
                        </p>
                        <p class="text-body-2 text-medium-emphasis text-center mt-2">
                            Esta acción no se puede deshacer.
                        </p>
                    </v-container>
                </template>
                <template #footer>
                    <div class="d-flex justify-end ga-2">
                        <v-btn
                            variant="outlined"
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
                    </div>
                </template>
            </AppModal>
        </v-container>
    </AuthenticatedLayout>
</template>
```

#### DESPUÉS (100 líneas) - Con CrudView

```vue
<script setup lang="ts">
import CrudView from '@/components/Crud/CrudView.vue';
import type { Area } from '@/types/configuracion';
import type { CreateAreaDto } from '@/types/configuracion';
import { formatStatusChip } from '@/utils/vuetifyTableHelpers';

const config = {
    entityName: 'área',
    title: 'Áreas',
    description: 'Gestiona las áreas de la organización; activa, edita o elimina según necesidad.',
    apiEndpoint: '/configuracion/areas',
    searchFields: ['nombre', 'descripcion'] as (keyof Area)[],
    columns: [
        {
            key: 'actions',
            title: 'ACCIONES',
            sortable: false,
            width: '150px',
        },
        {
            key: 'nombre',
            title: 'NOMBRE',
            sortable: true,
        },
        {
            key: 'descripcion',
            title: 'DESCRIPCIÓN',
            sortable: true,
        },
        {
            key: 'estado',
            title: 'ESTADO',
            sortable: true,
            align: 'center' as const,
            width: '120px',
        },
    ],
    formConfig: {
        initialValues: {
            nombre: '',
            descripcion: '',
        },
        getPayload: (form): CreateAreaDto => ({
            nombre: String(form.nombre).trim(),
            descripcion: form.descripcion ? String(form.descripcion).trim() : null,
        }),
        validate: (form) => {
            if (!String(form.nombre).trim()) {
                return 'El nombre del área es obligatorio.';
            }
            return null;
        },
        populateForm: (item: Area, form: Record<string, unknown>) => {
            form.nombre = item.nombre;
            form.descripcion = item.descripcion ?? '';
        },
        resetForm: (form: Record<string, unknown>) => {
            form.nombre = '';
            form.descripcion = '';
        },
    },
};
</script>

<template>
    <CrudView :config="config">
        <!-- Formulario personalizado -->
        <template #form="{ form }">
            <v-container fluid class="pa-4">
                <v-form @submit.prevent>
                    <v-text-field
                        v-model="form.nombre"
                        label="Nombre del Área"
                        :rules="[(v) => !!v || 'El nombre es obligatorio']"
                        counter="100"
                        maxlength="100"
                        placeholder="Ingrese el nombre del área"
                        required
                        variant="outlined"
                        density="compact"
                        class="mb-4"
                    />
                    <v-textarea
                        v-model="form.descripcion"
                        label="Descripción"
                        counter="255"
                        maxlength="255"
                        rows="3"
                        placeholder="Ingrese una descripción (opcional)"
                        variant="outlined"
                        density="compact"
                    />
                </v-form>
            </v-container>
        </template>

        <!-- Personalización de celdas -->
        <template #item-estado="{ item }">
            <v-chip
                :color="formatStatusChip(item.estado).color"
                size="small"
                variant="flat"
                class="text-uppercase font-weight-medium"
            >
                {{ formatStatusChip(item.estado).label }}
            </v-chip>
        </template>

        <template #item-descripcion="{ value }">
            <div class="text-body-2" style="max-width: 400px; word-wrap: break-word; overflow-wrap: break-word; white-space: normal; line-height: 1.4;">
                {{ value || '—' }}
            </div>
        </template>
    </CrudView>
</template>
```

**Reducción:** De 335 líneas a 100 líneas (70% menos código) ✅

---

## 🔄 PROCESO DE MIGRACIÓN

### Paso 1: Preparar la Configuración

Extrae la configuración de tu vista actual:

```typescript
const config = {
    // 1. Información básica
    entityName: 'área',  // Nombre singular para mensajes
    title: 'Áreas',      // Título de la página
    description: '...',   // Descripción opcional
    
    // 2. Endpoint de la API
    apiEndpoint: '/configuracion/areas',
    
    // 3. Campos de búsqueda
    searchFields: ['nombre', 'descripcion'] as (keyof Area)[],
    
    // 4. Columnas de la tabla
    columns: [
        { key: 'actions', title: 'ACCIONES', sortable: false, width: '150px' },
        { key: 'nombre', title: 'NOMBRE', sortable: true },
        // ... más columnas
    ],
    
    // 5. Configuración del formulario
    formConfig: {
        initialValues: { /* valores iniciales */ },
        getPayload: (form) => { /* transformar a payload */ },
        validate: (form) => { /* validación */ },
        populateForm: (item, form) => { /* poblar al editar */ },
        resetForm: (form) => { /* resetear */ },
    },
};
```

### Paso 2: Reemplazar el Template

```vue
<template>
    <!-- ANTES: Todo el código repetido -->
    <AuthenticatedLayout>
        <v-container>
            <!-- ... 200+ líneas de código repetido ... -->
        </v-container>
    </AuthenticatedLayout>

    <!-- DESPUÉS: Solo CrudView con slots -->
    <CrudView :config="config">
        <!-- Solo personalizaciones específicas -->
        <template #form="{ form }">
            <!-- Tu formulario personalizado -->
        </template>
        
        <template #item-estado="{ item }">
            <!-- Personalización de celdas si es necesario -->
        </template>
    </CrudView>
</template>
```

### Paso 3: Eliminar Código Duplicado

Elimina de tu vista:
- ❌ `AuthenticatedLayout` (ya está en CrudView)
- ❌ `VDataTableCard` (ya está en CrudView)
- ❌ `useVuetifyTable` (ya está en CrudView)
- ❌ `useCrudModal` (ya está en CrudView)
- ❌ Funciones `updateSearchValue`, `downloadExcel`, `toggleColumnVisibility`
- ❌ Modales de guardar/eliminar (ya están en CrudView)
- ❌ `onMounted` con carga inicial (ya está en CrudView)

---

## 📝 EJEMPLOS POR TIPO DE VISTA

### Vista Simple (Areas, Lugares, TipoPersonal)

```vue
<script setup lang="ts">
import CrudView from '@/components/Crud/CrudView.vue';
import type { Entity } from '@/types/configuracion';

const config = {
    entityName: 'entidad',
    title: 'Entidades',
    apiEndpoint: '/configuracion/entidades',
    searchFields: ['nombre'] as (keyof Entity)[],
    columns: [/* ... */],
    formConfig: {
        initialValues: { nombre: '' },
        getPayload: (form) => ({ nombre: String(form.nombre).trim() }),
        validate: (form) => !form.nombre ? 'Requerido' : null,
        populateForm: (item, form) => { form.nombre = item.nombre; },
        resetForm: (form) => { form.nombre = ''; },
    },
};
</script>

<template>
    <CrudView :config="config">
        <template #form="{ form }">
            <!-- Formulario básico -->
        </template>
    </CrudView>
</template>
```

### Vista con Campos Personalizados (EstadoMonitoreo con ColorPicker)

```vue
<script setup lang="ts">
import CrudView from '@/components/Crud/CrudView.vue';
import type { EstadoMonitoreo } from '@/types/configuracion';

const config = {
    entityName: 'estado de monitoreo',
    title: 'Estados de Monitoreo',
    apiEndpoint: '/configuracion/estado-monitoreo',
    // ... configuración básica
};
</script>

<template>
    <CrudView :config="config">
        <template #form="{ form }">
            <v-container fluid class="pa-4">
                <v-text-field
                    v-model="form.nombre"
                    label="Nombre"
                    variant="outlined"
                    density="compact"
                />
                
                <!-- Campo personalizado: Color Picker -->
                <v-sheet variant="outlined" class="pa-4 mb-4">
                    <v-label class="mb-2">Color de Fondo</v-label>
                    <input
                        v-model="form.color_fondo"
                        type="color"
                        class="color-picker"
                    />
                    <div class="mt-2">
                        Vista previa: 
                        <div 
                            :style="{ backgroundColor: form.color_fondo }"
                            class="color-preview"
                        />
                    </div>
                </v-sheet>
            </v-container>
        </template>
    </CrudView>
</template>
```

### Vista con Acciones Personalizadas (Usuario con Cambiar Contraseña)

```vue
<template>
    <CrudView :config="config">
        <!-- Acciones personalizadas en la tabla -->
        <template #actions="{ item }">
            <div class="d-flex align-center ga-1">
                <v-btn
                    icon="mdi-pencil"
                    size="small"
                    @click="crudModal.openEditModal(item)"
                />
                <v-menu>
                    <template #activator="{ props: menuProps }">
                        <v-btn v-bind="menuProps" icon="mdi-dots-vertical" />
                    </template>
                    <v-list>
                        <v-list-item
                            prepend-icon="mdi-key"
                            title="Cambiar Contraseña"
                            @click="handleChangePassword(item)"
                        />
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
    </CrudView>
</template>
```

---

## ⚠️ CASOS ESPECIALES

### Vista Muy Compleja (Roles con Permisos, Estudiante con Tabs)

Para vistas muy complejas que no encajan en el patrón estándar:

**Opción 1:** Mantener implementación custom si es necesario
```vue
<!-- Roles/Index.vue puede mantener su implementación custom -->
<!-- porque tiene lógica muy específica de permisos -->
```

**Opción 2:** Usar CrudView parcialmente
```vue
<!-- Usar CrudView para la tabla -->
<!-- Pero manejar el modal de forma custom -->
```

---

## ✅ CHECKLIST DE MIGRACIÓN

- [ ] Crear objeto `config` con toda la configuración
- [ ] Mover `headers` a `config.columns`
- [ ] Mover `formConfig` a `config.formConfig`
- [ ] Eliminar imports innecesarios
- [ ] Eliminar composables (`useVuetifyTable`, `useCrudModal`)
- [ ] Eliminar funciones auxiliares (`updateSearchValue`, etc.)
- [ ] Reemplazar template con `<CrudView>`
- [ ] Mover formulario al slot `#form`
- [ ] Mover personalizaciones de celdas a slots `#item-{key}`
- [ ] Probar crear, editar, eliminar
- [ ] Probar búsqueda, exportación, impresión

---

## 🎯 BENEFICIOS INMEDIATOS

1. **Menos código:** De 300-500 líneas a 50-100 líneas
2. **Consistencia:** Mismo comportamiento en todas las vistas
3. **Mantenibilidad:** Cambios en un solo lugar
4. **Type-safe:** Generics de TypeScript
5. **Menos bugs:** Lógica probada y centralizada

---

**¿Listo para migrar?** Empieza con vistas simples como `Areas`, `Lugares` o `TipoPersonal`.
