---
description: Buenas Prácticas y Arquitectura para Backend con Slim PHP 4
---
# Skill: Desarrollo de Backend con Slim PHP 4

Esta guía establece los estándares para el desarrollo de la API `amtfar-api` utilizando Slim PHP. El objetivo es mantener un código limpio, desacoplado y de alto rendimiento.

## 1. Estructura del Proyecto (Patrón ADR / MVC Ligero)
Slim no impone una estructura, pero adoptaremos el patrón **Action-Domain-Responder (ADR)** o un enfoque de Capas:
*   `src/Application/Actions/`: Controladores de una sola acción (Single Action Controllers). Ej: `CrearBoletaAction`, `ListarEmpleadosAction`.
*   `src/Domain/`: Entidades, Interfaces de Repositorios y Excepciones de negocio puros. Sin dependencias externas.
*   `src/Infrastructure/Persistence/`: Implementaciones de repositorios (consultas MySQL directas o con algún simple Query Builder/PDO).

## 2. Inyección de Dependencias (DI Container)
Utilizaremos `PHP-DI` para gestionar las dependencias de forma automática. 
*   **Regla**: Nunca instanciar clases complejas dentro de otras usando `new`. Siempre inyectarlas por el constructor.
*   **Regla**: Inyectar Repositorios a las Acciones, nunca pasar el objeto `PDO` crudo a las rutas web.

## 3. Manejo de Rutas y Middleware
*   **Middlewares**: Usar middlewares para validación de tokens JWT (Autenticación), manejo de CORS y formato de respuestas (Content-Type: application/json).
*   **Versión de API**: Agrupar rutas en `/api/v1/...`

## 4. Repositorios y Base de Datos
*   Para interactuar con la base de datos MySQL unificada, se recomienda extender un uso limpio de `PDO` o usar un Query Builder liviano si es necesario, pero manteniendo las consultas SQL parametrizadas para evitar SQL Injection.
*   **Regla**: Los *Actions/Controllers* no pueden contener código SQL bajo ningún punto de vista.

## 5. Respuestas Gloseadas
Estandarizar el formato de salida JSON. Todo Action debe construir una respuesta que siga la estructura:
```json
{
  "statusCode": 200,
  "data": { ... },
  "message": "Operación exitosa."
}
```

## 6. Validación de Datos (DTOs)
Las validaciones de los POST/PUT deben ocurrir antes de llegar a la capa de Dominio, preferentemente a nivel de *Action* usando una librería como `Respect/Validation`.

## 7. Lógica de Negocio y Dominio Core (Ecosistema AMTFAR)
El ecosistema exige adherencia a los siguientes procesos:
*   **Gestión de Farmacias**: Alta (creación de acceso) y Baja lógica (con `fecha_baja`). **Regla de negocio estricta:** La farmacia está obligada a generar y pagar boletas *hasta el mes correspondiente a su fecha de baja inclusive*. Ej: Si se carga baja el 20/03/2026, la farmacia debe generar la boleta de Marzo 2026. A partir del período siguiente cesa su morosidad y obligación.
*   **Remuneraciones y Padrón**: La carga de remuneraciones mensuales deriva en la generación de la boleta y en la **actualización automática del padrón** de empleados (historial y altas).
*   **Pagos e Integraciones**: Soporte de cobro por ventanilla, transferencia y pasarela. Implementar **Webhooks robustos** e **idempotencia** para evitar cobros duplicados.
*   **Reportes Backoffice**: Consultas eficientes de recaudación, morosidad (boletas no generadas o impagas), cobranzas agrupadas y reportes combinados de padrón/categoría.
*   **Soft Deletes Asegurados**: Absolutamente prohibido aplicar `DELETE` sobre entidades transaccionales (boletas, farmacias, padrón); usar logs de auditoría.
