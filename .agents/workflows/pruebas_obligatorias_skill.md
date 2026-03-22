---
description: Obligación de Testing Estricto antes de Confirmar (AMTFAR)
---
# Protocolo de Pruebas Obligatorias (AMTFAR)

1. **NUNCA confirmar un arreglo o nueva característica al usuario sin haberlo probado.**
2. **Uso del Browser Subagent:** Utilizar activamente el `browser_subagent` para ingresar a la vista local (`http://localhost:4200`), hacer clic en los elementos UI modificados y verificar que los errores hayan desaparecido y la nueva funcionalidad opere correctamente.
3. **Flujos End-To-End (E2E):** Toda carga de datos (ej. boletas, empleados) debe simularse con un usuario válido (`farmacia_demo` o `admin`) hasta el guardado final para asegurar que la base de datos y el backend (Slim PHP) no devuelvan errores HTTP 500.
4. **Verificación Visual:** Comprobar que los cambios estéticos (Angular UI, modales, alertas SweetAlert2, formatos de fecha) se reflejen tal cual lo esperado revisando capturas y logs de red.
5. **Reporte de Resultado:** Al notificar al usuario, incluir explícitamente qué pruebas en pantalla se realizaron de forma autónoma para garantizar que la solución es sólida y final.
