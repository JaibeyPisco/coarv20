# 📘 Guía Completa de Migración a Vuetify 3

## 📋 Tabla de Contenidos

1. [Análisis Técnico del Proyecto Actual](#análisis-técnico-del-proyecto-actual)
2. [Arquitectura Actual](#arquitectura-actual)
3. [Dependencias y Tecnologías](#dependencias-y-tecnologías)
4. [Análisis de Componentes UI](#análisis-de-componentes-ui)
5. [Puntos Críticos de Migración](#puntos-críticos-de-migración)
6. [Plan de Migración Paso a Paso](#plan-de-migración-paso-a-paso)
7. [Ejemplos de Migración](#ejemplos-de-migración)
8. [Checklist de Migración](#checklist-de-migración)
9. [Notas Importantes](#notas-importantes)

---

## 🔍 Análisis Técnico del Proyecto Actual

### Resumen Ejecutivo

El proyecto **COAR** es una aplicación Vue 3 con TypeScript que utiliza:
- **Framework UI**: Tabler (CSS Framework basado en Bootstrap)
- **Sistema de Estilos**: Tailwind CSS 4.1.17
- **Tablas**: Tabulator Tables 6.3.1
- **Estado**: Pinia 3.0.4
- **Routing**: Vue Router 4.6.3
- **Build Tool**: Vite 7.2.4

### Estructura del Proyecto

```
frontend/
├── src/
│   ├── api/                    # Configuración de Axios y servicios API
│   │   ├── axios.ts           # Instancia de axios configurada
│   │   ├── interceptors/      # Interceptores (auth, error, retry)
│   │   └── services/          # Servicios de API (auth, usuario)
│   │
│   ├── components/            # Componentes reutilizables
│   │   ├── Layouts/          # Layouts principales
│   │   │   ├── AuthenticatedLayout.vue
│   │   │   ├── GuestLayout.vue
│   │   │   └── Partial/      # AppSidebar, AppTopbar
│   │   ├── Partial/          # AppModal.vue
│   │   └── Table/            # TableCard.vue
│   │
│   ├── composables/          # Composables reutilizables
│   │   ├── useAuthReady.ts
│   │   ├── useCrudModal.ts
│   │   ├── useImageUpload.ts
│   │   ├── useMenuPermissions.ts
│   │   ├── useSidebarDropdown.ts
│   │   ├── useSidebarVisibility.ts
│   │   ├── useTableActions.ts
│   │   ├── useTablerAssets.ts
│   │   ├── useTabulator.ts
│   │   ├── useTabulatorDark.ts
│   │   └── useTabulatorTable.ts
│   │
│   ├── config/               # Configuración
│   │   ├── constants.ts
│   │   └── index.ts
│   │
│   ├── router/               # Configuración de rutas
│   │   ├── guards/           # auth.guard.ts, guest.guard.ts
│   │   ├── routes/           # Definición de rutas por módulo
│   │   └── index.ts
│   │
│   ├── stores/               # Stores de Pinia
│   │   ├── auth.ts
│   │   └── ui.store.ts
│   │
│   ├── types/                # Definiciones de tipos TypeScript
│   │   ├── auth/
│   │   ├── configuracion/
│   │   ├── operacion/
│   │   ├── reportes/
│   │   └── sidebar.ts
│   │
│   ├── utils/                # Utilidades y helpers
│   │   ├── HelperDates.ts
│   │   ├── logger.ts
│   │   ├── notificacion.ts
│   │   ├── tableHelpers.ts
│   │   └── tabulatorTheme.ts
│   │
│   ├── views/                # Páginas/Vistas
│   │   ├── Auth/
│   │   ├── Configuracion/   # 14 vistas CRUD
│   │   ├── Dashboard.vue
│   │   ├── NotFound.vue
│   │   ├── Operacion/
│   │   ├── Profile/
│   │   └── Reporte/
│   │
│   ├── App.vue               # Componente raíz
│   ├── main.ts               # Punto de entrada
│   └── style.css             # Estilos globales (Tailwind + Tabler)
│
├── public/
│   └── tabler/              # Assets estáticos de Tabler
│       ├── css/
│       ├── icons/
│       └── js/
│
├── index.html               # HTML principal con referencias a Tabler
├── vite.config.ts          # Configuración de Vite
├── tailwind.config.js      # Configuración de Tailwind
├── tsconfig.json           # Configuración de TypeScript
└── package.json            # Dependencias del proyecto
```

---

## 🏗️ Arquitectura Actual

### 1. Punto de Entrada (`main.ts`)

**Propósito**: Inicializa la aplicación Vue con todas las dependencias.

**Dependencias actuales**:
- Vue 3 con Composition API
- Pinia para estado global
- Vue Router para navegación
- vue-select para selects personalizados
- Sistema de notificaciones personalizado

**Archivo crítico**: `main.ts`

```typescript
// Estado actual
import { createApp } from 'vue';
import vSelect from 'vue-select';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';
import './style.css';
import { notificacion } from './utils/notificacion';
import 'vue-select/dist/vue-select.css';
```

### 2. Sistema de Layouts

#### AuthenticatedLayout.vue
**Propósito**: Layout principal para usuarios autenticados.

**Componentes clave**:
- `AppSidebar`: Sidebar vertical con navegación (usa Tabler)
- `AppTopbar`: Barra superior con usuario y tema (usa Tabler)
- Sistema de slots para header y contenido

**Dependencias de Tabler**:
- Clases CSS: `navbar`, `navbar-vertical`, `navbar-dark-neutral`
- Scripts: `tabler-theme.min.js`, `tabler.min.js`
- CSS: `tabler.min.css`, `tabler-themes.min.css`

#### GuestLayout.vue
**Propósito**: Layout para páginas públicas (login, registro).

**Dependencias**:
- Tailwind CSS para estilos (`flex`, `min-h-screen`, `bg-gray-100`)

### 3. Sistema de Componentes

#### TableCard.vue
**Propósito**: Wrapper para tablas Tabulator con toolbar y búsqueda.

**Características**:
- Toolbar con acciones (imprimir, exportar, mostrar columnas)
- Búsqueda integrada
- Footer con resumen de registros
- Overlay de carga

**Dependencias**:
- Tabler CSS para estilos (`card`, `card-header`, `card-body`)
- Tabulator para la tabla interna

#### AppModal.vue
**Propósito**: Modal personalizado con Teleport.

**Características**:
- Sistema de slots (header, body, footer)
- Tamaños configurables (sm, md, lg, xl, full)
- Manejo de backdrop y tecla Escape
- Estilos personalizados con variables CSS

**Dependencias**:
- Estilos propios (no depende directamente de Tabler, pero usa variables CSS de Tabler)

### 4. Sistema de Composables

#### useTabulatorTable.ts
**Propósito**: Composable para gestionar tablas Tabulator con funcionalidades CRUD.

**Funcionalidades**:
- Inicialización de tabla
- Carga de datos via AJAX
- Búsqueda y filtrado
- Exportación a Excel
- Impresión
- Gestión de visibilidad de columnas

**Dependencias críticas**:
- `tabulator-tables`: Librería de tablas
- `xlsx`: Para exportación Excel

#### useCrudModal.ts
**Propósito**: Composable para gestionar modales CRUD.

**Funcionalidades**:
- Gestión de estado de modales (crear/editar/eliminar)
- Validación de formularios
- Llamadas a API
- Manejo de errores y notificaciones

**Dependencias**:
- Sistema de notificaciones personalizado

#### useTablerAssets.ts
**Propósito**: Carga dinámica de assets de Tabler (CSS y JS).

**Funcionalidades**:
- Inyección de links CSS de Tabler
- Inyección de scripts de Tabler
- Limpieza al desmontar componente

**⚠️ CRÍTICO**: Este composable será **eliminado** en la migración a Vuetify.

### 5. Sistema de Estilos

#### style.css
**Propósito**: Estilos globales y configuración de temas.

**Contenido**:
- Directivas de Tailwind (`@tailwind base`, `@tailwind components`, `@tailwind utilities`)
- Importación de estilos de Tabulator
- Variables CSS para tema dark (basadas en Tabler)
- Estilos personalizados para tablas Tabulator en modo dark
- Estilos para componentes personalizados (`.actions-menu`, `.table-card`)

**Dependencias**:
- Tailwind CSS 4.1.17
- Tabulator CSS
- Variables CSS de Tabler (`--tblr-*`)

### 6. Sistema de Rutas

**Estructura**:
- Rutas agrupadas por módulo (`auth.routes.ts`, `configuracion.routes.ts`, etc.)
- Guards de navegación (`auth.guard.ts`, `guest.guard.ts`)
- Meta fields para protección de rutas (`requiresAuth`, `requiresGuest`)

**No requiere cambios** para la migración a Vuetify.

### 7. Sistema de Estado (Pinia)

#### auth.ts Store
**Propósito**: Gestión de autenticación.

**Estado**:
- `user`: Usuario actual
- `token`: Token de autenticación
- `loading`: Estado de carga
- `isAuthenticated`: Computed para verificar autenticación

**No requiere cambios** para la migración a Vuetify.

#### ui.store.ts
**Propósito**: Estado de UI (sidebar, modales, tema).

**Estado**:
- `sidebarOpen`: Estado del sidebar
- `sidebarCollapsed`: Estado de colapso del sidebar
- `activeModals`: Set de modales activos
- `globalLoading`: Estado de carga global

**⚠️ REQUIERE ADAPTACIÓN**: Algunos métodos pueden necesitar ajustes para trabajar con Vuetify.

---

## 📦 Dependencias y Tecnologías

### Dependencias Actuales (package.json)

#### Dependencias de Producción
```json
{
  "@vueuse/core": "^14.0.0",        // Utilidades Vue (puede mantenerse)
  "axios": "^1.13.2",               // HTTP client (se mantiene)
  "pinia": "^3.0.4",                // Estado global (se mantiene)
  "tabulator-tables": "^6.3.1",     // Tablas (se mantiene)
  "vue": "^3.5.24",                 // Framework (se mantiene)
  "vue-router": "^4.6.3",           // Routing (se mantiene)
  "vue-select": "^4.0.0-beta.6",    // Selects (puede reemplazarse con v-select de Vuetify)
  "xlsx": "^0.18.5"                 // Exportación Excel (se mantiene)
}
```

#### Dependencias de Desarrollo
```json
{
  "@tabler/icons-webfont": "^3.35.0",     // ⚠️ ELIMINAR (reemplazar con Material Design Icons)
  "@tailwindcss/forms": "^0.5.10",        // ⚠️ ELIMINAR (Vuetify tiene sus propios formularios)
  "@tailwindcss/postcss": "^4.1.17",      // ⚠️ ELIMINAR (Vuetify no usa Tailwind)
  "tailwindcss": "^4.1.17",               // ⚠️ ELIMINAR (Vuetify tiene su propio sistema de estilos)
  // ... otras dependencias se mantienen
}
```

### Tecnologías Clave

1. **Vue 3.5.24** (Composition API)
   - ✅ Compatible con Vuetify 3
   - ✅ No requiere cambios

2. **TypeScript 5.9.3**
   - ✅ Compatible con Vuetify 3
   - ✅ Vuetify tiene tipos TypeScript completos

3. **Vite 7.2.4**
   - ✅ Compatible con Vuetify 3
   - ✅ Requiere configuración adicional para Vuetify

4. **Pinia 3.0.4**
   - ✅ Compatible con Vuetify 3
   - ✅ No requiere cambios

5. **Vue Router 4.6.3**
   - ✅ Compatible con Vuetify 3
   - ✅ No requiere cambios

6. **Tabulator Tables 6.3.1**
   - ✅ Se mantiene (Vuetify tiene `v-data-table` pero Tabulator es más potente)
   - ⚠️ Puede requerir ajustes de estilos para integrarse con Vuetify

---

## 🎨 Análisis de Componentes UI

### Componentes que Dependen de Tabler

#### 1. AuthenticatedLayout.vue
**Dependencias Tabler**:
- Clases CSS: `navbar`, `navbar-vertical`, `navbar-expand-lg`, `navbar-dark-neutral`
- Estructura HTML específica de Tabler
- Scripts de Tabler para funcionalidad

**Migración a Vuetify**:
- Reemplazar con `v-navigation-drawer` (sidebar)
- Reemplazar con `v-app-bar` (topbar)
- Usar `v-app` como contenedor principal

#### 2. AppSidebar.vue
**Dependencias Tabler**:
- Clases CSS: `nav-item`, `nav-link`, `dropdown-menu`
- Bootstrap dropdowns (`data-bs-toggle="dropdown"`)
- Scripts de Tabler para dropdowns

**Migración a Vuetify**:
- Reemplazar con `v-list` y `v-list-item`
- Usar `v-menu` para dropdowns
- Usar `v-navigation-drawer` como contenedor

#### 3. AppTopbar.vue
**Dependencias Tabler**:
- Clases CSS: `navbar`, `navbar-expand-md`
- Bootstrap dropdowns para menú de usuario
- Sistema de temas de Tabler (`tabler-theme.min.js`)

**Migración a Vuetify**:
- Reemplazar con `v-app-bar`
- Usar `v-menu` para menú de usuario
- Usar sistema de temas de Vuetify (`useTheme`)

#### 4. TableCard.vue
**Dependencias Tabler**:
- Clases CSS: `card`, `card-header`, `card-body`, `card-footer`
- Botones de Tabler (`btn`, `btn-outline-secondary`)
- Inputs de Tabler (`form-control`, `input-icon`)

**Migración a Vuetify**:
- Reemplazar con `v-card`, `v-card-title`, `v-card-text`
- Usar `v-btn` para botones
- Usar `v-text-field` para búsqueda

#### 5. Vistas CRUD (ej: Areas/Index.vue)
**Dependencias Tabler**:
- Clases CSS: `page-header`, `page-title`, `btn`, `form-control`, `form-label`
- Estructura de página de Tabler (`page`, `page-wrapper`, `page-body`)

**Migración a Vuetify**:
- Reemplazar con `v-container`, `v-row`, `v-col`
- Usar `v-btn` para botones
- Usar `v-text-field`, `v-textarea` para formularios
- Usar `v-dialog` para modales (reemplazar AppModal)

### Componentes que Dependen de Tailwind

#### 1. GuestLayout.vue
**Dependencias Tailwind**:
- Clases: `flex`, `min-h-screen`, `bg-gray-100`, `pt-6`, `sm:justify-center`

**Migración a Vuetify**:
- Reemplazar con clases de utilidad de Vuetify o usar `v-container` y `v-row`

### Estilos Personalizados

#### Variables CSS de Tabler
El proyecto usa extensivamente variables CSS de Tabler:
- `--tblr-body-bg`
- `--tblr-card-bg`
- `--tblr-border-color`
- `--tblr-nav-link-color`
- etc.

**Migración a Vuetify**:
- Reemplazar con variables CSS de Vuetify (`--v-theme-*`)
- Configurar tema personalizado en Vuetify

#### Estilos para Tabulator en modo dark
El proyecto tiene estilos extensos para Tabulator en modo dark que usan variables de Tabler.

**Migración a Vuetify**:
- Adaptar estilos para usar variables de Vuetify
- Considerar usar tema dark de Vuetify

---

## ⚠️ Puntos Críticos de Migración

### 1. Eliminación de Tabler

**Impacto**: ALTO

**Archivos afectados**:
- `index.html`: Eliminar referencias a CSS y JS de Tabler
- `src/components/Layouts/AuthenticatedLayout.vue`: Reescritura completa
- `src/components/Layouts/Partial/AppSidebar.vue`: Reescritura completa
- `src/components/Layouts/Partial/AppTopbar.vue`: Reescritura completa
- `src/composables/useTablerAssets.ts`: **ELIMINAR**
- `src/style.css`: Eliminar variables CSS de Tabler, adaptar estilos

**Riesgos**:
- Pérdida de funcionalidad de sidebar/topbar
- Pérdida de sistema de temas
- Estilos rotos en todas las vistas

### 2. Eliminación de Tailwind CSS

**Impacto**: MEDIO

**Archivos afectados**:
- `tailwind.config.js`: **ELIMINAR**
- `postcss.config.js`: Modificar (eliminar plugin de Tailwind)
- `src/style.css`: Eliminar directivas de Tailwind
- `GuestLayout.vue`: Reemplazar clases Tailwind

**Riesgos**:
- Estilos utilitarios perdidos
- Necesidad de reescribir estilos con Vuetify

### 3. Migración de Modales

**Impacto**: MEDIO

**Archivos afectados**:
- `src/components/Partial/AppModal.vue`: Reemplazar con `v-dialog`
- Todas las vistas que usan `AppModal`: Actualizar sintaxis

**Riesgos**:
- Cambios en API de modales
- Necesidad de actualizar todas las vistas

### 4. Migración de Formularios

**Impacto**: MEDIO

**Archivos afectados**:
- Todas las vistas con formularios (14 vistas de Configuración + otras)

**Cambios necesarios**:
- `input` → `v-text-field`
- `textarea` → `v-textarea`
- `select` → `v-select` (reemplazar vue-select)
- `form-label` → `v-label`
- Validación: Adaptar a sistema de validación de Vuetify

**Riesgos**:
- Pérdida de validación existente
- Necesidad de reescribir todos los formularios

### 5. Integración de Tabulator con Vuetify

**Impacto**: BAJO-MEDIO

**Consideraciones**:
- Tabulator puede seguir funcionando dentro de componentes Vuetify
- Necesidad de ajustar estilos para que coincidan con tema de Vuetify
- Considerar usar `v-data-table` de Vuetify en el futuro (opcional)

### 6. Sistema de Notificaciones

**Impacto**: BAJO

**Archivos afectados**:
- `src/utils/notificacion.ts`: Adaptar para usar `v-snackbar` de Vuetify

**Riesgos**:
- Cambios en API de notificaciones
- Necesidad de actualizar todas las llamadas

### 7. Sistema de Temas (Dark/Light)

**Impacto**: MEDIO

**Archivos afectados**:
- `AppTopbar.vue`: Adaptar toggle de tema para usar `useTheme` de Vuetify
- `src/style.css`: Eliminar estilos de tema dark de Tabler

**Riesgos**:
- Pérdida de configuración de tema actual
- Necesidad de reconfigurar tema dark

---

## 📋 Plan de Migración Paso a Paso

### Fase 1: Preparación y Configuración

#### Paso 1.1: Instalar Vuetify
```bash
cd frontend
npm install vuetify@^3.5.0
npm install @mdi/font
```

#### Paso 1.2: Configurar Vuetify en main.ts
```typescript
// main.ts
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import 'vuetify/styles';
import '@mdi/font/css/materialdesignicons.css';

const vuetify = createVuetify({
  components,
  directives,
  theme: {
    defaultTheme: 'light',
    themes: {
      light: {
        colors: {
          primary: '#1976D2',
          secondary: '#424242',
          accent: '#82B1FF',
          error: '#FF5252',
          info: '#2196F3',
          success: '#4CAF50',
          warning: '#FFC107',
        },
      },
      dark: {
        colors: {
          primary: '#2196F3',
          secondary: '#424242',
          accent: '#FF4081',
          error: '#FF5252',
          info: '#2196F3',
          success: '#4CAF50',
          warning: '#FFC107',
        },
      },
    },
  },
});

const app = createApp(App);
app.use(pinia);
app.use(router);
app.use(vuetify);
app.mount('#app');
```

#### Paso 1.3: Actualizar index.html
```html
<!-- Eliminar referencias a Tabler -->
<!-- Eliminar referencias a Tailwind si se elimina completamente -->
<!-- Mantener solo lo esencial -->
```

#### Paso 1.4: Configurar Vite para Vuetify
```typescript
// vite.config.ts
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import vuetify from 'vite-plugin-vuetify';

export default defineConfig({
  plugins: [
    vue(),
    vuetify({ autoImport: true }),
  ],
  // ... resto de configuración
});
```

### Fase 2: Migración de Layouts

#### Paso 2.1: Crear nuevo AuthenticatedLayout con Vuetify
```vue
<template>
  <v-app>
    <AppSidebar />
    <v-main>
      <AppTopbar />
      <v-container v-if="$slots.header" class="py-4">
        <slot name="header" />
      </v-container>
      <v-container>
        <slot />
      </v-container>
    </v-main>
  </v-app>
</template>
```

#### Paso 2.2: Migrar AppSidebar a Vuetify
```vue
<template>
  <v-navigation-drawer
    v-model="drawer"
    :permanent="isDesktop"
    :temporary="!isDesktop"
  >
    <v-list>
      <v-list-item prepend-icon="mdi-view-dashboard" title="Dashboard" to="/dashboard" />
      <!-- ... más items -->
    </v-list>
  </v-navigation-drawer>
</template>
```

#### Paso 2.3: Migrar AppTopbar a Vuetify
```vue
<template>
  <v-app-bar>
    <v-app-bar-nav-icon @click="toggleDrawer" />
    <v-toolbar-title>{{ companyDisplayName }}</v-toolbar-title>
    <v-spacer />
    <v-btn icon @click="toggleTheme">
      <v-icon>{{ isDark ? 'mdi-weather-sunny' : 'mdi-weather-night' }}</v-icon>
    </v-btn>
    <v-menu>
      <template #activator="{ props }">
        <v-btn icon v-bind="props">
          <v-avatar>
            <v-img v-if="user.avatar_url" :src="user.avatar_url" />
            <span v-else>{{ user.initials }}</span>
          </v-avatar>
        </v-btn>
      </template>
      <v-list>
        <v-list-item to="/profile">Perfil</v-list-item>
        <v-list-item @click="handleLogout">Cerrar sesión</v-list-item>
      </v-list>
    </v-menu>
  </v-app-bar>
</template>
```

### Fase 3: Migración de Componentes

#### Paso 3.1: Migrar AppModal a v-dialog
```vue
<template>
  <v-dialog
    :model-value="open"
    :max-width="size === 'sm' ? '420' : size === 'lg' ? '720' : '540'"
    @update:model-value="emit('close')"
  >
    <v-card>
      <v-card-title v-if="title || $slots.header">
        <slot name="header">{{ title }}</slot>
      </v-card-title>
      <v-card-text>
        <slot name="body" />
      </v-card-text>
      <v-card-actions v-if="$slots.footer">
        <slot name="footer" />
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
```

#### Paso 3.2: Migrar TableCard a componentes Vuetify
```vue
<template>
  <v-card class="table-card">
    <v-card-title>
      <div class="d-flex align-items-center justify-space-between">
        <slot name="actions" />
        <v-text-field
          v-model="searchValue"
          prepend-inner-icon="mdi-magnify"
          placeholder="Buscar..."
          density="compact"
          hide-details
        />
      </div>
    </v-card-title>
    <v-card-text>
      <div ref="tableEl" class="tabulator-wrapper"></div>
    </v-card-text>
    <v-card-actions>
      <slot name="footer-left" />
      <v-spacer />
      <slot name="footer-right" />
    </v-card-actions>
  </v-card>
</template>
```

### Fase 4: Migración de Vistas

#### Paso 4.1: Migrar una vista de ejemplo (Areas/Index.vue)
- Reemplazar clases Tabler con componentes Vuetify
- Actualizar formularios
- Actualizar modales
- Actualizar botones

#### Paso 4.2: Migrar resto de vistas
- Aplicar el mismo patrón a todas las vistas
- Priorizar vistas más usadas

### Fase 5: Limpieza y Optimización

#### Paso 5.1: Eliminar dependencias no usadas
```bash
npm uninstall @tabler/icons-webfont tailwindcss @tailwindcss/postcss @tailwindcss/forms
```

#### Paso 5.2: Limpiar archivos
- Eliminar `tailwind.config.js`
- Eliminar `src/composables/useTablerAssets.ts`
- Limpiar `src/style.css`

#### Paso 5.3: Actualizar sistema de notificaciones
- Migrar a `v-snackbar` de Vuetify

---

## 💡 Ejemplos de Migración

### Ejemplo 1: Migración de Botón

**Antes (Tabler)**:
```vue
<button type="button" class="btn btn-primary btn-sm">
  <i class="ti ti-plus"></i> Nuevo
</button>
```

**Después (Vuetify)**:
```vue
<v-btn color="primary" size="small" prepend-icon="mdi-plus">
  Nuevo
</v-btn>
```

### Ejemplo 2: Migración de Input

**Antes (Tabler)**:
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

**Después (Vuetify)**:
```vue
<v-text-field
  v-model="form.nombre"
  label="Nombre"
  placeholder="Ingrese nombre"
  required
  :rules="[v => !!v || 'El nombre es obligatorio']"
/>
```

### Ejemplo 3: Migración de Card

**Antes (Tabler)**:
```vue
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Título</h3>
  </div>
  <div class="card-body">
    Contenido
  </div>
  <div class="card-footer">
    Pie
  </div>
</div>
```

**Después (Vuetify)**:
```vue
<v-card>
  <v-card-title>Título</v-card-title>
  <v-card-text>Contenido</v-card-text>
  <v-card-actions>Pie</v-card-actions>
</v-card>
```

### Ejemplo 4: Migración de Modal

**Antes (AppModal)**:
```vue
<AppModal :open="showModal" title="Título" @close="showModal = false">
  <template #body>
    Contenido del modal
  </template>
  <template #footer>
    <button @click="showModal = false">Cerrar</button>
  </template>
</AppModal>
```

**Después (v-dialog)**:
```vue
<v-dialog v-model="showModal" max-width="540">
  <v-card>
    <v-card-title>Título</v-card-title>
    <v-card-text>Contenido del modal</v-card-text>
    <v-card-actions>
      <v-btn @click="showModal = false">Cerrar</v-btn>
    </v-card-actions>
  </v-card>
</v-dialog>
```

### Ejemplo 5: Migración de Lista de Navegación

**Antes (Tabler)**:
```vue
<ul class="navbar-nav">
  <li class="nav-item">
    <RouterLink class="nav-link" to="/dashboard">
      <i class="ti ti-dashboard"></i>
      Dashboard
    </RouterLink>
  </li>
</ul>
```

**Después (Vuetify)**:
```vue
<v-list>
  <v-list-item
    prepend-icon="mdi-view-dashboard"
    title="Dashboard"
    to="/dashboard"
  />
</v-list>
```

---

## ✅ Checklist de Migración

### Pre-migración
- [ ] Crear rama de desarrollo para migración
- [ ] Hacer backup completo del proyecto
- [ ] Documentar funcionalidades actuales
- [ ] Crear tests (si no existen)

### Fase 1: Configuración
- [ ] Instalar Vuetify 3
- [ ] Instalar Material Design Icons
- [ ] Configurar Vuetify en `main.ts`
- [ ] Configurar plugin de Vuetify en `vite.config.ts`
- [ ] Actualizar `index.html` (eliminar Tabler)
- [ ] Configurar tema personalizado de Vuetify

### Fase 2: Layouts
- [ ] Migrar `AuthenticatedLayout.vue`
- [ ] Migrar `AppSidebar.vue` a `v-navigation-drawer`
- [ ] Migrar `AppTopbar.vue` a `v-app-bar`
- [ ] Migrar `GuestLayout.vue`
- [ ] Probar navegación y responsive

### Fase 3: Componentes Base
- [ ] Migrar `AppModal.vue` a `v-dialog`
- [ ] Migrar `TableCard.vue`
- [ ] Actualizar sistema de notificaciones
- [ ] Migrar sistema de temas

### Fase 4: Vistas
- [ ] Migrar `Dashboard.vue`
- [ ] Migrar vistas de Auth (Login)
- [ ] Migrar vistas de Configuración (14 vistas)
  - [ ] Areas/Index.vue
  - [ ] Lugares/Index.vue
  - [ ] Empresa/Index.vue
  - [ ] ... (resto de vistas)
- [ ] Migrar vistas de Operación
- [ ] Migrar vistas de Reportes
- [ ] Migrar `Profile.vue`
- [ ] Migrar `NotFound.vue`

### Fase 5: Composables y Utilidades
- [ ] Eliminar `useTablerAssets.ts`
- [ ] Actualizar `useCrudModal.ts` (si es necesario)
- [ ] Actualizar `useTabulatorTable.ts` (ajustar estilos)
- [ ] Actualizar `notificacion.ts` para usar Vuetify

### Fase 6: Estilos
- [ ] Eliminar Tailwind CSS
- [ ] Eliminar variables CSS de Tabler
- [ ] Adaptar estilos de Tabulator para Vuetify
- [ ] Configurar tema dark de Vuetify
- [ ] Limpiar `style.css`

### Fase 7: Limpieza
- [ ] Eliminar dependencias no usadas
- [ ] Eliminar archivos de configuración obsoletos
- [ ] Actualizar documentación
- [ ] Limpiar imports no usados

### Fase 8: Testing
- [ ] Probar autenticación
- [ ] Probar navegación
- [ ] Probar CRUD en todas las vistas
- [ ] Probar responsive design
- [ ] Probar tema dark/light
- [ ] Probar exportación e impresión de tablas
- [ ] Probar validación de formularios

### Fase 9: Optimización
- [ ] Optimizar bundle size
- [ ] Verificar performance
- [ ] Ajustar estilos finales
- [ ] Documentar cambios

---

## 📝 Notas Importantes

### 1. Compatibilidad con Tabulator

**Recomendación**: Mantener Tabulator durante la migración inicial.

**Razón**: 
- Tabulator es más potente que `v-data-table` para casos complejos
- Ya está integrado y funcionando
- Puede migrarse gradualmente a `v-data-table` si se desea

**Acción**: Solo ajustar estilos para que coincidan con tema de Vuetify.

### 2. Sistema de Temas

**Importante**: Vuetify tiene su propio sistema de temas más robusto que Tabler.

**Ventajas**:
- Mejor integración con componentes
- Cambio de tema más fluido
- Mejor soporte para temas personalizados

**Acción**: Configurar tema personalizado en Vuetify basado en colores actuales.

### 3. Iconos

**Cambio**: De Tabler Icons a Material Design Icons.

**Impacto**: 
- Necesidad de reemplazar todos los iconos
- Sintaxis diferente: `ti ti-dashboard` → `mdi-view-dashboard`

**Acción**: Crear mapeo de iconos o buscar equivalentes en MDI.

### 4. Validación de Formularios

**Cambio**: Vuetify tiene sistema de validación integrado con `rules`.

**Ventaja**: Más robusto y consistente.

**Acción**: Migrar validaciones existentes a sistema de `rules` de Vuetify.

### 5. Responsive Design

**Nota**: Vuetify tiene sistema de breakpoints diferente a Tailwind/Tabler.

**Acción**: Usar props de Vuetify (`sm`, `md`, `lg`, etc.) en lugar de clases CSS.

### 6. Performance

**Consideración**: Vuetify puede aumentar el bundle size inicialmente.

**Mitigación**: 
- Usar tree-shaking (ya configurado con `autoImport: true`)
- Considerar lazy loading de componentes pesados
- Optimizar imports

### 7. Migración Gradual

**Recomendación**: Si es posible, hacer migración gradual por módulos.

**Estrategia**:
1. Migrar layouts primero
2. Migrar componentes base
3. Migrar vistas una por una
4. Mantener compatibilidad temporal si es necesario

### 8. Testing

**Crítico**: Probar exhaustivamente después de cada fase.

**Áreas clave**:
- Autenticación y autorización
- Navegación y routing
- Formularios y validación
- Tablas y exportación
- Modales y diálogos
- Responsive design
- Tema dark/light

### 9. Documentación

**Importante**: Actualizar toda la documentación del proyecto.

**Incluir**:
- Guía de componentes Vuetify usados
- Convenciones de código
- Patrones de migración aplicados
- Troubleshooting común

### 10. Rollback Plan

**Preparación**: Tener plan de rollback en caso de problemas críticos.

**Incluir**:
- Backup del código anterior
- Scripts de rollback
- Documentación de problemas encontrados

---

## 🚀 Comandos Útiles

### Instalación
```bash
cd frontend
npm install vuetify@^3.5.0 @mdi/font
```

### Desarrollo
```bash
npm run dev
```

### Build
```bash
npm run build
```

### Limpieza de dependencias
```bash
npm uninstall @tabler/icons-webfont tailwindcss @tailwindcss/postcss @tailwindcss/forms autoprefixer
```

---

## 📚 Recursos Adicionales

- [Documentación oficial de Vuetify 3](https://vuetifyjs.com/)
- [Material Design Icons](https://pictogrammers.com/library/mdi/)
- [Guía de migración de Vuetify 2 a 3](https://vuetifyjs.com/en/getting-started/upgrade-guide/)
- [Ejemplos de Vuetify](https://vuetifyjs.com/en/components/all/)

---

## ⚠️ Advertencias Finales

1. **NO eliminar Tabler hasta tener Vuetify completamente funcional**
2. **Hacer commits frecuentes durante la migración**
3. **Probar exhaustivamente cada componente migrado**
4. **Mantener comunicación con el equipo sobre cambios**
5. **Documentar decisiones de diseño durante la migración**

---

**Última actualización**: 2024
**Versión de Vuetify objetivo**: 3.5.0+
**Estado**: Plan de migración - Pendiente de ejecución
