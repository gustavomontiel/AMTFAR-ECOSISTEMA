---
description: Buenas Prácticas, UI/UX y Arquitectura para Frontend con Angular 21
---
# Skill: Desarrollo de Frontend y UI/UX con Angular 21

Esta guía establece los estándares de calidad humana y tecnológica para desarrollar `amtfar-app-farmacias` y `amtfar-backoffice` con Angular 21.

## 1. Arquitectura Angular (Standalone Components y Control Flow)
Angular 21 se centra intensamente en los `Standalone Components` (sin módulos / `NgModule`) y evoluciona la sintaxis nativa de plantillas.
*   **Regla de Oro (Standalone)**: Todos los componentes, directivas y pipes nuevos deben generarse con la bandera `--standalone` (verdadero por defecto en v17+).
*   **Control Flow Nativo (OBLIGATORIO)**: Queda ESTRICTAMENTE PROHIBIDO el uso de las viejas directivas estructurales (`*ngIf`, `*ngFor`, `*ngSwitch`). TODO el HTML debe escrbirse utilizando la nueva sintaxis de bloques optimizada: `@if`, `@for` (siempre con la cláusula `track`) y `@switch`.
*   **Signals por Defecto (Estado Local)**: Siempre que inicialices una variable de estado en un componente (ej. visibilidad, estados de carga, campos de formularios simples), DEBEMOS utilizar `signal()` de `@angular/core` en lugar de variables planas. Usa `.set()` y `.update()` para modificarlas y léelas como funciones `()` en los templates. RxJS solo se usará pasivamente para peticiones HTTP (o combinadas con `toSignal()`).

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

## 5. Diseño de Flujos Core (AMTFAR)
*   **App Farmacias (Portal de Carga)**: El flujo de carga de remuneraciones por período y empleado debe ser transparente. La opción de pago debe elegirse claramente (Ventanilla, Transferencia, Pago Web). La UI debe recalcar la actualización del padrón.
*   **App Backoffice (Monitor & Reportes)**: Vistas de recaudación, seguimiento de deudores, altas/bajas de farmacias y consulta de padrón con grillas funcionales completas.

