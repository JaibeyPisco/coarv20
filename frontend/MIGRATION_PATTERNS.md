# 📋 Patrones de Migración para Vistas CRUD

Este documento contiene los patrones comunes para migrar vistas CRUD de Tabler a Vuetify.

## Patrón 1: Header de Página

### Antes (Tabler)
```vue
<template #header>
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Bienvenido</div>
                <h2 class="page-title">Dashboard</h2>
            </div>
        </div>
    </div>
</template>
```

### Después (Vuetify)
```vue
<template #header>
    <div class="d-flex align-center justify-space-between flex-wrap ga-3">
        <div>
            <div class="text-caption text-medium-emphasis mb-1">Bienvenido</div>
            <h1 class="text-h4 font-weight-bold">Dashboard</h1>
        </div>
    </div>
</template>
```

## Patrón 2: Botón de Acción Principal

### Antes (Tabler)
```vue
<button
    type="button"
    class="btn btn-primary d-flex align-items-center gap-2 btn-md"
    @click="crudModal.openCreateModal"
>
    <i class="ti ti-plus"></i>
    Nuevo
</button>
```

### Después (Vuetify)
```vue
<v-btn
    color="primary"
    prepend-icon="mdi-plus"
    @click="crudModal.openCreateModal"
>
    Nuevo
</v-btn>
```

## Patrón 3: Input de Texto

### Antes (Tabler)
```vue
<div class="mb-3">
    <label class="form-label required" for="nombre">Nombre</label>
    <input
        id="nombre"
        v-model="form.nombre"
        type="text"
        class="form-control"
        placeholder="Ingrese nombre"
    />
</div>
```

### Después (Vuetify)
```vue
<v-text-field
    v-model="form.nombre"
    label="Nombre"
    placeholder="Ingrese nombre"
    :rules="[v => !!v || 'El nombre es obligatorio']"
    required
    class="mb-3"
/>
```

## Patrón 4: Textarea

### Antes (Tabler)
```vue
<div class="mb-3">
    <label class="form-label" for="descripcion">Descripción</label>
    <textarea
        id="descripcion"
        v-model="form.descripcion"
        class="form-control"
        rows="3"
    ></textarea>
</div>
```

### Después (Vuetify)
```vue
<v-textarea
    v-model="form.descripcion"
    label="Descripción"
    rows="3"
    auto-grow
/>
```

## Patrón 5: Botones en Footer de Modal

### Antes (Tabler)
```vue
<template #footer>
    <div class="d-flex justify-content-between w-100">
        <button
            type="button"
            class="btn btn-default btn-sm pull-left"
            @click="closeModal"
        >
            <i class="fa fa-times"></i> Cancelar
        </button>
        <button
            type="button"
            class="btn btn-primary btn-sm"
            :disabled="saving"
            @click="handleSave"
        >
            <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
            Guardar
        </button>
    </div>
</template>
```

### Después (Vuetify)
```vue
<template #footer>
    <div class="d-flex justify-space-between w-100">
        <v-btn
            variant="text"
            @click="closeModal"
        >
            Cancelar
        </v-btn>
        <v-btn
            color="primary"
            :loading="saving"
            :disabled="saving"
            @click="handleSave"
        >
            Guardar
        </v-btn>
    </div>
</template>
```

## Patrón 6: Card Simple

### Antes (Tabler)
```vue
<div class="row row-cards">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h3 class="card-title">Título</h3>
                <p class="text-secondary">Contenido</p>
            </div>
        </div>
    </div>
</div>
```

### Después (Vuetify)
```vue
<v-row>
    <v-col cols="12">
        <v-card elevation="1">
            <v-card-title>Título</v-card-title>
            <v-card-text>
                <p class="text-medium-emphasis mb-0">Contenido</p>
            </v-card-text>
        </v-card>
    </v-col>
</v-row>
```

## Patrón 7: Modal con AppModal

### Antes (Tabler)
```vue
<AppModal
    :open="showModal"
    title="Título"
    @close="showModal = false"
>
    <template #body>
        Contenido
    </template>
    <template #footer>
        Botones
    </template>
</AppModal>
```

### Después (Vuetify) - Compatible
```vue
<AppModal
    :open="showModal"
    title="Título"
    @update:open="showModal = $event"
>
    <template #body>
        Contenido
    </template>
    <template #footer>
        Botones
    </template>
</AppModal>
```

## Mapeo de Clases CSS

| Tabler/Tailwind | Vuetify |
|----------------|---------|
| `btn btn-primary` | `v-btn color="primary"` |
| `btn btn-outline-secondary` | `v-btn variant="outlined"` |
| `btn btn-danger` | `v-btn color="error"` |
| `btn btn-sm` | `v-btn size="small"` |
| `form-control` | `v-text-field` o `v-textarea` |
| `form-label` | `label` prop en `v-text-field` |
| `card` | `v-card` |
| `card-body` | `v-card-text` |
| `card-title` | `v-card-title` |
| `card-header` | `v-card-title` con `v-divider` |
| `card-footer` | `v-card-actions` |
| `row` | `v-row` |
| `col-12` | `v-col cols="12"` |
| `text-secondary` | `text-medium-emphasis` |
| `mb-3` | `class="mb-3"` (mantener utilidades) |
| `d-flex` | `class="d-flex"` (mantener utilidades) |
| `align-items-center` | `align-center` |
| `justify-content-between` | `justify-space-between` |
| `gap-2` | `ga-2` o `style="gap: 8px;"` |

## Mapeo de Iconos

| Tabler Icon | Material Design Icon |
|------------|---------------------|
| `ti ti-plus` | `mdi-plus` |
| `ti ti-edit` | `mdi-pencil` |
| `ti ti-trash` | `mdi-delete` |
| `ti ti-search` | `mdi-magnify` |
| `ti ti-printer` | `mdi-printer` |
| `ti ti-file-spreadsheet` | `mdi-file-excel` |
| `ti ti-eye` | `mdi-eye` |
| `ti ti-eye-off` | `mdi-eye-off` |
| `ti ti-times` | `mdi-close` |
| `ti ti-check` | `mdi-check` |

## Checklist de Migración por Vista

Para cada vista CRUD, verificar:

- [ ] Header migrado a componentes Vuetify
- [ ] Botones migrados a `v-btn`
- [ ] Formularios migrados a `v-text-field`, `v-textarea`, `v-select`
- [ ] Cards migrados a `v-card`
- [ ] Modales usando `AppModal` migrado
- [ ] Clases Tabler eliminadas
- [ ] Iconos mapeados a Material Design Icons
- [ ] Validación usando `rules` de Vuetify
- [ ] Estados de carga usando `loading` prop de `v-btn`
- [ ] Espaciado usando clases de Vuetify o utilidades

## Notas Importantes

1. **Mantener funcionalidad**: La lógica de negocio NO debe cambiar, solo la UI
2. **Validación**: Usar `rules` de Vuetify en lugar de validación manual
3. **Estados**: Usar `loading` prop en lugar de spinners manuales
4. **Compatibilidad**: AppModal mantiene compatibilidad con API anterior
5. **TableCard**: Ya está migrado, solo usar el componente
