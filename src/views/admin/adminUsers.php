<?php
require_once __DIR__ . '/../../controllers/auth/check_auth.php';
$user = getLoggedUser();
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Usuarios · Edu-System</title>
        <link rel="stylesheet" href="users.css">
    </head>
    <body>
        <?php include __DIR__ . '/../../../ui/header/header.php'; ?>
        <?php include __DIR__ . '/../../../ui/adminSideBar/adminSideBar.php'; ?>

        <div class="workspace">
            <main>

                <!-- Contenido de Usuarios: edita aquí los textos, las tarjetas y las tablas. -->
                <div id="page-content">
                    <!-- Users management presentation prepared for real backend data. -->
                    <section class="page-heading">
                        <div>
                            <p class="eyebrow">GESTIÓN ACADÉMICA</p>
                            <h1>Comunidad escolar</h1>
                            <p class="subtitle">Administra los perfiles, roles y accesos de tu institución.</p>
                        </div>
                        <div class="actions">
                            <button class="btn" type="button" disabled title="Exportación pendiente de conectar al backend">Exportar CSV</button>
                        </div>
                    </section>
                    <section class="kpi-grid">
                        <article class="kpi">
                            <h2 class="kpi-label">Usuarios registrados</h2>
                            <strong class="kpi-value"><!-- Dynamic value: total registered users count --></strong>
                        </article>
                        <article class="kpi">
                            <h2 class="kpi-label">Alumnos</h2>
                            <strong class="kpi-value"><!-- Dynamic value: total students count --></strong>
                        </article>
                        <article class="kpi">
                            <h2 class="kpi-label">Equipo académico</h2>
                            <strong class="kpi-value"><!-- Dynamic value: total teachers and directors count --></strong>
                        </article>
                        <article class="kpi">
                            <h2 class="kpi-label">Por verificar</h2>
                            <strong class="kpi-value"><!-- Dynamic value: pending verification accounts count --></strong>
                        </article>
                    </section>
                   
                    <section class="panel">
                        <div class="panel-head">
                            <div>
                                <h2>Directorio de usuarios</h2>
                                <p>Consulta y edita los perfiles de la comunidad.</p>
                            </div>
                        </div>
                        <div class="filters">
                            <label class="search">
                                <span class="filter-label">Buscar por nombre, correo o ID</span>
                                <input class="field" id="search" disabled title="Búsqueda pendiente de conectar al backend" type="search" placeholder="Buscar por nombre, correo o ID…">
                            </label>
                            <label>
                                <span class="filter-label">Rol</span>
                                <select id="category" disabled title="Filtros pendientes de conectar al backend">
                                    <option value="">Todos los roles</option>
                                    <option>Alumno</option>
                                    <option>Ponente</option>
                                    <option>Director</option>
                                    <option>Administrador</option>
                                </select>
                            </label>
                            <label>
                                <span class="filter-label">Estado</span>
                                <select id="status" disabled title="Filtros pendientes de conectar al backend">
                                    <option value="">Todos los estados</option>
                                    <option>Verificado</option>
                                    <option>Pendiente</option>
                                </select>
                            </label>
                        </div>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th scope="col">USUARIO</th>
                                        <th scope="col">ID / CONTROL</th>
                                        <th scope="col">ROL</th>
                                        <th scope="col">ESTADO</th>
                                        <th scope="col">ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Users list will be loaded dynamically here -->
                                </tbody>
                            </table>
                        </div>
                        <div class="table-footer">
                            <span><!-- Dynamic value: pagination summary (e.g. Mostrando X–Y de Z registros) --></span>
                        </div>
                    </section>
                    <p class="subtitle">Los roles organizan la participación de alumnos, ponentes, directores y administradores.</p>

                </div>
            </main>
        </div>
    </body>
</html>
