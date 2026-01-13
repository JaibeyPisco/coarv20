# 📊 Progreso de Migración a Vuetify

## Estado Actual: 🟢 Fase 3 Completada

**Fecha de inicio**: 2024
**Rama**: `feature/migrate-to-vuetify`

---

## ✅ Fase 1: Preparación y Configuración - COMPLETADA

### Tareas Completadas

- [x] **1.1** Instalar Vuetify 3.5.0
- [x] **1.2** Instalar Material Design Icons (@mdi/font)
- [x] **1.3** Instalar vite-plugin-vuetify
- [x] **1.4** Configurar Vuetify en `main.ts`
  - Configuración de temas (light/dark)
  - Importación de componentes y directivas
  - Importación de estilos de Vuetify
  - Importación de Material Design Icons
- [x] **1.5** Configurar plugin en `vite.config.ts`
  - Habilitado auto-import de componentes
- [x] **1.6** Actualizar `App.vue` para usar `v-app`

### Cambios Realizados

#### `package.json`
```json
{
  "dependencies": {
    "vuetify": "^3.5.0",
    "@mdi/font": "^latest"
  },
  "devDependencies": {
    "vite-plugin-vuetify": "^latest"
  }
}
```

#### `src/main.ts`
- Importaciones de Vuetify agregadas
- Configuración de temas personalizada
- Plugin de Vuetify registrado en la app

#### `vite.config.ts`
- Plugin de Vuetify agregado con auto-import

#### `src/App.vue`
- Envuelto con `<v-app>` para estructura correcta de Vuetify

## ✅ Fase 2: Migración de Layouts - COMPLETADA

### Tareas Completadas

- [x] **2.1** Migrar `AppSidebar.vue` → `v-navigation-drawer`
  - Implementado con `v-list` y `v-list-group` para menús expandibles
  - Integrado sistema de permisos existente
  - Mapeo de iconos Tabler a Material Design Icons
  - Responsive con drawer temporal en móvil
  
- [x] **2.2** Migrar `AppTopbar.vue` → `v-app-bar`
  - Implementado con `v-app-bar` y `v-menu` para menú de usuario
  - Integrado sistema de temas de Vuetify (`useTheme`)
  - Botón de toggle de tema dark/light
  - Avatar de usuario con menú dropdown
  
- [x] **2.3** Migrar `AuthenticatedLayout.vue`
  - Integrado AppSidebar y AppTopbar migrados
  - Eliminada dependencia de `useTablerAssets`
  - Estructura adaptada a `v-app` de Vuetify
  - Loader de autenticación migrado a componentes Vuetify
  
- [x] **2.4** Migrar `GuestLayout.vue`
  - Convertido a `v-container` y `v-card` de Vuetify
  - Eliminadas clases de Tailwind
  
- [x] **2.5** Crear `iconMapper.ts`
  - Helper para mapear iconos Tabler a Material Design Icons
  - Facilita migración gradual de iconos

### Cambios Realizados

#### `src/components/Layouts/Partial/AppSidebar.vue`
- Reescrito completamente usando `v-navigation-drawer`
- Manejo automático de grupos expandibles
- Integración con sistema de permisos

#### `src/components/Layouts/Partial/AppTopbar.vue`
- Reescrito usando `v-app-bar`
- Sistema de temas migrado a `useTheme` de Vuetify
- Menú de usuario con `v-menu`

#### `src/components/Layouts/AuthenticatedLayout.vue`
- Eliminada dependencia de Tabler
- Estructura adaptada a Vuetify
- Loader migrado a `v-progress-circular`

#### `src/components/Layouts/GuestLayout.vue`
- Migrado a componentes Vuetify
- Eliminadas clases Tailwind

#### `src/utils/iconMapper.ts` (NUEVO)
- Mapeo de iconos Tabler → Material Design Icons
- Helper reutilizable para toda la aplicación

## ✅ Fase 3: Migración de Componentes Base - COMPLETADA

### Tareas Completadas

- [x] **3.1** Migrar `AppModal.vue` → `v-dialog`
  - Implementado con `v-dialog` nativo de Vuetify
  - Soporte para tamaños: sm, md, lg, xl, fullscreen
  - Props `persistent` y `scrollable`
  - Compatible con API anterior (props `open` y evento `close`)
  - Eliminados estilos personalizados, usa estilos de Vuetify
  
