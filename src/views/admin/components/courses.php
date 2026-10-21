<!-- Presentación HTML con datos de muestra; sin persistencia ni JavaScript. -->
<section class="page-heading">
<div>
<p class="eyebrow">GESTIÓN ACADÉMICA</p>
<h1>Catálogo de cursos</h1>
<p class="subtitle">Organiza la oferta académica y da seguimiento a cada curso.</p>
</div>
<div class="actions">
<button class="btn" type="button" disabled title="Exportación pendiente de conectar al backend">Exportar CSV</button>
</div>
</section>
<section class="kpi-grid " aria-label="Indicadores de demostración">
<article class="kpi">
<span class="kpi-index">01</span>
<h2 class="kpi-label">Cursos en catálogo</h2>
<strong class="kpi-value">6</strong>
<p class="kpi-note">Oferta académica completa</p>
</article>
<article class="kpi">
<span class="kpi-index">02</span>
<h2 class="kpi-label">Publicados</h2>
<strong class="kpi-value">4</strong>
<p class="kpi-note">Disponibles para alumnos</p>
</article>
<article class="kpi">
<span class="kpi-index">03</span>
<h2 class="kpi-label">En revisión</h2>
<strong class="kpi-value">1</strong>
<p class="kpi-note">Pendientes de publicación</p>
</article>
<article class="kpi">
<span class="kpi-index">04</span>
<h2 class="kpi-label">Inscripciones</h2>
<strong class="kpi-value">104</strong>
<p class="kpi-note">En todos los cursos</p>
</article>
</section>
<details class="panel preview-editor">
<summary>Nuevo curso</summary>
<form class="preview-form" aria-describedby="form-note">
<div class="dialog-head">
<h2 id="dialog-title">Nuevo curso</h2>
</div>
<p id="form-note" class="dialog-note">Vista previa del formulario. El guardado estará disponible al conectar el backend.</p>
<div class="form-grid">
<label class="form-field ">Código del curso<input class="field" name="id" value="" type="text" required maxlength="120">
</label>
<label class="form-field">Área académica<select name="category" required>
<option >Ciencias exactas</option>
<option >Tecnología</option>
<option >Humanidades</option>
</select>
</label>
<label class="form-field full">Título del curso<input class="field" name="title" value="" type="text" required maxlength="120">
</label>
<label class="form-field">Ponente asignado<select name="teacher" required>
<option >Laura Méndez</option>
<option >Carlos Rivera</option>
</select>
</label>
<label class="form-field">Estado de publicación<select name="status" required>
<option >En revisión</option>
<option >Publicado</option>
<option >Pausado</option>
</select>
</label>
<label class="form-field">Cupo máximo<input class="field" type="number" name="capacity" min="1" max="1000" value="30" required>
</label>
<label class="form-field full">Descripción<textarea class="field" name="description" rows="3" maxlength="1000">
</textarea>
</label>
</div>
<div class="form-actions">
<button class="btn" type="reset">Limpiar</button>
<button class="btn primary" type="submit" disabled title="Pendiente de conectar al backend">Guardar curso</button>
</div>
</form>
</details>
<section class="panel">
<div class="panel-head">
<div>
<h2>Oferta académica</h2>
<p>Consulta la capacidad, los responsables y el estado de publicación.</p>
</div>
<span class="pill wine">Datos de muestra</span>
</div>
<div class="filters">
<label class="search">
<span class="filter-label">Buscar por curso, ponente o código</span>
<input class="field" id="search" disabled title="Búsqueda pendiente de conectar al backend" type="search" placeholder="Buscar por curso, ponente o código…">
</label>
<label>
<span class="filter-label">Área académica</span>
<select id="category" disabled title="Filtros pendientes de conectar al backend">
<option value="">Todas las áreas</option>
<option>Ciencias exactas</option>
<option>Tecnología</option>
<option>Humanidades</option>
</select>
</label>
<label>
<span class="filter-label">Estado</span>
<select id="status" disabled title="Filtros pendientes de conectar al backend">
<option value="">Todos los estados</option>
<option>Publicado</option>
<option>En revisión</option>
<option>Pausado</option>
</select>
</label>
</div>
<div class="table-wrap">
<table>
<thead>
<tr>
<th scope="col">CURSO</th>
<th scope="col">PONENTE</th>
<th scope="col">INSCRITOS / CUPO</th>
<th scope="col">ESTADO</th>
<th scope="col">ACCIONES</th>
</tr>
</thead>
<tbody>
<tr>
<td>
<span class="cell-title">Matemáticas aplicadas</span>
<span class="cell-sub">MAT-101 · Ciencias exactas</span>
</td>
<td>Laura Méndez</td>
<td>32 / 40<span class="cell-sub">80% de ocupación</span>
</td>
<td>
<span class="pill green">Publicado</span>
</td>
<td>
<button type="button" disabled title="Edición pendiente de conectar al backend" class="row-action" aria-label="Editar Matemáticas aplicadas">Editar</button>
</td>
</tr>
<tr>
<td>
<span class="cell-title">Fundamentos de programación</span>
<span class="cell-sub">PRG-201 · Tecnología</span>
</td>
<td>Carlos Rivera</td>
<td>28 / 35<span class="cell-sub">80% de ocupación</span>
</td>
<td>
<span class="pill green">Publicado</span>
</td>
<td>
<button type="button" disabled title="Edición pendiente de conectar al backend" class="row-action" aria-label="Editar Fundamentos de programación">Editar</button>
</td>
</tr>
<tr>
<td>
<span class="cell-title">Comunicación académica</span>
<span class="cell-sub">COM-101 · Humanidades</span>
</td>
<td>Laura Méndez</td>
<td>24 / 30<span class="cell-sub">80% de ocupación</span>
</td>
<td>
<span class="pill green">Publicado</span>
</td>
<td>
<button type="button" disabled title="Edición pendiente de conectar al backend" class="row-action" aria-label="Editar Comunicación académica">Editar</button>
</td>
</tr>
<tr>
<td>
<span class="cell-title">Análisis de datos</span>
<span class="cell-sub">DAT-202 · Tecnología</span>
</td>
<td>Carlos Rivera</td>
<td>20 / 30<span class="cell-sub">67% de ocupación</span>
</td>
<td>
<span class="pill green">Publicado</span>
</td>
<td>
<button type="button" disabled title="Edición pendiente de conectar al backend" class="row-action" aria-label="Editar Análisis de datos">Editar</button>
</td>
</tr>
<tr>
<td>
<span class="cell-title">Física experimental</span>
<span class="cell-sub">FIS-102 · Ciencias exactas</span>
</td>
<td>Laura Méndez</td>
<td>0 / 30<span class="cell-sub">0% de ocupación</span>
</td>
<td>
<span class="pill amber">En revisión</span>
</td>
<td>
<button type="button" disabled title="Edición pendiente de conectar al backend" class="row-action" aria-label="Editar Física experimental">Editar</button>
</td>
</tr>
<tr>
<td>
<span class="cell-title">Historia contemporánea</span>
<span class="cell-sub">HIS-101 · Humanidades</span>
</td>
<td>Laura Méndez</td>
<td>0 / 30<span class="cell-sub">0% de ocupación</span>
</td>
<td>
<span class="pill ">Pausado</span>
</td>
<td>
<button type="button" disabled title="Edición pendiente de conectar al backend" class="row-action" aria-label="Editar Historia contemporánea">Editar</button>
</td>
</tr>
</tbody>
</table>
</div>
<div class="table-footer">
<span>Mostrando 1–6 de 6 registros</span>
</div>
</section>
<p class="subtitle">Los cursos en revisión y pausados permanecen en el catálogo administrativo.</p>
