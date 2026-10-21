<?php
    require_once("../../controllers/auth/check_auth.php");
    $user = getLoggedUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Edu-System</title>
    <link rel="stylesheet" href="users.css">
</head>
<body>
    <?php include_once("../../../ui/header/header.php"); ?>

    <?php include_once("../../../ui/adminSideBar/adminSideBar.php"); ?>
    
    <main class="dashboard-main">
        <div class="dashboard-content">
            
            <!-- Encabezado de la página -->
            <section class="page-header">
                <div>
                    <h1>Gestión de Usuarios</h1>
                    <p class="page-subtitle">Administra los accesos, roles y estados de los usuarios en la plataforma.</p>
                </div>
                <div>
                    <button class="btn-primary" id="btnOpenModal">+ Nuevo Usuario</button>
                </div>
            </section>

            <!-- Resumen de Métricas de Usuarios -->
            <section class="kpi-grid">
                <div class="kpi-card">
                    <span class="kpi-title">Total Usuarios</span>
                    <h2 class="kpi-value">0</h2>
                </div>

                <div class="kpi-card">
                    <span class="kpi-title">Alumnos</span>
                    <h2 class="kpi-value">0</h2>
                </div>

                <div class="kpi-card">
                    <span class="kpi-title">Ponentes</span>
                    <h2 class="kpi-value">0</h2>
                </div>

                <div class="kpi-card">
                    <span class="kpi-title">Directores</span>
                    <h2 class="kpi-value">0</h2>
                </div>
            </section>

            <!-- Sección Principal: Filtros y Tabla de Usuarios -->
            <section class="dashboard-card users-section">
                
                <!-- Barra de Búsqueda y Filtros -->
                <div class="filter-bar">
                    <div class="search-input-wrapper">
                        <input type="text" class="search-input" placeholder="Buscar por nombre, correo o ID...">
                    </div>

                    <div class="filters-group">
                        <select class="filter-select">
                            <option value="">Todos los roles</option>
                            <option value="1">Administrador</option>
                            <option value="2">Alumno</option>
                            <option value="3">Director</option>
                            <option value="4">Ponente</option>
                        </select>

                        <select class="filter-select">
                            <option value="">Estado de cuenta</option>
                            <option value="1">Verificado</option>
                            <option value="0">Pendiente</option>
                        </select>
                    </div>
                </div>

                <!-- Tabla de Usuarios -->
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID / No. Control</th>
                                <th>Usuario</th>
                                <th>Correo Electrónico</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Las filas se cargarán dinámicamente -->
                        </tbody>
                    </table>
                </div>

            </section>

        </div>
    </main>

    <!-- Modal para Nuevo Usuario (Frontend) -->
    <div class="modal-overlay" id="userModal">
        <div class="modal-card">
            <div class="modal-header">
                <h3>Nuevo Usuario</h3>
                <button class="modal-close-btn" id="btnCloseModal">✕</button>
            </div>
            <form class="modal-form" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Número de Control / ID</label>
                        <input type="number" placeholder="Ej. 20240101" required>
                    </div>
                    <div class="form-group">
                        <label>Rol</label>
                        <select required>
                            <option value="" disabled selected>Seleccionar rol</option>
                            <option value="1">Administrador</option>
                            <option value="2">Alumno</option>
                            <option value="3">Director</option>
                            <option value="4">Ponente</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre(s)</label>
                        <input type="text" placeholder="Nombre" required>
                    </div>
                    <div class="form-group">
                        <label>Apellidos</label>
                        <input type="text" placeholder="Apellidos" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Correo Electrónico</label>
                    <input type="email" placeholder="correo@institucion.edu" required>
                </div>

                <div class="form-group">
                    <label>Contraseña Provisional</label>
                    <input type="password" placeholder="Contraseña de acceso" required>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" id="btnCancelModal">Cancelar</button>
                    <button type="submit" class="btn-primary">Guardar Usuario</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('userModal');
        const openBtn = document.getElementById('btnOpenModal');
        const closeBtn = document.getElementById('btnCloseModal');
        const cancelBtn = document.getElementById('btnCancelModal');

        openBtn.addEventListener('click', () => modal.classList.add('show'));
        closeBtn.addEventListener('click', () => modal.classList.remove('show'));
        cancelBtn.addEventListener('click', () => modal.classList.remove('show'));
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.classList.remove('show');
        });
    </script>
</body>
</html>
