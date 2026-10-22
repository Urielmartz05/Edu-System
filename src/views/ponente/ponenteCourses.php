<?php
    require_once __DIR__ . "/../../controllers/auth/check_auth.php";
    $user = getLoggedUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Cursos · Edu-System</title>
    <link rel="stylesheet" href="courses.css">
</head>
<body>
    <?php include __DIR__ . '/../../../ui/header/header.php'; ?>
    <?php include __DIR__ . '/../../../components/teacherSidebar/teacherSidebar.php'; ?>

    <div class="workspace">
        <main>
            <div id="page-content">
                <!-- Encabezado -->
                <section class="page-heading">
                    <div>
                        <p class="eyebrow">PORTAL DOCENTE</p>
                        <h1>Mis Cursos</h1>
                        <p class="subtitle">Administra los grupos asignados, consulta las listas de estudiantes y gestiona el contenido temático.</p>
                    </div>
                </section>

                <!-- Métricas Rápidas -->
                <section class="kpi-grid">
                    <article class="kpi">
                        <h2 class="kpi-label">Cursos asignados</h2>
                        <strong class="kpi-value"><!-- Dynamic value: total assigned courses count --></strong>
                    </article>
                    <article class="kpi">
                        <h2 class="kpi-label">Total de alumnos</h2>
                        <strong class="kpi-value"><!-- Dynamic value: total enrolled students count --></strong>
                    </article>
                    <article class="kpi">
                        <h2 class="kpi-label">Promedio de asistencia</h2>
                        <strong class="kpi-value"><!-- Dynamic value: average attendance percentage --></strong>
                    </article>
                    <article class="kpi">
                        <h2 class="kpi-label">Avance del temario</h2>
                        <strong class="kpi-value"><!-- Dynamic value: syllabus progress percentage --></strong>
                    </article>
                </section>

                <!-- Panel Principal de Cursos -->
                <section class="panel">
                    <div class="panel-head">
                        <div>
                            <h2>Catálogo de grupos a mi cargo</h2>
                            <p>Lista detallada de asignaturas para el periodo actual.</p>
                        </div>
                        <span class="badge-tag"><!-- Dynamic value: current academic period (e.g. Periodo 2026-B) --></span>
                    </div>

                    <div class="filters-bar">
                        <input type="search" class="search-input" placeholder="Buscar por clave o nombre de curso..." disabled title="Filtro disponible en siguiente fase">
                        <select class="filter-select" disabled title="Filtro disponible en siguiente fase">
                            <option>Todos los estados</option>
                            <option>Activo</option>
                            <option>Por iniciar</option>
                            <option>Concluido</option>
                        </select>
                    </div>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Clave</th>
                                    <th>Asignatura</th>
                                    <th>Grupo / Semestre</th>
                                    <th>Alumnos</th>
                                    <th>Horario y Aula</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Assigned courses will be loaded dynamically here -->
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
