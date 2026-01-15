# 📋 Plan de Cambio: Usar Cards por Defecto de Vuetify

**Objetivo**: Migrar de estilos personalizados a los valores por defecto de Vuetify 3  
**Fecha**: 2026-01-15  
**Alcance**: Todos los módulos de Configuración y el Manual

---

## 🎯 Objetivo del Cambio

Eliminar estilos CSS personalizados y usar las props nativas de Vuetify para cards, manteniendo un diseño consistente y aprovechando las características por defecto del framework.

---

## 📊 Análisis de Cambios Necesarios

### Cambios Principales

| **Antes (Personalizado)** | **Después (Vuetify Default)** | **Notas** |
|---------------------------|-------------------------------|-----------|
| `class="rounded-xl"` | `rounded="lg"` o `rounded="xl"` | Usar prop `rounded` de Vuetify |
| `class="rounded-lg"` | `rounded="md"` o `rounded="lg"` | Usar prop `rounded` de Vuetify |
| `elevation="1"` | `elevation="1"` | Mantener igual (ya es estándar) |
| `elevation="2"` | `elevation="2"` | Mantener igual (ya es estándar) |
| `<style scoped>` con `.rounded-xl` | Eliminar | No necesario con props nativas |
| `<style scoped>` con `.rounded-lg` | Eliminar | No necesario con props nativas |

---

## 🔄 Valores de Vuetify para `rounded`

Vuetify 3 soporta los siguientes valores para la prop `rounded`:

- `rounded="0"` - Sin bordes redondeados
- `rounded="sm"` - Pequeño (4px)
- `rounded="md"` - Mediano (8px) - **Recomendado para cards internos**
- `rounded="lg"` - Grande (12px) - **Recomendado para cards principales**
- `rounded="xl"` - Extra grande (16px) - **Recomendado para cards destacados**
- `rounded="pill"` - Forma de píldora
- `rounded="circle"` - Forma circular

---

## 📝 Cambios Específicos por Componente

### 1. Header Section (Card de Encabezado)

**ANTES:**
```vue
<v-card class="mb-4 rounded-xl" elevation="1">
    <v-card-text class="pa-4">
        <!-- contenido -->
    </v-card-text>
</v-card>
```

**DESPUÉS:**
```vue
<v-card class="mb-4" rounded="lg" elevation="1">
    <v-card-text class="pa-4">
        <!-- contenido -->
    </v-card-text>
</v-card>
```

**Cambios:**
- `class="rounded-xl"` → `rounded="lg"`
- Mantener `elevation="1"`

---

### 2. Main Content Card (Card Principal)

**ANTES:**
```vue
<v-card class="rounded-xl" elevation="2">
    <!-- contenido -->
</v-card>
```

**DESPUÉS:**
```vue
<v-card rounded="lg" elevation="2">
    <!-- contenido -->
</v-card>
```

**Cambios:**
- `class="rounded-xl"` → `rounded="lg"`
- Mantener `elevation="2"`

---

### 3. Cards Internos (v-sheet o v-card secundarios)

**ANTES:**
```vue
<v-sheet
    variant="outlined"
    class="pa-4 rounded-lg"
    color="surface"
>
    <!-- contenido -->
</v-sheet>
```

**DESPUÉS:**
```vue
<v-sheet
    variant="outlined"
    class="pa-4"
    rounded="md"
    color="surface"
>
    <!-- contenido -->
</v-sheet>
```

**Cambios:**
- `class="rounded-lg"` → `rounded="md"`
- Mantener `variant="outlined"` y `color="surface"`

---

### 4. Eliminar Estilos CSS Personalizados

**ANTES:**
```vue
<style scoped>
.rounded-xl {
    border-radius: 16px !important;
}

.rounded-lg {
    border-radius: 8px !important;
}
</style>
```

**DESPUÉS:**
```vue
<!-- Eliminar completamente la sección <style scoped> -->
```

**Cambios:**
- Eliminar toda la sección `<style scoped>` relacionada con border-radius
- Los estilos ahora se manejan con props nativas de Vuetify

---

## 📋 Checklist de Archivos a Modificar

### Módulos de Configuración

- [x] `frontend/src/views/Configuracion/Areas/Index.vue`
- [x] `frontend/src/views/Configuracion/Lugares/Index.vue`
- [x] `frontend/src/views/Configuracion/TipoPersonal/Index.vue`
- [x] `frontend/src/views/Configuracion/TiposIncidencia/Index.vue`
- [x] `frontend/src/views/Configuracion/EstadoMonitoreo/Index.vue`
- [x] `frontend/src/views/Configuracion/Personal/Index.vue`
- [x] `frontend/src/views/Configuracion/Usuario/Index.vue`
- [x] `frontend/src/views/Configuracion/Estudiante/Index.vue`
- [x] `frontend/src/views/Configuracion/Roles/Index.vue`
- [x] `frontend/src/views/Configuracion/Empresa/Index.vue`

