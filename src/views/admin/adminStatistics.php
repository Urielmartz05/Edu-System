<?php
require_once __DIR__ . '/../../controllers/auth/check_auth.php';
$user = getLoggedUser();
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Estadísticas · Edu-System</title>
        <link rel="stylesheet" href="statistics.css">
    </head>
    <body>
        <?php include __DIR__ . '/../../../ui/header/header.php'; ?>
        <?php include __DIR__ . '/../../../ui/adminSideBar/adminSideBar.php'; ?>

        <div class="workspace">
            <main>
                
                <!-- Contenido de Estadísticas: edita aquí los textos, las tarjetas y las tablas. -->
                <div id="page-content">
                    <!-- Academic statistics presentation prepared for real backend data. -->
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
                    <section class="kpi-grid">
                        <article class="kpi">
                            <h2 class="kpi-label">Inscripciones</h2>
                            <strong class="kpi-value"><!-- Dynamic value: total enrollments in report period --></strong>
                        </article>
                        <article class="kpi">
                            <h2 class="kpi-label">Tasa de finalización</h2>
                            <strong class="kpi-value"><!-- Dynamic value: course completion rate percentage --></strong>
                        </article>
                        <article class="kpi">
                            <h2 class="kpi-label">Avance promedio</h2>
                            <strong class="kpi-value"><!-- Dynamic value: average syllabus progress percentage --></strong>
                        </article>
                        <article class="kpi">
                            <h2 class="kpi-label">Calificación promedio</h2>
                            <strong class="kpi-value"><!-- Dynamic value: average student grade (e.g. X.X / 10) --></strong>
                        </article>
                    </section>
                    <div class="grid">
                        <section class="panel">
                            <div class="panel-head">
                                <div>
                                    <h2>Evolución del aprendizaje</h2>
                                    <p>Inscripciones y finalizaciones por mes</p>
                                </div>
                                <span class="pill "><!-- Dynamic value: period label --></span>
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
                                <div class="bar-chart">
                                    <!-- Dynamic bar-groups: iterate over monthly statistics -->
                                    <!--
                                    <div class="bar-group">
                                        <div class="bar" style="height: [enrollments_percentage]%" title="[Month]: [count] inscripciones"></div>
                                        <div class="bar light" style="height: [completions_percentage]%" title="[count] finalizaciones"></div>
                                    </div>
                                    -->
                                </div>
                                <div class="chart-labels">
                                    <!-- Dynamic labels: iterate over month abbreviations (e.g. <span>Jul</span>) -->
                                </div>
                            </div>
                            <p class="chart-foot"><!-- Dynamic summary note for monthly chart data --></p>
                        </section>
                        <section class="panel">
                            <div class="panel-head">
                                <div>
                                    <h2>Distribución de resultados</h2>
                                    <p>Alumnos por rango de calificación</p>
                                </div>
                            </div>
                            <div class="progress-list">
                                <!-- Dynamic progress items: iterate over grade distribution tiers -->
                                <!--
                                <div>
                                    <div class="progress-title">
                                        <span><!-- Grade range tier title --></span>
                                        <strong><!-- Tier percentage --></strong>
                                    </div>
                                    <div class="progress-track">
                                        <div class="progress-fill green" style="width:<!-- Tier percentage -->%"></div>
                                    </div>
                                    <p class="list-note"><!-- Tier description note --></p>
                                </div>
                                -->
                            </div>
                        </section>
                    </div>
                    <aside class="insight">
                        <strong>Acompañamiento académico</strong>
                        <p><!-- Dynamic recommendation or academic insight text --></p>
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
                                    <!-- Statistical breakdown records will be loaded dynamically here -->
                                </tbody>
                            </table>
                        </div>
                    </section>
                    <!-- Dynamic notes / report disclaimer here -->

                </div>
            </main>
        </div>
    </body>
</html>
