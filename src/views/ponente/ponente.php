<?php
    require_once __DIR__ . "/../../controllers/auth/check_auth.php";
    $user = getLoggedUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Docente · Edu-System</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <?php include __DIR__ . '/../../../ui/header/header.php'; ?>
    <?php include __DIR__ . '/../../../components/teacherSidebar/teacherSidebar.php'; ?>

    <div class="workspace">
        <main>
            <div id="page-content">
                <!-- Banner de bienvenida -->
                <section class="welcome">
                    <div class="welcome-text">
                        <span class="welcome-badge">Portal Docente / Ponente</span>
                        <h1>Bienvenido, <?php echo htmlspecialchars($user['name']); ?></h1>
                        <p>Gestiona tus cursos asignados, da seguimiento a tus alumnos y consulta las sesiones académicas programadas para este ciclo escolar.</p>
                    </div>
                    <img class="welcome-image" src="/resources/images/academic-welcome.svg" alt="Bienvenida académica">
                </section>

                <!-- Tarjetas KPI -->
                <section class="kpi-grid">
                    <article class="kpi">
                        <h2 class="kpi-label">Cursos asignados</h2>
                        <strong class="kpi-value"><!-- Dynamic value: total assigned courses count --></strong>
                    </article>
                    <article class="kpi">
                        <h2 class="kpi-label">Alumnos inscritos</h2>
                        <strong class="kpi-value"><!-- Dynamic value: total enrolled students count --></strong>
                    </article>
                    <article class="kpi">
                        <h2 class="kpi-label">Sesiones este mes</h2>
                        <strong class="kpi-value"><!-- Dynamic value: total sessions scheduled this month --></strong>
                    </article>
                    <article class="kpi">
                        <h2 class="kpi-label">Evaluaciones por calificar</h2>
                        <strong class="kpi-value"><!-- Dynamic value: pending submissions to grade --></strong>
                    </article>
                </section>

                <!-- Accesos rápidos -->
                <div class="quick-links">
                    <a class="quick-link" href="ponenteCourses.php">
                        Ver catálogo de mis cursos
                        <span>→</span>
                    </a>
                    <a class="quick-link" href="ponenteCourses.php">
                        Registrar asistencia y calificaciones
                        <span>→</span>
                    </a>
                </div>

                <!-- Grid principal -->
                <div class="grid">
                    <!-- Tabla de Cursos Activos -->
                    <section class="panel">
                        <div class="panel-head">
                            <div>
                                <h2>Mis cursos en curso</h2>
                                <p>Cursos asignados para el periodo académico actual.</p>
                            </div>
                            <a href="ponenteCourses.php" class="btn-link">Ver todos</a>
                        </div>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Nombre del curso</th>
                                        <th>Alumnos</th>
                                        <th>Horario / Aula</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <!-- Active courses will be loaded dynamically here -->
                            </tbody>
                            </table>
                        </div>
                    </section>

                    <!-- Avisos y Agenda -->
                    <section class="panel">
                        <div class="panel-head">
                            <div>
                                <h2>Avisos y agenda</h2>
                                <p>Próximos eventos y fechas límite.</p>
                            </div>
                        </div>
                        <div class="activity-list">
                            <!-- Upcoming notices and events will be loaded dynamically here -->
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