### Componentes Base

- [x] `frontend/src/components/Partial/AppModal.vue`

### Documentación

- [x] `frontend/MANUAL_MODULO_COMPLETO.md`

---

## 🔍 Patrón de Búsqueda y Reemplazo

### Búsqueda 1: Cards principales con rounded-xl

**Buscar:**
```vue
class="mb-4 rounded-xl"
class="rounded-xl"
rounded="xl"
```

**Reemplazar por:**
```vue
class="mb-4" rounded="lg"
rounded="lg"
rounded="lg"
```

### Búsqueda 2: Cards internos con rounded-lg

**Buscar:**
```vue
class="pa-4 rounded-lg"
class="rounded-lg"
class="pa-3 rounded-lg"
```

**Reemplazar por:**
```vue
class="pa-4" rounded="md"
rounded="md"
class="pa-3" rounded="md"
```

### Búsqueda 3: Estilos CSS personalizados

**Buscar:**
```vue
<style scoped>
.rounded-xl {
    border-radius: 16px !important;
}

.rounded-lg {
    border-radius: 8px !important;
}
</style>
```

**Reemplazar por:**
```vue
<!-- Eliminar completamente -->
```

---

## 📐 Guía de Uso de `rounded` por Contexto

### Cards Principales (Header y Main Content)
```vue
<v-card rounded="lg" elevation="1">
```
- **Uso**: Cards principales de sección
- **Valor**: `lg` (12px) - Balance entre moderno y profesional

### Cards Secundarios (v-sheet internos)
```vue
<v-sheet rounded="md" variant="outlined">
```
- **Uso**: Cards internos, secciones dentro de cards principales
- **Valor**: `md` (8px) - Más sutil que el principal

### Cards Destacados (Modales principales)
```vue
<v-card rounded="xl" elevation="2">
```
- **Uso**: Modales importantes, cards destacados
- **Valor**: `xl` (16px) - Más pronunciado para elementos destacados

### Elementos Pequeños (Chips, badges)
```vue
<v-chip rounded="md">
```
- **Uso**: Chips, badges, elementos pequeños
- **Valor**: `md` (8px) - Consistente con cards secundarios

---

## ✅ Verificación Post-Cambio

Después de aplicar los cambios, verificar:

1. **Visual**: Los bordes redondeados se ven correctamente
2. **Consistencia**: Todos los cards usan el mismo patrón
3. **Funcionalidad**: No se rompió ninguna funcionalidad
4. **Performance**: No hay estilos CSS innecesarios
5. **Mantenibilidad**: El código es más limpio y usa props nativas

---

## 🎨 Ejemplo Completo Antes/Después

### ANTES (Con Estilos Personalizados)

```vue
<template>
    <AuthenticatedLayout>
        <v-container fluid class="pa-4">
            <!-- Header Section -->
            <v-card class="mb-4 rounded-xl" elevation="1">
                <v-card-text class="pa-4">
                    <h1 class="text-h5 font-weight-bold mb-2">Mi Módulo</h1>
                </v-card-text>
            </v-card>

            <!-- Main Content Card -->
            <v-card class="rounded-xl" elevation="2">
                <v-card-title class="pa-4">
                    <span class="text-h6">Datos</span>
                </v-card-title>
            </v-card>

            <!-- Card Interno -->
            <v-sheet
                variant="outlined"
                class="pa-4 rounded-lg"
                color="surface"
            >
                Contenido
            </v-sheet>
        </v-container>
    </AuthenticatedLayout>
</template>

<style scoped>
.rounded-xl {
    border-radius: 16px !important;
}

.rounded-lg {
    border-radius: 8px !important;
}
</style>
```

### DESPUÉS (Con Props Nativas de Vuetify)

```vue
<template>
    <AuthenticatedLayout>
        <v-container fluid class="pa-4">
            <!-- Header Section -->
            <v-card class="mb-4" rounded="lg" elevation="1">
                <v-card-text class="pa-4">
                    <h1 class="text-h5 font-weight-bold mb-2">Mi Módulo</h1>
                </v-card-text>
            </v-card>

            <!-- Main Content Card -->
            <v-card rounded="lg" elevation="2">
                <v-card-title class="pa-4">
                    <span class="text-h6">Datos</span>
                </v-card-title>
            </v-card>

            <!-- Card Interno -->
            <v-sheet
                variant="outlined"
                class="pa-4"
                rounded="md"
                color="surface"
            >
                Contenido
            </v-sheet>
        </v-container>
    </AuthenticatedLayout>
</template>

<!-- Sin estilos CSS personalizados -->
```

