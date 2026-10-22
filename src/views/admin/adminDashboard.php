<?php
require_once __DIR__ . '/../../controllers/auth/check_auth.php';
$user = getLoggedUser();
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Panel general · Edu-System</title>
        <link rel="stylesheet" href="dashboard.css">
    </head>
    <body>
        <?php include __DIR__ . '/../../../ui/header/header.php'; ?>
        <?php include __DIR__ . '/../../../ui/adminSideBar/adminSideBar.php'; ?>

        <div class="workspace">
            <main>
                
                <!-- Contenido de Panel general: edita aquí los textos, las tarjetas y las tablas. -->
                <div id="page-content">
                    <!-- Dashboard presentation prepared for real backend data. -->
                    <section class="welcome">
                        <h1>Bienvenido a tu comunidad académica</h1>
                        <img class="welcome-image" src="/resources/images/academic-welcome.svg" alt="" width="500" height="300">
                    </section>
                    <section class="kpi-grid five">
                        <article class="kpi">
                            <h2 class="kpi-label">Alumnos registrados</h2>
                            <strong class="kpi-value"><!-- Dynamic value: registered students count --></strong>
                        </article>
                        <article class="kpi">
                            <h2 class="kpi-label">Cursos publicados</h2>
                            <strong class="kpi-value"><!-- Dynamic value: published courses count --></strong>
                        </article>
                        <article class="kpi">
                            <h2 class="kpi-label">Inscripciones</h2>
                            <strong class="kpi-value"><!-- Dynamic value: total enrollments in active term --></strong>
                        </article>
                        <article class="kpi">
                            <h2 class="kpi-label">Ponentes</h2>
                            <strong class="kpi-value"><!-- Dynamic value: registered teachers count --></strong>
                        </article>
                        <article class="kpi">
                            <h2 class="kpi-label">Cuentas pendientes</h2>
                            <strong class="kpi-value"><!-- Dynamic value: pending verification accounts count --></strong>
                        </article>
                    </section>
                    <div class="quick-links">
                        <a class="quick-link" href="adminUsers.php">Administrar comunidad <span>→</span>
                        </a>
                        <a class="quick-link" href="adminCourses.php">Revisar catálogo de cursos <span>→</span>
                        </a>
                        <a class="quick-link" href="adminStatistics.php">Consultar rendimiento <span>→</span>
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
                                    <!-- System activity logs will be loaded dynamically here -->
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
                                <!-- Dynamic follow-up notifications -->
                                <div class="timeline-item">
                                    <span class="timeline-mark">
                                    </span>
                                    <div>
                                        <strong>Cuentas por verificar: <!-- Dynamic count: pending users --></strong>
                                        <p>Revisa el estado de los nuevos usuarios.</p>
                                        <a class="btn text" href="adminUsers.php?status=Pendiente">Revisar usuarios →</a>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <span class="timeline-mark">
                                    </span>
                                    <div>
                                        <strong>Cursos en revisión: <!-- Dynamic count: courses under review --></strong>
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

                </div>
            </main>
        </div>
    </body>
</html>
