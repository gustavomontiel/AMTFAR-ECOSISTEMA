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
