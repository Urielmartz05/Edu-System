# Frontend administrativo

Las cuatro vistas usan PHP, HTML y CSS, sin JavaScript ni dependencias de frontend. El header y la sidebar originales de `ui/` permanecen sin modificaciones. La autenticación existente se conserva.

`components/layout.php` incluye la presentación HTML de cada sección. Los registros, KPI y gráficos son datos de muestra editables en `components/dashboard.php`, `components/users.php`, `components/courses.php` y `components/statistics.php`. No hay endpoints nuevos, escrituras en base de datos ni almacenamiento en el navegador.

Los formularios se muestran con el elemento nativo HTML `details`. La búsqueda, los filtros, el cambio de periodo, la exportación, la edición y el guardado están deshabilitados hasta implementar el backend. Los enlaces entre páginas y la apertura de los formularios funcionan sin scripts.

El dashboard mantiene cinco KPI y el panel de bienvenida rojo con ilustración académica. Los gráficos se conservan únicamente en Estadísticas y están construidos con HTML y CSS.
