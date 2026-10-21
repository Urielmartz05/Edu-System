<?php
    require_once("../../controllers/auth/check_auth.php");
    $user = getLoggedUser();

    // Determinar saludo según la hora del día
    $hora = (int)date('G');
    if ($hora >= 5 && $hora < 12) {
        $saludo = "Buenos días";
    } elseif ($hora >= 12 && $hora < 19) {
        $saludo = "Buenas tardes";
    } else {
        $saludo = "Buenas noches";
    }

    $dias = ['Sunday' => 'Domingo', 'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles', 'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado'];
    $meses = ['January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo', 'April' => 'Abril', 'May' => 'Mayo', 'June' => 'Junio', 'July' => 'Julio', 'August' => 'Agosto', 'September' => 'Septiembre', 'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre'];
    $fechaHoy = $dias[date('l')] . ", " . date('j') . " de " . $meses[date('F')] . " de " . date('Y');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Edu-System</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <?php include_once("../../../ui/header/header.php"); ?>

    <?php include_once("../../../ui/adminSideBar/adminSideBar.php"); ?>
    
    <main class="dashboard-main">
        <div class="dashboard-content">
            
            <!-- Banner de Saludo y Bienvenida -->
            <section class="welcome-banner">
                <div class="welcome-text">
                    <h1><?php echo htmlspecialchars($saludo . ", " . $user['name']); ?></h1>
                    <p class="welcome-subtitle">Resumen general y rendimiento de la plataforma de cursos online.</p>
                </div>
                <div class="welcome-date">
                    <span class="date-chip"><?php echo htmlspecialchars($fechaHoy); ?></span>
                </div>
            </section>

            <!-- Tarjetas de Métricas Clave (KPIs) -->
            <section class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">Estudiantes Activos</span>
                    </div>
                    <div class="kpi-body">
                        <h2 class="kpi-value">0</h2>
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">Cursos en Catálogo</span>
                    </div>
                    <div class="kpi-body">
                        <h2 class="kpi-value">0</h2>
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">Inscripciones del Mes</span>
                    </div>
                    <div class="kpi-body">
                        <h2 class="kpi-value">0</h2>
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">Tasa de Finalización</span>
                    </div>
                    <div class="kpi-body">
                        <h2 class="kpi-value">0%</h2>
                    </div>
                </div>
            </section>

            <!-- Sección Principal: Tabla de Cursos y Feed de Actividad -->
            <div class="dashboard-columns">
                
                <!-- Columna Izquierda: Cursos Populares y Acciones Rápidas -->
                <div class="dashboard-col-main">
                    <div class="dashboard-card">
                        <div class="card-header-flex">
                            <div>
                                <h3 class="card-title">Cursos con Mayor Demanda</h3>
                                <p class="card-desc">Cursos con más alumnos inscritos y mejores valoraciones</p>
                            </div>
                            <span class="badge-total">0 cursos</span>
                        </div>

                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Curso</th>
                                        <th>Instructor</th>
                                        <th>Inscritos</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Actividad Reciente y Estado de la Plataforma -->
                <div class="dashboard-col-side">
                    <div class="dashboard-card">
                        <h3 class="card-title">Actividad Reciente</h3>
                        <p class="card-desc">Últimos eventos en el sistema</p>

                        <div class="activity-timeline">
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </main>
</body>
</html>