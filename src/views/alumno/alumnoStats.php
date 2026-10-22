<?php
    require_once __DIR__ . "/../../controllers/auth/check_auth.php";
    $user = getLoggedUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Estadísticas · Edu-System</title>
    <link rel="stylesheet" href="stats.css">
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
                        <p class="eyebrow">SEGUIMIENTO ACADÉMICO</p>
                        <h1>Mis Estadísticas</h1>
                        <p class="subtitle">Monitorea tu desempeño escolar, desglose de calificaciones por parcial y avance en el plan de estudios.</p>
                    </div>
                    <span class="period-badge"><!-- Dynamic value: academic cycle (e.g. Ciclo Escolar: Ago–Dic 2026) --></span>
                </section>

                <!-- Métricas Clave -->
                <section class="kpi-grid">
                    <article class="kpi">
                        <h2 class="kpi-label">Promedio general</h2>
                        <strong class="kpi-value"><!-- Dynamic value: overall grade point average --></strong>
                    </article>
                    <article class="kpi">
                        <h2 class="kpi-label">Asistencia global</h2>
                        <strong class="kpi-value"><!-- Dynamic value: overall attendance percentage --></strong>
                    </article>
                    <article class="kpi">
                        <h2 class="kpi-label">Materias aprobadas</h2>
                        <strong class="kpi-value"><!-- Dynamic value: passed courses count --></strong>
                    </article>
                    <article class="kpi">
                        <h2 class="kpi-label">Entregas a tiempo</h2>
                        <strong class="kpi-value"><!-- Dynamic value: on-time submissions percentage --></strong>
                    </article>
                </section>

                <!-- Gráficos y Desglose -->
                <div class="grid">
                    <!-- Gráfico de Calificaciones por Materia -->
                    <section class="panel">
                        <div class="panel-head">
                            <div>
                                <h2>Calificación promedio por asignatura</h2>
                                <p>Rendimiento acumulado en el semestre actual.</p>
                            </div>
                        </div>
                        <div class="grades-chart">
                            <!-- Grades by subject will be loaded dynamically here -->
                        </div>
                    </section>

                    <!-- Ponderación de Evaluaciones -->
                    <section class="panel">
                        <div class="panel-head">
                            <div>
                                <h2>Ponderación global de evaluación</h2>
                                <p>Distribución de criterios de calificación.</p>
                            </div>
                        </div>
                        <div class="eval-breakdown">
                            <!-- Evaluation criteria breakdown will be loaded dynamically here -->
                        </div>
                    </section>
                </div>

                <!-- Tabla de Desglose de Calificaciones -->
                <section class="panel">
                    <div class="panel-head">
                        <div>
                            <h2>Historial detallado por parcial</h2>
                            <p>Registro de evaluaciones acumuladas en el periodo actual.</p>
                        </div>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Clave</th>
                                    <th>Materia</th>
                                    <th>Parcial 1</th>
                                    <th>Parcial 2</th>
                                    <th>Proyecto</th>
                                    <th>Calificación Proyectada</th>
                                    <th>Estatus</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Term grades breakdown rows will be loaded dynamically here -->
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
