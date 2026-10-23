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
    <?php include __DIR__ . '/../../../components/studentSidebar/studentSidebar.php'; ?>

    <div class="workspace">
        <main>
            <div id="page-content">
                <!-- Encabezado -->
                <section class="page-heading">
                    <div>
                        <p class="eyebrow">PORTAL ESTUDIANTIL</p>
                        <h1>Mis Cursos</h1>
                        <p class="subtitle">Consulta las asignaturas inscritas en el ciclo escolar actual, docentes a cargo, aulas y horarios de clase.</p>
                    </div>
                </section>

                <!-- Métricas Rápidas -->
                <section class="kpi-grid">
                    <article class="kpi">
                        <h2 class="kpi-label">Materias inscritas</h2>
                        <strong class="kpi-value"><!-- Dynamic value: enrolled courses count --></strong>
                    </article>
                    <article class="kpi">
                        <h2 class="kpi-label">Horas semanales</h2>
                        <strong class="kpi-value"><!-- Dynamic value: weekly class hours --></strong>
                    </article>
                    <article class="kpi">
                        <h2 class="kpi-label">Créditos en curso</h2>
                        <strong class="kpi-value"><!-- Dynamic value: enrolled credits count --></strong>
                    </article>
                    <article class="kpi">
                        <h2 class="kpi-label">Tareas entregadas</h2>
                        <strong class="kpi-value"><!-- Dynamic value: submitted assignments ratio (e.g. X / Y) --></strong>
                    </article>
                </section>

                <!-- Panel Principal de Cursos -->
                <section class="panel">
                    <div class="panel-head">
                        <div>
                            <h2>Catálogo de materias registradas</h2>
                            <p>Lista oficial de asignaturas para el periodo escolar actual.</p>
                        </div>
                        <span class="badge-tag"><!-- Dynamic value: current academic period (e.g. Periodo 2026-B) --></span>
                    </div>

                    <div class="filters-bar">
                        <input type="search" class="search-input" placeholder="Buscar por clave o nombre de materia...">
                        <select class="filter-select">
                            <option>Todos los estados</option>
                            <option>Cursando</option>
                            <option>Concluida</option>
                        </select>
                    </div>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Clave</th>
                                    <th>Asignatura</th>
                                    <th>Profesor Titular</th>
                                    <th>Créditos</th>
                                    <th>Horario y Aula</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Registered courses will be loaded dynamically here -->
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
