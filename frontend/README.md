# Frontend - COAR Application

Proyecto Vue 3 separado para el frontend de la aplicación COAR.

## Estructura del Proyecto

```
frontend/
├── src/
│   ├── api/              # Configuración de Axios y clientes API
│   ├── components/        # Componentes reutilizables
│   ├── composables/      # Composables de Vue
│   ├── router/           # Configuración de Vue Router
│   ├── stores/           # Stores de Pinia (estado global)
│   ├── types/            # Definiciones de tipos TypeScript
│   ├── utils/            # Utilidades y helpers
│   ├── views/            # Páginas/Vistas
│   ├── App.vue           # Componente raíz
│   └── main.ts           # Punto de entrada
├── public/               # Archivos estáticos
├── .env                  # Variables de entorno
└── package.json
```

## Configuración

### Variables de Entorno

Crea un archivo `.env` basado en `.env.example`:

```env
VITE_API_BASE_URL=http://127.0.0.1:8000/api
VITE_APP_NAME=COAR
```

### Instalación

```bash
npm install
```

### Desarrollo

```bash
npm run dev
```

El servidor de desarrollo se ejecutará en `http://localhost:5173`

### Build para Producción

```bash
npm run build
```

## Características

- ✅ Vue 3 con Composition API y TypeScript
- ✅ Vue Router para navegación
- ✅ Pinia para gestión de estado
- ✅ Axios configurado con interceptores
- ✅ Autenticación con guards de router
- ✅ Tailwind CSS para estilos
- ✅ Tabulator para tablas de datos

## Autenticación

La autenticación se maneja mediante:
- Store de Pinia (`stores/auth.ts`)
- Guards de router que verifican autenticación
- Interceptores de Axios para manejar tokens CSRF

## API

Todas las peticiones API se realizan a través de `src/api/axios.ts` que está configurado para:
- Enviar credenciales (cookies)
- Agregar token CSRF automáticamente
- Manejar errores 401 (no autenticado)

## Migración desde Inertia

Este proyecto migra el frontend desde Inertia.js a un proyecto Vue independiente. Las principales diferencias:

- **Rutas**: Ahora se usan rutas de Vue Router en lugar de rutas de Laravel
- **Estado**: Se usa Pinia en lugar de props de Inertia
- **Autenticación**: Se maneja mediante API en lugar de sesiones de Laravel directamente
- **Navegación**: Se usa `router.push()` en lugar de `router.visit()`
