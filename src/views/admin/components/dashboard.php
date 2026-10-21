<!-- Presentación HTML con datos de muestra; sin persistencia ni JavaScript. -->
<section class="welcome" aria-labelledby="welcome-title">
<h1 id="welcome-title">Bienvenido a tu comunidad académica</h1>
<img class="welcome-image" src="/resources/images/academic-welcome.svg" alt="" width="500" height="300">
</section>
<section class="kpi-grid five" aria-label="Indicadores de demostración">
<article class="kpi">
<span class="kpi-index">01</span>
<h2 class="kpi-label">Alumnos registrados</h2>
<strong class="kpi-value">4</strong>
<p class="kpi-note">Comunidad estudiantil</p>
</article>
<article class="kpi">
<span class="kpi-index">02</span>
<h2 class="kpi-label">Cursos publicados</h2>
<strong class="kpi-value">4</strong>
<p class="kpi-note">De 6 cursos en catálogo</p>
</article>
<article class="kpi">
<span class="kpi-index">03</span>
<h2 class="kpi-label">Inscripciones</h2>
<strong class="kpi-value">104</strong>
<p class="kpi-note">En los cursos del periodo</p>
</article>
<article class="kpi">
<span class="kpi-index">04</span>
<h2 class="kpi-label">Ponentes</h2>
<strong class="kpi-value">2</strong>
<p class="kpi-note">Equipo docente registrado</p>
</article>
<article class="kpi">
<span class="kpi-index">05</span>
<h2 class="kpi-label">Cuentas pendientes</h2>
<strong class="kpi-value">1</strong>
<p class="kpi-note">Pendientes de verificación</p>
</article>
</section>
<div class="quick-links">
<a class="quick-link" href="adminUsers.php">Administrar comunidad <span aria-hidden="true">→</span>
</a>
<a class="quick-link" href="adminCourses.php">Revisar catálogo de cursos <span aria-hidden="true">→</span>
</a>
<a class="quick-link" href="adminStatistics.php">Consultar rendimiento <span aria-hidden="true">→</span>
</a>
</div>
<div class="grid">
<section class="panel">
<div class="panel-head">
<div>
<h2>Cursos con mayor participación</h2>
<p>Distribución de alumnos en el periodo</p>
</div>
<a class="btn text" href="adminCourses.php">Ver cursos →</a>
</div>
<div class="table-wrap">
<table>
<thead>
<tr>
<th scope="col">CURSO / PONENTE</th>
<th scope="col">INSCRITOS</th>
<th scope="col">ESTADO</th>
</tr>
</thead>
<tbody>
<tr>
<td>
<span class="cell-title">Matemáticas aplicadas</span>
<span class="cell-sub">Laura Méndez</span>
</td>
<td>32 <span class="cell-sub">de 40 lugares</span>
</td>
<td>
<span class="pill green">Publicado</span>
</td>
</tr>
<tr>
<td>
<span class="cell-title">Fundamentos de programación</span>
<span class="cell-sub">Carlos Rivera</span>
</td>
<td>28 <span class="cell-sub">de 35 lugares</span>
</td>
<td>
<span class="pill green">Publicado</span>
</td>
</tr>
<tr>
<td>
<span class="cell-title">Comunicación académica</span>
<span class="cell-sub">Laura Méndez</span>
</td>
<td>24 <span class="cell-sub">de 30 lugares</span>
</td>
<td>
<span class="pill green">Publicado</span>
</td>
</tr>
<tr>
<td>
<span class="cell-title">Análisis de datos</span>
<span class="cell-sub">Carlos Rivera</span>
</td>
<td>20 <span class="cell-sub">de 30 lugares</span>
</td>
<td>
<span class="pill green">Publicado</span>
</td>
</tr>
</tbody>
</table>
</div>
</section>
<section class="panel">
<div class="panel-head">
<div>
<h2>Atención y seguimiento</h2>
<p>Pendientes de la comunidad escolar</p>
</div>
</div>
<div class="timeline">
<div class="timeline-item">
<span class="timeline-mark">
</span>
<div>
<strong>Cuentas por verificar: 1</strong>
<p>Revisa el estado de los nuevos usuarios.</p>
<a class="btn text" href="adminUsers.php?status=Pendiente">Revisar usuarios →</a>
</div>
</div>
<div class="timeline-item">
<span class="timeline-mark">
</span>
<div>
<strong>Cursos en revisión: 1</strong>
<p>Consulta los cursos pendientes de publicación.</p>
<a class="btn text" href="adminCourses.php?status=En%20revisi%C3%B3n">Revisar cursos →</a>
</div>
</div>
<div class="timeline-item">
<span class="timeline-mark">
</span>
<div>
<strong>Revisa el avance académico</strong>
<p>Identifica los cursos que necesitan acompañamiento.</p>
<a class="btn text" href="adminStatistics.php">Consultar estadísticas →</a>
</div>
</div>
</div>
</section>
</div>
