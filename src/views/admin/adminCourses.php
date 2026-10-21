<?php
    require_once("../../controllers/auth/check_auth.php");
    $user = getLoggedUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Cursos - Edu-System</title>
    <link rel="stylesheet" href="courses.css">
</head>
<body>
    <?php include_once("../../../ui/header/header.php"); ?>

    <?php include_once("../../../ui/adminSideBar/adminSideBar.php"); ?>
    
    <main class="dashboard-main">
        <div class="dashboard-content">
            
            <!-- Encabezado de la página -->
            <section class="page-header">
                <div>
                    <h1>Gestión de Cursos</h1>
                    <p class="page-subtitle">Administra el catálogo de cursos, materias, ponentes asignados y estados de publicación.</p>
                </div>
                <div>
                    <button class="btn-primary" id="btnOpenModal">+ Nuevo Curso</button>
                </div>
            </section>

            <!-- Resumen de Métricas de Cursos -->
            <section class="kpi-grid">
                <div class="kpi-card">
                    <span class="kpi-title">Total Cursos</span>
                    <h2 class="kpi-value">0</h2>
                </div>

                <div class="kpi-card">
                    <span class="kpi-title">Publicados</span>
                    <h2 class="kpi-value">0</h2>
                </div>

                <div class="kpi-card">
                    <span class="kpi-title">En Revisión</span>
                    <h2 class="kpi-value">0</h2>
                </div>

                <div class="kpi-card">
                    <span class="kpi-title">Total Inscripciones</span>
                    <h2 class="kpi-value">0</h2>
                </div>
            </section>

            <!-- Sección Principal: Filtros y Tabla de Cursos -->
            <section class="dashboard-card courses-section">
                
                <!-- Barra de Búsqueda y Filtros -->
                <div class="filter-bar">
                    <div class="search-input-wrapper">
                        <input type="text" class="search-input" placeholder="Buscar por título, instructor o código...">
                    </div>

                    <div class="filters-group">
                        <select class="filter-select">
                            <option value="">Todas las categorías</option>
                            <option value="Programación">Programación</option>
                            <option value="Bases de Datos">Bases de Datos</option>
                            <option value="Diseño">Diseño UI/UX</option>
                            <option value="Ciberseguridad">Ciberseguridad</option>
                            <option value="Redes">Redes e Infraestructura</option>
                        </select>

                        <select class="filter-select">
                            <option value="">Estado del curso</option>
                            <option value="publicado">Publicado</option>
                            <option value="revision">En revisión</option>
                            <option value="pausado">Pausado</option>
                        </select>
                    </div>
                </div>

                <!-- Tabla de Cursos -->
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Código / ID</th>
                                <th>Curso</th>
                                <th>Instructor / Ponente</th>
                                <th>Inscritos</th>
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

    <!-- Modal para Nuevo Curso (Frontend) -->
    <div class="modal-overlay" id="courseModal">
        <div class="modal-card">
            <div class="modal-header">
                <h3>Nuevo Curso</h3>
                <button class="modal-close-btn" id="btnCloseModal">✕</button>
            </div>
            <form class="modal-form" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Código del Curso</label>
                        <input type="text" placeholder="Ej. CURSO-101" required>
                    </div>
                    <div class="form-group">
                        <label>Categoría</label>
                        <select required>
                            <option value="" disabled selected>Seleccionar categoría</option>
                            <option value="Programación">Programación</option>
                            <option value="Bases de Datos">Bases de Datos</option>
                            <option value="Diseño">Diseño UI/UX</option>
                            <option value="Ciberseguridad">Ciberseguridad</option>
                            <option value="Redes">Redes e Infraestructura</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Título del Curso</label>
                    <input type="text" placeholder="Ej. Desarrollo Web con PHP y MySQL" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Ponente / Instructor Asignado</label>
                        <select required>
                            <option value="" disabled selected>Seleccionar instructor</option>
                            <!-- Se listarán los usuarios con rol de Ponente -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Estado Inicial</label>
                        <select required>
                            <option value="publicado">Publicado</option>
                            <option value="revision" selected>En revisión</option>
                            <option value="pausado">Pausado</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Descripción Breve</label>
                    <textarea rows="3" placeholder="Resumen del contenido y objetivos del curso..."></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" id="btnCancelModal">Cancelar</button>
                    <button type="submit" class="btn-primary">Guardar Curso</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('courseModal');
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
