<?php
require_once __DIR__ . '/../../controllers/auth/check_auth.php';
$user = getLoggedUser();
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Cursos · Edu-System</title>
        <link rel="stylesheet" href="courses.css">
    </head>
    <body>
        <?php include __DIR__ . '/../../../ui/header/header.php'; ?>
        <?php include __DIR__ . '/../../../ui/adminSideBar/adminSideBar.php'; ?>

        <div class="workspace">
            <main>

                <!-- Contenido de Cursos: edita aquí los textos, las tarjetas y las tablas. -->
                <div id="page-content">
                    <!-- Courses catalog presentation prepared for real backend data. -->
                    <section class="page-heading">
                        <div>
                            <p class="eyebrow">GESTIÓN ACADÉMICA</p>
                            <h1>Catálogo de cursos</h1>
                            <p class="subtitle">Organiza la oferta académica y da seguimiento a cada curso.</p>
                        </div>
                        <div class="actions">
                            <button class="btn" type="button">Exportar CSV</button>
                        </div>
                    </section>
                    <section class="kpi-grid">
                        <article class="kpi">
                            <h2 class="kpi-label">Cursos en catálogo</h2>
                            <strong class="kpi-value"><!-- Dynamic value: total catalog courses count --></strong>
                        </article>
                        <article class="kpi">
                            <h2 class="kpi-label">Publicados</h2>
                            <strong class="kpi-value"><!-- Dynamic value: published courses count --></strong>
                        </article>
                        <article class="kpi">
                            <h2 class="kpi-label">En revisión</h2>
                            <strong class="kpi-value"><!-- Dynamic value: courses under review count --></strong>
                        </article>
                        <article class="kpi">
                            <h2 class="kpi-label">Inscripciones</h2>
                            <strong class="kpi-value"><!-- Dynamic value: total enrollments count --></strong>
                        </article>
                    </section>
                    
                    <section class="panel">
                        <div class="panel-head">
                            <div>
                                <h2>Oferta académica</h2>
                                <p>Consulta la capacidad, los responsables y el estado de publicación.</p>
                            </div>
                        </div>
                        <div class="filters">
                            <label class="search">
                                <span class="filter-label">Buscar por curso, ponente o código</span>
                                <input class="field" id="search" type="search" placeholder="Buscar por curso, ponente o código…">
                            </label>
                            <label>
                                <span class="filter-label">Área académica</span>
                                <select id="category">
                                    <option value="">Todas las áreas</option>
                                    <option>Ciencias exactas</option>
                                    <option>Tecnología</option>
                                    <option>Humanidades</option>
                                </select>
                            </label>
                            <label>
                                <span class="filter-label">Estado</span>
                                <select id="status">
                                    <option value="">Todos los estados</option>
                                    <option>Publicado</option>
                                    <option>En revisión</option>
                                    <option>Pausado</option>
                                </select>
                            </label>
                        </div>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th scope="col">CURSO</th>
                                        <th scope="col">PONENTE</th>
                                        <th scope="col">INSCRITOS / CUPO</th>
                                        <th scope="col">ESTADO</th>
                                        <th scope="col">ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Course catalog will be loaded dynamically here -->
                                </tbody>
                            </table>
                        </div>
                        <div class="table-footer">
                            <span><!-- Dynamic value: pagination summary (e.g. Mostrando X–Y de Z registros) --></span>
                        </div>
                    </section>
                    <p class="subtitle">Los cursos en revisión y pausados permanecen en el catálogo administrativo.</p>

                </div>
            </main>
        </div>
    </body>
</html>
