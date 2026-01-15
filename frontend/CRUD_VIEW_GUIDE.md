# 📘 Guía de Uso del Componente CrudView

## Introducción

`CrudView` es un componente genérico que encapsula toda la lógica común de vistas CRUD, eliminando ~80% de código duplicado en las vistas de configuración.

## Beneficios

- ✅ **Reducción de código:** De ~300-500 líneas a ~50-100 líneas por vista
- ✅ **Consistencia:** Mismo comportamiento en todas las vistas
- ✅ **Mantenibilidad:** Cambios en un solo lugar afectan todas las vistas
- ✅ **Type-safe:** Usa generics de TypeScript para seguridad de tipos

## Estructura Básica

```vue
<script setup lang="ts">
import CrudView from '@/components/Crud/CrudView.vue';
import type { Entity } from '@/types/configuracion';

const config = {
    entityName: 'entidad',
    title: 'Título de la Página',
    description: 'Descripción opcional',
    apiEndpoint: '/configuracion/entidades',
    searchFields: ['nombre'] as (keyof Entity)[],
    columns: [
        // Definición de columnas
    ],
    formConfig: {
        // Configuración del formulario
    },
};
</script>

<template>
    <CrudView :config="config">
        <!-- Slots personalizados -->
    </CrudView>
</template>
```

## Configuración Completa

### Config Básico

```typescript
const config: CrudViewConfig<Entity> = {
    // Nombre de la entidad (para mensajes)
    entityName: 'área',
    
    // Título y descripción de la página
    title: 'Áreas',
    description: 'Gestiona las áreas de la organización',
    
    // Endpoint de la API
    apiEndpoint: '/configuracion/areas',
    
    // Campos en los que se puede buscar
    searchFields: ['nombre', 'descripcion'] as (keyof Entity)[],
    
    // Columnas de la tabla
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
            key: 'estado',
            title: 'ESTADO',
            sortable: true,
            align: 'center' as const,
            width: '120px',
        },
    ],
    
    // Configuración del formulario
    formConfig: {
        // Valores iniciales del formulario
        initialValues: {
            nombre: '',
            descripcion: '',
        },
        
        // Transforma el formulario en payload para la API
        getPayload: (form) => ({
            nombre: String(form.nombre).trim(),
            descripcion: form.descripcion ? String(form.descripcion).trim() : null,
        }),
        
        // Validación del formulario
        validate: (form) => {
            if (!String(form.nombre).trim()) {
                return 'El nombre es obligatorio.';
            }
            return null;
        },
        
        // Pobla el formulario al editar
        populateForm: (item: Entity, form: Record<string, unknown>) => {
            form.nombre = item.nombre;
            form.descripcion = item.descripcion ?? '';
        },
        
        // Resetea el formulario
        resetForm: (form: Record<string, unknown>) => {
            form.nombre = '';
            form.descripcion = '';
        },
    },
    
    // Configuración de paginación/server-side (opcional)
    serverSidePagination: false,
    serverSideSorting: false,
    serverSideSearch: false,
    
    // Callback después de guardar (opcional)
    onSuccess: () => {
        console.log('Guardado exitosamente');
    },
};
```

## Slots Disponibles

### `form` - Formulario Personalizado

```vue
<template #form="{ form }">
    <v-container fluid class="pa-4">
        <v-form @submit.prevent>
            <v-text-field
                v-model="form.nombre"
                label="Nombre"
                variant="outlined"
                density="compact"
            />
            <!-- Más campos -->
        </v-form>
    </v-container>
</template>
```

### `actions` - Acciones Personalizadas en Tabla

```vue
<template #actions="{ item }">
    <div class="d-flex align-center ga-1">
        <v-btn
            icon="mdi-pencil"
            size="small"
            @click="crudModal.openEditModal(item)"
        />
        <!-- Más acciones -->
    </div>
</template>
```

### `item-{columnKey}` - Personalización de Celdas

```vue
<template #item-estado="{ item }">
    <v-chip
        :color="formatStatusChip(item.estado).color"
        size="small"
        variant="flat"
    >
        {{ formatStatusChip(item.estado).label }}
    </v-chip>
</template>

<template #item-descripcion="{ value }">
    <div class="text-body-2" style="max-width: 400px;">
        {{ value || '—' }}
    </div>
</template>
```

### `header-actions` - Acciones Adicionales en Header

```vue
<template #header-actions>
    <v-btn
        color="secondary"
        prepend-icon="mdi-upload"
        @click="handleImport"
    >
        Importar
    </v-btn>
</template>
```

## Ejemplo Completo

Ver `frontend/src/views/Configuracion/Areas/Index.refactored.vue` para un ejemplo completo.

## Migración desde Vista Actual

### Antes (300+ líneas)

```vue
<script setup lang="ts">
// ... imports ...
const saveForm = reactive({ /* ... */ });
const headers = [ /* ... */ ];
const table = useVuetifyTable({ /* ... */ });
const crudModal = useCrudModal({ /* ... */ });
// ... más código ...
</script>

<template>
    <AuthenticatedLayout>
        <!-- ... mucho código repetido ... -->
    </AuthenticatedLayout>
</template>
```

### Después (50-100 líneas)

```vue
<script setup lang="ts">
import CrudView from '@/components/Crud/CrudView.vue';
const config = { /* ... configuración ... */ };
</script>

<template>
    <CrudView :config="config">
        <!-- Solo personalizaciones específicas -->
    </CrudView>
</template>
```

## Notas Importantes

1. **Type Safety:** El componente usa generics, asegúrate de especificar el tipo correcto
2. **Form State:** El formulario es reactivo, usa `v-model` normalmente
3. **Slots:** Los slots son opcionales, solo úsalos cuando necesites personalización
4. **API Endpoints:** Deben seguir el patrón REST estándar:
   - `GET /api/endpoint` - Listar
   - `POST /api/endpoint` - Crear
   - `POST /api/endpoint/{id}` - Actualizar
   - `DELETE /api/endpoint/{id}` - Eliminar

## Próximos Pasos

1. Migrar vistas simples primero (Areas, Lugares, TipoPersonal)
2. Luego vistas más complejas (Roles, Usuario, Estudiante)
3. Vistas muy complejas pueden mantener implementación custom si es necesario

---

**Última actualización:** 2024
