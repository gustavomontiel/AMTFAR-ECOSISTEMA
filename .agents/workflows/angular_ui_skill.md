---
description: Buenas Prácticas, UI/UX y Arquitectura para Frontend con Angular 21
---
# Skill: Desarrollo de Frontend y UI/UX con Angular 21

Esta guía establece los estándares de calidad humana y tecnológica para desarrollar `amtfar-app-farmacias` y `amtfar-backoffice` con Angular 21.

## 1. Arquitectura Angular (Standalone Components)
Angular 21 se centra intensamente en los `Standalone Components` (sin módulos / `NgModule`).
*   **Regla de Oro**: Todos los componentes, directivas y pipes nuevos deben generarse con la bandera `--standalone`.
*   **Signals**: Utilizaremos la nueva reactividad basada en `Signals` (`signal()`, `computed()`, `effect()`) por encima de RxJS (`BehaviorSubject`) para el manejo del estado local o síncrono. RxJS solo se usará para peticiones HTTP pasadas a Signals con `toSignal()`.

## 2. Organización del Proyecto (Feature-Based)
```text
src/
├── core/         # Servicios singleton, interceptores HTTP (tokens), Guards.
├── shared/       # Componentes visuales genéricos (Botones base, Inputs UI), pipes.
├── features/     # Módulos lógicos agnósticos (ej: 'boletas', 'empleados', 'conciliacion').
│   ├── boletas/
│   │   ├── components/ # Componentes de presentación puramente visuales
│   │   ├── pages/      # Smart Components (se conectan al servicio)
│   │   └── services/   # Lógica contra la API.
└── app.routes.ts # Rutas maestras en formato standalone.
```
*   **Smart vs Dumb Components**: Diferenciar claramente el componente que obtiene la data (Dumb: `@Input()`, `@Output()`) del componente "Página" que llama a la API e inyecta la data hacia abajo.

## 3. UI/UX - Reglas de Diseño de Amtfar
Nuestro objetivo es llevar a AMTFAR a un ecosistema de diseño "Wow" y muy intuitivo:
*   **Framework CSS**: En lugar de depender de Bootstrap pesado de inicio a fin, usaremos **Tailwind CSS** para tener control atómico absoluto, o una implementación limpia sobre SCSS. Si usamos librería de componentes, Angular Material está optimizado para MDC (Material Design Components).
*   **Colores y Tipografía**: Establecer una paleta coherente con el *brans* de Amtfar en el CSS root (`:root`). Utilizar Google Fonts como `Inter` o `Roboto` para legibilidad máxima en datos numéricos (boletas).
*   **Microinteracciones**: Los botones primarios deben tener _hover states_ notorios (desplazamiento vertical leve, sombra o cambio de brillo). Las tablas de datos no deben sentirse rígidas.
*   **Feedback Inmediato**: Toda acción asíncrona (ej: generar boleta) debe mostrar "Esqueletos (Skeleton Loaders)" o "Spinners" inyectados mientras el servidor procesa, impidiendo el doble-click accidental.
*   **Manejo del Espacio Activo**: Las pantallas de administración deben aprovechar el ancho, pero enfocando el formulario en el medio con `max-w-screen-md` para que la lectura de campos horizontales no fatigue la vista del usuario de Backoffice.

## 4. Consumo de API
*   Todo llamado a Slim PHP se hará en Services específicos usando `HttpClient`.
*   Toda respuesta debe ser tipada mediante `Interfaces TypeScript` de forma estricta (`getBoletas(): Observable<BoletaResponse>`). Nunca usar `any`.
*   Usar interceptores HTTP para el paso automático del Token de JWT.
