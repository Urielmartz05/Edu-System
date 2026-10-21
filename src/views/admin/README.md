# Frontend administrativo

Las cuatro vistas conservan el controlador de autenticación existente. Utilizan el header y la sidebar originales de `ui/`, sin modificarlos. Comparten la presentación de `components/layout.php`, `admin.css` y `admin.js`; no agregan endpoints, consultas ni cambios a la base de datos.

- Dashboard: resumen de la comunidad, cinco indicadores, cursos con mayor participación y accesos a pendientes.
- Usuarios y cursos: búsqueda sin distinción de acentos, filtros combinables, paginación, alta/edición y exportación CSV de los resultados filtrados.
- Estadísticas: comparación de dos escenarios académicos, gráficos accesibles, rendimiento por curso y exportación del periodo seleccionado.

Los registros son ficticios y se identifican como demostración. Las altas y ediciones se conservan en `sessionStorage` en la pestaña actual. Los indicadores de directorio se calculan a partir de esos registros; las series mensuales, finalización y calificaciones son escenarios ilustrativos. No se solicitan ni almacenan contraseñas. Para reiniciar los registros de muestra, eliminar la clave `edu-admin-demo-v1` de `sessionStorage`.

Los formularios usan validación nativa, control de identificadores/correos duplicados, diálogo con manejo de foco y cierre con Escape. Las tablas permiten desplazamiento horizontal en pantallas pequeñas. No se requieren paquetes, fuentes remotas ni bibliotecas de gráficos.

## Validación

JavaScript verificado con `node --check`. Interacciones revisadas con Playwright/Chromium sobre una representación HTML temporal del layout: búsqueda, filtros, paginación, alta/edición, persistencia en la pestaña, CSV, cambio de periodo, diálogos; anchos de 1440, 768 y 390 px. El entorno de trabajo no incluye PHP, por lo que la ejecución del layout con la sesión autenticada debe validarse en el servidor PHP del proyecto.