- [x] **3.2** Migrar `TableCard.vue` a componentes Vuetify
  - Reescrito usando `v-card`, `v-card-title`, `v-card-text`, `v-card-actions`
  - Toolbar con `v-btn` para acciones (imprimir, exportar)
  - Menú de columnas con `v-menu` y `v-list`
  - Búsqueda con `v-text-field` y prepend-icon
  - Overlay de carga con `v-overlay` y `v-progress-circular`
  - Eliminadas clases de Tabler
  
- [x] **3.3** Actualizar sistema de notificaciones a `v-snackbar`
  - Nuevo store `notifications.ts` con Pinia
  - Componente `AppNotifications.vue` global
  - Mantiene compatibilidad con API anterior (`notificacion()`)
  - Métodos helper: `success()`, `error()`, `warning()`, `info()`
  - Auto-remoción después de duración configurable
  - Iconos según tipo de notificación
  - Agregado a `App.vue` para funcionamiento global

### Cambios Realizados

#### `src/components/Partial/AppModal.vue`
- Reescrito completamente usando `v-dialog`
- Eliminados ~290 líneas de estilos personalizados
- API simplificada pero compatible

#### `src/components/Table/TableCard.vue`
- Migrado a componentes Vuetify
- Toolbar y búsqueda mejorados
- Overlay de carga más elegante

#### `src/stores/notifications.ts` (NUEVO)
- Store de Pinia para gestión de notificaciones
- Cola de notificaciones
- Métodos helper por tipo

#### `src/components/AppNotifications.vue` (NUEVO)
- Componente global para mostrar notificaciones
- Usa `v-snackbar` de Vuetify
- Integrado en `App.vue`

#### `src/utils/notificacion.ts`
- Actualizado para usar nuevo store
- Mantiene compatibilidad con código existente
- Mapea 'danger' → 'error' para Vuetify

## ✅ Fase 4: Migración de Vistas CRUD - EN PROGRESO

### Tareas Completadas

- [x] **4.1** Migrar `Dashboard.vue`
  - Header migrado a componentes Vuetify
  - Cards migrados a `v-card`
  - Eliminadas clases Tabler
  
- [x] **4.2** Migrar `Areas/Index.vue`
  - Completamente migrado a Vuetify
  - Formularios con validación usando `rules`
  - Modales actualizados
  
- [x] **4.3** Migrar `Lugares/Index.vue`
  - Completamente migrado a Vuetify
  - Mismo patrón que Areas
  
- [x] **4.4** Migrar `Empresa/Index.vue`
  - Formulario complejo migrado
  - Upload de imágenes con `v-file-input`
  - Preview con `v-img`

### Vistas Pendientes de Migración

**Configuración** (6 vistas restantes):
- [ ] EstadoMonitoreo/Index.vue
- [ ] TiposIncidencia/Index.vue
- [ ] TipoPersonal/Index.vue
- [ ] Personal/Index.vue
- [ ] Roles/Index.vue
- [ ] Usuario/Index.vue
- [ ] Estudiante/Index.vue (+ formularios relacionados)

**Otras vistas**:
- [ ] Operacion/NuevaIncidencia.vue
- [ ] Reporte/movimientoInformacion.vue
- [ ] Profile/*.vue
- [ ] Auth/*.vue

### Próximos Pasos

**Continuar Fase 4**: Migrar vistas restantes de Configuración
- Aplicar mismo patrón usado en Areas/Lugares
- Priorizar vistas más simples primero

---

## 📝 Notas

- **Tabler aún está presente**: Mantenemos Tabler temporalmente hasta completar la migración de layouts
- **Sin errores de compilación**: La aplicación compila correctamente con Vuetify instalado
- **Temas configurados**: Light y Dark themes están listos para usar

---

## 🚨 Advertencias

- ⚠️ **NO eliminar Tabler todavía**: Los layouts aún dependen de Tabler
- ⚠️ **Probar antes de continuar**: Verificar que la app sigue funcionando con Vuetify instalado

---

**Última actualización**: Fase 1 completada
**Siguiente fase**: Fase 2 - Migración de Layouts
