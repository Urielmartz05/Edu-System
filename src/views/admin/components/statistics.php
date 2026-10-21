<!-- Presentación HTML con datos de muestra; sin persistencia ni JavaScript. -->
<section class="page-heading">
<div>
<p class="eyebrow">GESTIÓN ACADÉMICA</p>
<h1>Estadísticas académicas</h1>
<p class="subtitle">Información para acompañar el aprendizaje y tomar mejores decisiones.</p>
</div>
<div class="actions">
<label>
<span class="filter-label">Periodo del informe</span>
<select id="period" disabled title="Periodo de muestra">
<option value="actual" selected>Periodo actual · Ago–Dic 2026</option>
<option value="anterior" >Periodo anterior · Ene–Jun 2026</option>
</select>
</label>
<button class="btn" type="button" disabled title="Exportación pendiente de conectar al backend">Exportar informe</button>
</div>
</section>
<section class="kpi-grid " aria-label="Indicadores de demostración">
<article class="kpi">
<span class="kpi-index">01</span>
<h2 class="kpi-label">Inscripciones</h2>
<strong class="kpi-value">104</strong>
<p class="kpi-note">Total en el periodo de muestra</p>
</article>
<article class="kpi">
<span class="kpi-index">02</span>
<h2 class="kpi-label">Tasa de finalización</h2>
<strong class="kpi-value">74%</strong>
<p class="kpi-note">Inscripciones con curso finalizado</p>
</article>
<article class="kpi">
<span class="kpi-index">03</span>
<h2 class="kpi-label">Avance promedio</h2>
<strong class="kpi-value">70%</strong>
<p class="kpi-note">En los cursos publicados</p>
</article>
<article class="kpi">
<span class="kpi-index">04</span>
<h2 class="kpi-label">Calificación promedio</h2>
<strong class="kpi-value">8.6 / 10</strong>
<p class="kpi-note">Promedio de evaluaciones de muestra</p>
</article>
</section>
<div class="grid">
<section class="panel">
<div class="panel-head">
<div>
<h2>Evolución del aprendizaje</h2>
<p>Inscripciones y finalizaciones por mes</p>
</div>
<span class="pill ">Ago–Dic 2026</span>
</div>
<div class="chart">
<div class="chart-legend">
<span>
<i class="legend-dot">
</i>Inscripciones</span>
<span>
<i class="legend-dot light">
</i>Finalizaciones</span>
</div>
<div class="bar-chart" role="img" aria-label="Datos ilustrativos: Jul: 22 inscripciones y 10 finalizaciones; Ago: 37 inscripciones y 15 finalizaciones; Sep: 48 inscripciones y 25 finalizaciones; Oct: 57 inscripciones y 32 finalizaciones; Nov: 64 inscripciones y 42 finalizaciones; Dic: 71 inscripciones y 51 finalizaciones">
<div class="bar-group">
<div class="bar" style="height:27.500000000000004%" title="Jul: 22 inscripciones">
</div>
<div class="bar light" style="height:12.5%" title="10 finalizaciones">
</div>
</div>
<div class="bar-group">
<div class="bar" style="height:46.25%" title="Ago: 37 inscripciones">
</div>
<div class="bar light" style="height:18.75%" title="15 finalizaciones">
</div>
</div>
<div class="bar-group">
<div class="bar" style="height:60%" title="Sep: 48 inscripciones">
</div>
<div class="bar light" style="height:31.25%" title="25 finalizaciones">
</div>
</div>
<div class="bar-group">
<div class="bar" style="height:71.25%" title="Oct: 57 inscripciones">
</div>
<div class="bar light" style="height:40%" title="32 finalizaciones">
</div>
</div>
<div class="bar-group">
<div class="bar" style="height:80%" title="Nov: 64 inscripciones">
</div>
<div class="bar light" style="height:52.5%" title="42 finalizaciones">
</div>
</div>
<div class="bar-group">
<div class="bar" style="height:88.75%" title="Dic: 71 inscripciones">
</div>
<div class="bar light" style="height:63.74999999999999%" title="51 finalizaciones">
</div>
</div>
</div>
<div class="chart-labels" aria-hidden="true">
<span>Jul</span>
<span>Ago</span>
<span>Sep</span>
<span>Oct</span>
<span>Nov</span>
<span>Dic</span>
</div>
</div>
<p class="chart-foot">Número de registros por mes · Serie de ejemplo</p>
</section>
<section class="panel">
<div class="panel-head">
<div>
<h2>Distribución de resultados</h2>
<p>Alumnos por rango de calificación</p>
</div>
</div>
<div class="progress-list">
<div>
<div class="progress-title">
<span>Sobresaliente · 9 a 10</span>
<strong>38%</strong>
</div>
<div class="progress-track" role="progressbar" aria-label="Sobresaliente · 9 a 10" aria-valuenow="38" aria-valuemin="0" aria-valuemax="100">
<div class="progress-fill green" style="width:38%">
</div>
</div>
<p class="list-note">Porcentaje de alumnos evaluados</p>
</div>
<div>
<div class="progress-title">
<span>Satisfactorio · 7 a 8.9</span>
<strong>46%</strong>
</div>
<div class="progress-track" role="progressbar" aria-label="Satisfactorio · 7 a 8.9" aria-valuenow="46" aria-valuemin="0" aria-valuemax="100">
<div class="progress-fill " style="width:46%">
</div>
</div>
<p class="list-note">Porcentaje de alumnos evaluados</p>
</div>
<div>
<div class="progress-title">
<span>Requiere apoyo · Menos de 7</span>
<strong>16%</strong>
</div>
<div class="progress-track" role="progressbar" aria-label="Requiere apoyo · Menos de 7" aria-valuenow="16" aria-valuemin="0" aria-valuemax="100">
<div class="progress-fill gold" style="width:16%">
</div>
</div>
<p class="list-note">Seguimiento académico recomendado</p>
</div>
</div>
</section>
</div>
<aside class="insight">
<strong>Acompañamiento académico</strong>
<p>El 16% de los alumnos de este escenario requiere apoyo. Revisa el avance por curso para orientar las acciones de seguimiento.</p>
</aside>
<section class="panel">
<div class="panel-head">
<div>
<h2>Rendimiento por curso</h2>
<p>Avance, participación y capacidad de los cursos publicados</p>
</div>
</div>
<div class="table-wrap">
<table>
<thead>
<tr>
<th scope="col">CURSO</th>
<th scope="col">INSCRITOS</th>
<th scope="col">OCUPACIÓN</th>
<th scope="col">AVANCE PROMEDIO</th>
<th scope="col">SEGUIMIENTO</th>
</tr>
</thead>
<tbody>
<tr>
<td>
<span class="cell-title">Matemáticas aplicadas</span>
<span class="cell-sub">Laura Méndez</span>
</td>
<td>32</td>
<td>80%</td>
<td>78%</td>
<td>
<span class="pill green">En buen avance</span>
</td>
</tr>
<tr>
<td>
<span class="cell-title">Fundamentos de programación</span>
<span class="cell-sub">Carlos Rivera</span>
</td>
<td>28</td>
<td>80%</td>
<td>65%</td>
<td>
<span class="pill amber">Requiere seguimiento</span>
</td>
</tr>
<tr>
<td>
<span class="cell-title">Comunicación académica</span>
<span class="cell-sub">Laura Méndez</span>
</td>
<td>24</td>
<td>80%</td>
<td>84%</td>
<td>
<span class="pill green">En buen avance</span>
</td>
</tr>
<tr>
<td>
<span class="cell-title">Análisis de datos</span>
<span class="cell-sub">Carlos Rivera</span>
</td>
<td>20</td>
<td>67%</td>
<td>52%</td>
<td>
<span class="pill amber">Requiere seguimiento</span>
</td>
</tr>
</tbody>
</table>
</div>
</section>
<p class="subtitle">La finalización, las calificaciones y las series mensuales son escenarios ilustrativos; no representan información institucional real.</p>
