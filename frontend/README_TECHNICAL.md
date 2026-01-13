# 📚 Documentación Técnica - Frontend COAR

## 🏗️ Arquitectura del Proyecto

### Stack Tecnológico
- **Framework**: Vue 3 (Composition API)
- **Lenguaje**: TypeScript (strict mode)
- **Estado**: Pinia
- **Routing**: Vue Router 4
- **HTTP Client**: Axios
- **UI Framework**: Tabler (CSS Framework)
- **Tablas**: Tabulator Tables
- **Build Tool**: Vite
- **Testing**: Vitest
- **Linting**: ESLint + Prettier

---

## 📁 Estructura de Carpetas

```
src/
├── api/                    # Configuración de API y servicios
│   ├── axios.ts           # Instancia de axios configurada
│   ├── interceptors/      # Interceptores de axios
│   │   ├── auth.interceptor.ts
│   │   ├── error.interceptor.ts
│   │   └── retry.interceptor.ts
│   └── services/          # Servicios de API
│       ├── auth.service.ts
│       └── usuario.service.ts
│
├── components/            # Componentes reutilizables
│   ├── Layouts/          # Layouts principales
│   │   ├── AuthenticatedLayout.vue
│   │   ├── GuestLayout.vue
│   │   └── Partial/      # Componentes parciales de layout
│   ├── Partial/          # Componentes parciales
│   └── Table/            # Componentes de tabla
│
├── composables/          # Composables reutilizables
│   ├── useAuthReady.ts
│   ├── useCrudModal.ts
│   ├── useImageUpload.ts
│   ├── useMenuPermissions.ts
│   ├── useTabulatorTable.ts
│   └── ...
│
├── config/               # Configuración
│   └── constants.ts
│
├── router/               # Configuración de rutas
│   ├── guards/           # Guards de navegación
│   ├── routes/           # Definición de rutas
│   └── index.ts
│
├── stores/               # Stores de Pinia
│   ├── auth.ts
│   └── ui.store.ts
│
├── types/                # Definiciones de tipos TypeScript
│   ├── auth/             # Tipos relacionados con autenticación
│   ├── configuracion/    # Tipos de módulo configuración
│   └── common.ts         # Tipos comunes
│
├── utils/                # Utilidades
│   ├── notificacion.ts
│   └── tableHelpers.ts
│
└── views/                # Vistas/páginas
    ├── Auth/
    ├── Configuracion/
    └── Dashboard.vue
```

---

## 🔧 Configuración y Scripts

### Scripts Disponibles

```bash
# Desarrollo
npm run dev              # Inicia servidor de desarrollo

# Build
npm run build            # Compila para producción

# Preview
npm run preview          # Preview de build de producción

# Linting
npm run lint             # Ejecuta ESLint y corrige errores
npm run lint:check       # Solo verifica sin corregir

# Formateo
npm run format           # Formatea código con Prettier
npm run format:check     # Solo verifica formato

# Testing
npm run test             # Ejecuta tests
npm run test:ui          # Ejecuta tests con UI
npm run test:coverage    # Ejecuta tests con cobertura
```

---

## 🎯 Composables Principales

### `useTabulatorTable`
Gestiona tablas Tabulator con funcionalidades completas.

**Uso:**
```typescript
const table = useTabulatorTable<Area>({
    tableEl,
    columns,
    ajaxURL: '/configuracion/areas',
    onDataLoaded: (data) => console.log(data)
});
```

### `useCrudModal`
Gestiona modales CRUD con validación y manejo de errores.

**Uso:**
```typescript
const crudModal = useCrudModal<Area>({
    entityName: 'área',
    getPayload: (form) => ({ nombre: form.nombre }),
    validateForm: (form) => !form.nombre ? 'Requerido' : null,
    onCreate: (data) => apiClient.post('/api/areas', data),
    onUpdate: (id, data) => apiClient.put(`/api/areas/${id}`, data),
    resetForm: () => { form.nombre = ''; }
});
```

### `useImageUpload`
Maneja carga y previsualización de imágenes.

**Uso:**
```typescript
const imageUpload = useImageUpload('/images/default.jpg');
// En template: <input @change="imageUpload.handleChange" />
// <img :src="imageUpload.preview" />
```

### `useAuthReady`
Asegura que la autenticación esté lista antes de hacer peticiones.

**Uso:**
```typescript
const { isReady } = useAuthReady();
// En template: <div v-if="!isReady">Cargando...</div>
```

---

## 🔐 Autenticación y Autorización

### Flujo de Autenticación

1. **Login**: Usuario inicia sesión → Token guardado en localStorage
2. **Guards**: Router guards verifican token antes de rutas protegidas
3. **Interceptor**: Axios agrega token automáticamente a peticiones
4. **401 Handling**: Si token inválido → Logout automático y redirección

