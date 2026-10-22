<?php
    require_once __DIR__ . "/../../controllers/auth/check_auth.php";
    $user = getLoggedUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Alumno · Edu-System</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <?php include __DIR__ . '/../../../ui/header/header.php'; ?>
    <?php include __DIR__ . '/../../../components/studentSidebar/studentSidebar.php'; ?>

    <div class="workspace">
        <main>
            <div id="page-content">
                <!-- Banner de bienvenida -->
                <section class="welcome">
                    <div class="welcome-text">
                        <span class="welcome-badge">Portal Estudiantil</span>
                        <h1>Hola de nuevo, <?php echo htmlspecialchars($user['name']); ?></h1>
                        <p>Bienvenido a tu espacio académico. Consulta tus asignaturas inscritas, sigue el progreso de tus calificaciones y mantén al día tus entregas escolares.</p>
                    </div>
                    <img class="welcome-image" src="/resources/images/academic-welcome.svg" alt="Bienvenida académica">
                </section>

                <!-- Tarjetas KPI -->
                <section class="kpi-grid">
                    <article class="kpi">
                        <h2 class="kpi-label">Materias inscritas</h2>
                        <strong class="kpi-value"><!-- Dynamic value: enrolled courses count --></strong>
                    </article>
                    <article class="kpi">
                        <h2 class="kpi-label">Promedio general</h2>
                        <strong class="kpi-value"><!-- Dynamic value: overall grade point average --></strong>
                    </article>
                    <article class="kpi">
                        <h2 class="kpi-label">Asistencia global</h2>
                        <strong class="kpi-value"><!-- Dynamic value: overall attendance percentage --></strong>
                    </article>
                    <article class="kpi">
                        <h2 class="kpi-label">Créditos acumulados</h2>
                        <strong class="kpi-value"><!-- Dynamic value: accumulated credits count --></strong>
                    </article>
                </section>

                <!-- Accesos rápidos -->
                <div class="quick-links">
                    <a class="quick-link" href="alumnoCourses.php">
                        Ver mis materias y horarios
                        <span>→</span>
                    </a>
                    <a class="quick-link" href="alumnoStats.php">
                        Consultar mi historial y estadísticas
                        <span>→</span>
                    </a>
                </div>

                <!-- Grid principal -->
                <div class="grid">
                    <!-- Tabla de Asignaturas Cursadas -->
                    <section class="panel">
                        <div class="panel-head">
                            <div>
                                <h2>Mis materias en curso</h2>
                                <p>Asignaturas inscritas para el periodo escolar vigente.</p>
                            </div>
                            <a href="alumnoCourses.php" class="btn-link">Ver todas</a>
                        </div>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Clave</th>
                                        <th>Materia</th>
                                        <th>Docente</th>
                                        <th>Horario / Aula</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <!-- Enrolled courses will be loaded dynamically here -->
                            </tbody>
                            </table>
                        </div>
                    </section>

                    <!-- Avisos y Entregas Pendientes -->
                    <section class="panel">
                        <div class="panel-head">
                            <div>
                                <h2>Próximas entregas</h2>
                                <p>Fechas límite y exámenes agendados.</p>
                            </div>
                        </div>
                        <div class="task-list">
                            <!-- Upcoming assignments will be loaded dynamically here -->
                        </div>
                            </div>
                            */ ?>
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
