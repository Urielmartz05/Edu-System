# Frontend administrativo

Las cuatro vistas usan PHP, HTML y CSS, sin JavaScript ni dependencias de frontend. El header y la sidebar originales de `ui/` permanecen sin modificaciones. La autenticación existente se conserva.

El HTML está directamente en los archivos de cada vista: `adminDashboard.php`, `adminUsers.php`, `adminCourses.php` y `adminStatistics.php`. Para cambiar textos, KPI o tablas, edita ese archivo. Cada vista cuenta con su propia hoja de estilos independiente: `dashboard.css`, `courses.css`, `users.css` y `statistics.css`.

Los registros, KPI y gráficos son datos de muestra escritos en el HTML. No hay endpoints nuevos, escrituras en base de datos ni almacenamiento en el navegador.

Los formularios se muestran con el elemento nativo HTML `details`. La búsqueda, los filtros, el cambio de periodo, la exportación, la edición y el guardado están deshabilitados hasta implementar el backend. Los enlaces entre páginas y la apertura de los formularios funcionan sin scripts.

El dashboard mantiene cinco KPI y el panel de bienvenida rojo con ilustración académica. Los gráficos se conservan únicamente en Estadísticas y están construidos con HTML y CSS.