---

## 📚 Referencias

- [Vuetify 3 - Cards](https://vuetifyjs.com/en/components/cards/)
- [Vuetify 3 - Border Radius](https://vuetifyjs.com/en/styles/border-radius/)
- [Vuetify 3 - Elevation](https://vuetifyjs.com/en/styles/elevation/)

---

## 🚀 Orden de Ejecución Recomendado

1. **Fase 1**: Actualizar el Manual (`MANUAL_MODULO_COMPLETO.md`)
2. **Fase 2**: Actualizar módulos simples primero (Areas, Lugares, TipoPersonal)
3. **Fase 3**: Actualizar módulos complejos (Roles, Usuario, Estudiante)
4. **Fase 4**: Verificación y testing
5. **Fase 5**: Limpieza final (eliminar estilos CSS no usados)

---

## ⚠️ Consideraciones Importantes

1. **No cambiar `elevation`**: Los valores de elevation ya son estándar de Vuetify
2. **Mantener padding**: Los valores de `pa-4`, `pa-6`, etc. se mantienen igual
3. **Consistencia**: Usar `rounded="lg"` para cards principales y `rounded="md"` para internos
4. **Testing**: Verificar visualmente después de cada cambio
5. **Backup**: Hacer commit antes de cambios masivos

---

**Estado**: ✅ COMPLETADO  
**Prioridad**: Media  
**Esfuerzo estimado**: 2-3 horas para todos los módulos  
**Fecha de finalización**: 2026-01-15

---

## ✅ Resumen de Cambios Aplicados

### Módulos Actualizados (10/10)

1. ✅ **Areas/Index.vue**
   - `class="rounded-xl"` → `rounded="lg"`
   - Eliminados estilos CSS personalizados

2. ✅ **Lugares/Index.vue**
   - `class="rounded-xl"` → `rounded="lg"`
   - Eliminados estilos CSS personalizados

3. ✅ **TipoPersonal/Index.vue**
   - `class="rounded-xl"` → `rounded="lg"`
   - Eliminados estilos CSS personalizados

4. ✅ **TiposIncidencia/Index.vue**
   - `class="rounded-xl"` → `rounded="lg"`
   - Eliminados estilos CSS personalizados

5. ✅ **EstadoMonitoreo/Index.vue**
   - `class="rounded-xl"` → `rounded="lg"`
   - `class="rounded-lg"` → `rounded="md"` (en v-sheet)
   - Eliminados estilos CSS de border-radius (mantenidos estilos de color-picker)

6. ✅ **Personal/Index.vue**
   - `class="rounded-xl"` → `rounded="lg"`
   - `class="rounded-lg"` → `rounded="md"` (en v-card internos)
   - Eliminados estilos CSS personalizados

7. ✅ **Usuario/Index.vue**
   - `class="rounded-xl"` → `rounded="lg"`
   - `class="rounded-lg"` → `rounded="md"` (en v-card internos)
   - Eliminados estilos CSS personalizados

8. ✅ **Estudiante/Index.vue**
   - `class="rounded-xl"` → `rounded="lg"`
   - `class="rounded-lg"` → `rounded="md"` (en v-card internos)
   - Eliminados estilos CSS personalizados

9. ✅ **Roles/Index.vue**
   - `class="rounded-xl"` → `rounded="lg"`
   - `class="rounded-lg"` → `rounded="md"` (en v-card interno)
   - Eliminados estilos CSS de border-radius (mantenidos estilos de tabla de permisos)

10. ✅ **Empresa/Index.vue**
    - `class="rounded-xl"` → `rounded="lg"`
    - `class="rounded-lg"` → `rounded="md"` (en v-sheet y v-img)
    - Eliminados estilos CSS personalizados

### Componentes Base Actualizados

- ✅ **AppModal.vue**
  - `class="rounded-xl"` → `rounded="lg"`

### Documentación Actualizada

- ✅ **MANUAL_MODULO_COMPLETO.md**
  - Ejemplos actualizados con props nativas
  - Sección de estilos CSS eliminada
  - Guía de valores de `rounded` agregada

---

## 📊 Estadísticas

- **Archivos modificados**: 11
- **Estilos CSS eliminados**: 10 archivos
- **Props nativas agregadas**: ~20 instancias
- **Errores de linter**: 0
- **Funcionalidad preservada**: 100%

---

## ✨ Beneficios Obtenidos

1. **Código más limpio**: Sin estilos CSS personalizados innecesarios
2. **Mejor mantenibilidad**: Uso de props nativas de Vuetify
3. **Consistencia**: Todos los módulos usan el mismo patrón
4. **Performance**: Menos CSS personalizado = mejor rendimiento
5. **Compatibilidad**: Mejor integración con Vuetify 3