### Permisos

Los permisos se validan usando `useMenuPermissions`:
- Usuarios especiales (SUPER ADMINISTRADOR, SUPER USUARIO) tienen acceso total
- Otros usuarios validan permisos por menú desde `user.permisos`

---

## 🌐 API y Manejo de Errores

### Configuración de Axios

- **Base URL**: Configurada según entorno (dev: `/api`, prod: `.env`)
- **Interceptores**:
  - **Auth**: Agrega token a peticiones
  - **Retry**: Reintenta errores de red
  - **Error**: Muestra notificaciones automáticamente

### Manejo de Errores

Los errores se manejan en dos niveles:

1. **Backend**: `ErrorHandlerService` genera mensajes user-friendly
2. **Frontend**: Interceptor de axios muestra notificaciones automáticamente

**Omitir notificación:**
```typescript
apiClient.get('/api/endpoint', {
    skipErrorNotification: true
});
```

---

## 🧪 Testing

### Configuración

- **Framework**: Vitest
- **Environment**: happy-dom
- **Coverage**: v8 provider

### Ejecutar Tests

```bash
npm run test              # Modo watch
npm run test:ui          # UI interactiva
npm run test:coverage    # Con cobertura
```

### Estructura de Tests

```
tests/
├── setup.ts             # Configuración global
└── composables/         # Tests de composables
    └── useImageUpload.test.ts
```

---

## 📝 Convenciones de Código

### Nomenclatura

- **Componentes**: PascalCase (`AuthenticatedLayout.vue`)
- **Composables**: camelCase con prefijo `use` (`useTabulatorTable.ts`)
- **Utilidades**: camelCase (`notificacion.ts`)
- **Tipos**: PascalCase (`User`, `Area`)

### TypeScript

- **Strict mode**: Activado
- **Tipos explícitos**: Requeridos en funciones públicas
- **Interfaces**: Para estructuras de datos
- **Types**: Para uniones y utilidades

### Vue

- **Composition API**: Uso exclusivo
- **Script Setup**: Preferido
- **Props**: Tipadas con `defineProps<T>()`
- **Emits**: Tipados con `defineEmits<T>()`

---

## 🚀 Mejores Prácticas

### 1. Composables
- ✅ Extraer lógica reutilizable a composables
- ✅ Un composable = una responsabilidad
- ✅ Documentar con JSDoc

### 2. Componentes
- ✅ Componentes pequeños y enfocados
- ✅ Props tipadas
- ✅ Usar slots para flexibilidad

### 3. Estado
- ✅ Pinia para estado global
- ✅ Props para estado local
- ✅ Evitar prop drilling excesivo

### 4. API
- ✅ Usar `apiClient` para todas las peticiones
- ✅ Tipar requests y responses
- ✅ Manejar errores en interceptores

---

## 🔍 Debugging

### Herramientas

- **Vue DevTools**: Para inspeccionar componentes y estado
- **Network Tab**: Para ver peticiones API
- **Console**: Logs estructurados con contexto

### Logs

```typescript
// ✅ Bueno
console.error('Error cargando empresa:', error);

// ❌ Evitar
console.log(error);
```

---

## 📦 Dependencias Principales

### Producción
- `vue`: Framework principal
- `vue-router`: Routing
- `pinia`: State management
- `axios`: HTTP client
- `tabulator-tables`: Tablas avanzadas

### Desarrollo
- `typescript`: Type safety
- `vite`: Build tool
- `vitest`: Testing
- `eslint`: Linting
- `prettier`: Formateo

---

## 🐛 Troubleshooting

### Errores Comunes

**1. Error de tipos en Tabulator**
```typescript
// @ts-expect-error -- tabulator-tables no proporciona tipos ES module
import { TabulatorFull as Tabulator } from 'tabulator-tables';
```

**2. Iconos no se muestran**
- Verificar que `/tabler/icons/tabler-icons.min.css` esté cargado
- Verificar que las fuentes estén en `/tabler/icons/fonts/`

**3. Errores 401 intermitentes**
- Ya resuelto con `useAuthReady` en `AuthenticatedLayout`
- Verificar que el token esté en localStorage

---

## 📚 Recursos

- [Vue 3 Docs](https://vuejs.org/)
- [Pinia Docs](https://pinia.vuejs.org/)
- [Vue Router Docs](https://router.vuejs.org/)
- [Tabulator Docs](https://tabulator.info/)
- [Tabler Docs](https://tabler.io/docs/)

---

## 🤝 Contribuir

1. Seguir convenciones de código
2. Agregar tests para nueva funcionalidad
3. Documentar composables y funciones públicas
4. Ejecutar `npm run lint` antes de commit
5. Mantener componentes pequeños (< 500 líneas)

---

**Última actualización**: 2025-01-27

