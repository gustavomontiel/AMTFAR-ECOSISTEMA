---
description: Mejores Prácticas y UI/UX Priorizados para Ecosistema AMTFAR
---
# Skill: Filosofía y Directrices de UI/UX para AMTFAR

El ecosistema AMTFAR pone a la **Experiencia de Usuario (UX)** como la prioridad nº 1. Todo desarrollo de Frontend (sea Angular u otro) debe seguir estas heurísticas y directrices para asegurar una interfaz intuitiva, moderna y de cero fricciones, reduciendo la carga cognitiva tanto para la Farmacia como para el Personal de Backoffice.

## 1. Reducción de la Carga Cognitiva
*   **Ley de Hick**: Minimizar el número de opciones en pantalla. Si un formulario tiene 20 campos, agruparlos lógicamente en "Pasos" o tarjetas descriptivas (Card UI).
*   **Jerarquía Visual Clara (Z-Pattern / F-Pattern)**: Lo más importante (como el "Total a Pagar") debe destacar visualmente (mayor tamaño de fuente, color de contraste fuerte, tipografía en negrita). Elementos secundarios (como "fecha de creación") deben usar colores tenues (ej: estilos muteados o grises suaves).
*   **"Progressive Disclosure"**: Mostrar solo la información esencial de inicio. Si el usuario necesita ver el desglose matemático completo de las retenciones de un empleado, usar un componente acordeón (`<details>`) o un Modal solo cuando el usuario haga clic en "Ver Detalle".

## 2. Prevención y Manejo de Errores (Error Forgiving)
Nunca asumir que el usuario no se equivocará, y cuando lo haga, la interfaz no debe ser castigadora.
*   **Validación Inline Interactiva**: Los mensajes de error de los inputs deben aparecer debajo del mismo input en tiempo real (o en `onBlur`), *no* tras enviar el formulario.
*   **Estados Deshabilitados con Motivo**: Si un botón "Generar Boleta" está deshabilitado porque falta seleccionar el Periodo, al hacerle `hover` debe aparecer un *Tooltip* que diga: _"Por favor, selecciona un Periodo antes de continuar"_.
*   **Protección contra el Doble Click**: Crucial en transacciones. Todo botón de "Pagar", "Guardar" o "Generar" debe entrar en un estado `disabled` y cambiar su texto a "Procesando..." o mostrar un spinner instantáneamente luego del primer click.

## 3. Feedback Constante e Inmediato
El sistema debe sentirse vivo y comunicativo.
*   **Esqueletos de Carga (Skeleton Loaders)**: Nunca mostrar la pantalla en blanco o solo un círculo girando en medio de la nada cuando se entra a "Listado de Empleados". Mostrar un esqueleto gris parpadeante con la forma de la tabla mientras carga la API.
*   **Microinteracciones Atractivas ("Wow Effect")**: 
    - Al pasar el cursor por encima de botones o filas de la tabla (`hover`), aplicar transiciones CSS suaves (`transition-all duration-300`). 
    - Al hacer clic, utilizar efectos sutiles como scale (`active:scale-95`).
    - Las modales deben abrirse con una animación de entrada (`fade-in` y transiciones en el eje Y (ej: deslizando desde abajo suavemente)).
*   **Toast Notifications Sensatas**: Para éxitos ("Boleta generada con éxito") mostrar notificaciones no intrusivas tipo "Toast" abajo a la derecha, auto-descartables en 4 segundos. Para errores críticos ("Servidor desconectado"), usar una alerta fija que requiera intervención.

## 4. Tipografía, Colores y Accesibilidad (A11y)
*   **Tipografía Moderna**: Usar fuentes *Sans-Serif* limpias y de buena altura-x como `Inter`, `Roboto` o `Nunito`. Nunca usar fuentes por defecto del navegador.
*   **Identidad y Estándares FATFA**: Las paletas de colores, iconografía y estética general deben corresponderse estrictamente con los lineamientos gráficos institucionales de FATFA.
*   **Contraste Óptimo (WCAG 2.1)**: Los textos deben tener buen contraste contra el fondo. Evitar usar gris claro sobre fondo blanco para textos importantes.
*   **Consistencia en la Paleta de Colores**: 
    - **Primario**: Para la acción más importante (el "Call to Action").
    - **Secundario/Outline**: Para acciones alternativas (ej. "Cancelar" o "Ver más").
    - **Peligro (Rojo/Naranja)**: Solo para botones destructivos ("Eliminar Empleado", "Anular Boleta"). Un botón de cancelar a menudo no es destructivo, simplemente debería ser un botón neutro o "gris".
*   **Indicadores Visuales No Solo Basados en Color**: Si una boleta está "Pendiente" o "Pagada", acompañar el color con un ícono explícito (un tick de éxito, o un reloj de espera) previendo temas de daltonismo o mala calibración del monitor.
*   **Identificación Directa de Deudas**: Los listados de boletas impagas o farmacias deudoras deben resaltarse jerárquicamente, ofreciendo rápido acceso a sus datos de contacto para cobranza.

## 5. Mobile-First Responsiveness (Adaptabilidad)
Aunque el Backoffice pueda usarse mayormente en desktop, la App Farmacias puede ser vista en tablets o celulares.
*   Uso agresivo de diseño fluído y utilidades como **CSS Flexbox/Grid** o TailwindCSS.
*   Las Tablas complejas en pantallas chicas deben volverse una lista de "Tarjetas" (Cards) apiladas, o bien usar Scroll Horizontal forzado solo sobre la tabla, pero _nunca_ romper el layout completo de la página web.

## 6. Lenguaje, Copywriting y Simplicidad
*   Evitar jerga técnica en la UI. En vez de "Error 500 conectando a SQL", decir "Tuvimos un inconveniente al conectar con nuestros servidores. Por favor, reintente en unos minutos."
*   Los botones deben indicar la acción exacta que ocurrirá: En vez de usar un botón que diga "OK" al borrar, debe decir "Sí, Eliminar".

## 7. UX Proactiva y Sanitización Defensiva
- **Actuar sin pedir permiso para mejorar la UX:** Si un input puede ser mejorado (ej. forzar solo números en un CUIL, autocompletar guiones, poner máscaras de fecha), el asistente debe implementarlo asertivamente como parte de la "calidad premium" esperada, sin necesidad de consultar previamente si se debería hacer.
- **Micro-interacciones defensivas:** Los botones de "Guardar" deben reaccionar al estado de carga deshabilitándose al instante (`disabled=true`) al hacer clic para prevenir cuellos de botella y doble-envíos.
- **Validación Resiliente & Silenciosa:** Eliminar instantáneamente caracteres inválidos del input mientras el usuario escribe o pega texto (ej: `replace(/[^0-9]/g, '')` en el binding de Angular) en lugar de arrojar molestos y estresantes mensajes de error rojos post-tecleo. Que el sistema trabaje para el usuario, no al revés.
