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
        <link rel="stylesheet" href="admin.css">
    </head>
    <body>
        <a class="skip-link" href="#main">Saltar al contenido</a>
        <?php include __DIR__ . '/../../../ui/header/header.php'; ?>
        <?php include __DIR__ . '/../../../ui/adminSideBar/adminSideBar.php'; ?>

        <div class="workspace">
            <main id="main" tabindex="-1">
                <div class="demo-notice">
                    <span class="notice-dot"></span>Vista de demostración
                    <span>Datos ilustrativos. Las acciones de gestión se habilitarán al conectar el backend.</span>
                </div>

                <!-- Contenido de Usuarios: edita aquí los textos, las tarjetas y las tablas. -->
                <div id="page-content">
                    <!-- Presentación HTML con datos de muestra; sin persistencia ni JavaScript. -->
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
                    <section class="kpi-grid " aria-label="Indicadores de demostración">
                        <article class="kpi">
                            <span class="kpi-index">01</span>
                            <h2 class="kpi-label">Usuarios registrados</h2>
                            <strong class="kpi-value">8</strong>
                            <p class="kpi-note">Todos los perfiles</p>
                        </article>
                        <article class="kpi">
                            <span class="kpi-index">02</span>
                            <h2 class="kpi-label">Alumnos</h2>
                            <strong class="kpi-value">4</strong>
                            <p class="kpi-note">Comunidad estudiantil</p>
                        </article>
                        <article class="kpi">
                            <span class="kpi-index">03</span>
                            <h2 class="kpi-label">Equipo académico</h2>
                            <strong class="kpi-value">3</strong>
                            <p class="kpi-note">Ponentes y directores</p>
                        </article>
                        <article class="kpi">
                            <span class="kpi-index">04</span>
                            <h2 class="kpi-label">Por verificar</h2>
                            <strong class="kpi-value">1</strong>
                            <p class="kpi-note">Cuentas pendientes</p>
                        </article>
                    </section>
                    <details class="panel preview-editor">
                        <summary>Nuevo usuario</summary>
                        <form class="preview-form" aria-describedby="form-note">
                            <div class="dialog-head">
                                <h2 id="dialog-title">Nuevo usuario</h2>
                            </div>
                            <p id="form-note" class="dialog-note">Vista previa del formulario. El guardado estará disponible al conectar el backend.</p>
                            <div class="form-grid">
                                <label class="form-field ">Número de control / ID<input class="field" name="id" value="" type="text" required maxlength="120">
                                </label>
                                <label class="form-field">Rol<select name="role" required>
                                        <option >Alumno</option>
                                        <option >Ponente</option>
                                        <option >Director</option>
                                        <option >Administrador</option>
                                    </select>
                                </label>
                                <label class="form-field full">Nombre completo<input class="field" name="name" value="" type="text" required maxlength="120">
                                </label>
                                <label class="form-field full">Correo institucional<input class="field" name="email" value="" type="email" required maxlength="180">
                                </label>
                                <label class="form-field">Estado de cuenta<select name="status" required>
                                        <option >Pendiente</option>
                                        <option >Verificado</option>
                                    </select>
                                </label>
                            </div>
                            <div class="form-actions">
                                <button class="btn" type="reset">Limpiar</button>
                                <button class="btn primary" type="submit" disabled title="Pendiente de conectar al backend">Guardar usuario</button>
                            </div>
                        </form>
                    </details>
                    <section class="panel">
                        <div class="panel-head">
                            <div>
                                <h2>Directorio de usuarios</h2>
                                <p>Consulta y edita los perfiles de la comunidad.</p>
                            </div>
                            <span class="pill wine">Datos de muestra</span>
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
                                    <tr>
                                        <td>
                                            <div class="person">
                                                <span class="avatar" aria-hidden="true">VT</span>
                                                <span>
                                                    <span class="cell-title">Valentina Torres</span>
                                                    <span class="cell-sub">valentina.torres@institucion.edu</span>
                                                </span>
                                            </div>
                                        </td>
                                        <td>20260001</td>
                                        <td>
                                            <span class="pill ">Alumno</span>
                                        </td>
                                        <td>
                                            <span class="pill green">Verificado</span>
                                        </td>
                                        <td>
                                            <button type="button" disabled title="Edición pendiente de conectar al backend" class="row-action" aria-label="Editar a Valentina Torres">Editar</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="person">
                                                <span class="avatar" aria-hidden="true">DH</span>
                                                <span>
                                                    <span class="cell-title">Diego Hernández</span>
                                                    <span class="cell-sub">diego.hernandez@institucion.edu</span>
                                                </span>
                                            </div>
                                        </td>
                                        <td>20260002</td>
                                        <td>
                                            <span class="pill ">Alumno</span>
                                        </td>
                                        <td>
                                            <span class="pill green">Verificado</span>
                                        </td>
                                        <td>
                                            <button type="button" disabled title="Edición pendiente de conectar al backend" class="row-action" aria-label="Editar a Diego Hernández">Editar</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="person">
                                                <span class="avatar" aria-hidden="true">LM</span>
                                                <span>
                                                    <span class="cell-title">Laura Méndez</span>
                                                    <span class="cell-sub">laura.mendez@institucion.edu</span>
                                                </span>
                                            </div>
                                        </td>
                                        <td>DOC-001</td>
                                        <td>
                                            <span class="pill ">Ponente</span>
                                        </td>
                                        <td>
                                            <span class="pill green">Verificado</span>
                                        </td>
                                        <td>
                                            <button type="button" disabled title="Edición pendiente de conectar al backend" class="row-action" aria-label="Editar a Laura Méndez">Editar</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="person">
                                                <span class="avatar" aria-hidden="true">CR</span>
                                                <span>
                                                    <span class="cell-title">Carlos Rivera</span>
                                                    <span class="cell-sub">carlos.rivera@institucion.edu</span>
                                                </span>
                                            </div>
                                        </td>
                                        <td>DOC-002</td>
                                        <td>
                                            <span class="pill ">Ponente</span>
                                        </td>
                                        <td>
                                            <span class="pill green">Verificado</span>
                                        </td>
                                        <td>
                                            <button type="button" disabled title="Edición pendiente de conectar al backend" class="row-action" aria-label="Editar a Carlos Rivera">Editar</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="person">
                                                <span class="avatar" aria-hidden="true">SR</span>
                                                <span>
                                                    <span class="cell-title">Sofía Ramírez</span>
                                                    <span class="cell-sub">sofia.ramirez@institucion.edu</span>
                                                </span>
                                            </div>
                                        </td>
                                        <td>20260003</td>
                                        <td>
                                            <span class="pill ">Alumno</span>
                                        </td>
                                        <td>
                                            <span class="pill amber">Pendiente</span>
                                        </td>
                                        <td>
                                            <button type="button" disabled title="Edición pendiente de conectar al backend" class="row-action" aria-label="Editar a Sofía Ramírez">Editar</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="person">
                                                <span class="avatar" aria-hidden="true">AC</span>
                                                <span>
                                                    <span class="cell-title">Andrea Castillo</span>
                                                    <span class="cell-sub">andrea.castillo@institucion.edu</span>
                                                </span>
                                            </div>
                                        </td>
                                        <td>DIR-001</td>
                                        <td>
                                            <span class="pill ">Director</span>
                                        </td>
                                        <td>
                                            <span class="pill green">Verificado</span>
                                        </td>
                                        <td>
                                            <button type="button" disabled title="Edición pendiente de conectar al backend" class="row-action" aria-label="Editar a Andrea Castillo">Editar</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="person">
                                                <span class="avatar" aria-hidden="true">ML</span>
                                                <span>
                                                    <span class="cell-title">Mateo López</span>
                                                    <span class="cell-sub">mateo.lopez@institucion.edu</span>
                                                </span>
                                            </div>
                                        </td>
                                        <td>20260004</td>
                                        <td>
                                            <span class="pill ">Alumno</span>
                                        </td>
                                        <td>
                                            <span class="pill green">Verificado</span>
                                        </td>
                                        <td>
                                            <button type="button" disabled title="Edición pendiente de conectar al backend" class="row-action" aria-label="Editar a Mateo López">Editar</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="person">
                                                <span class="avatar" aria-hidden="true">GO</span>
                                                <span>
                                                    <span class="cell-title">Gabriel Ortiz</span>
                                                    <span class="cell-sub">gabriel.ortiz@institucion.edu</span>
                                                </span>
                                            </div>
                                        </td>
                                        <td>ADM-001</td>
                                        <td>
                                            <span class="pill wine">Administrador</span>
                                        </td>
                                        <td>
                                            <span class="pill green">Verificado</span>
                                        </td>
                                        <td>
                                            <button type="button" disabled title="Edición pendiente de conectar al backend" class="row-action" aria-label="Editar a Gabriel Ortiz">Editar</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="table-footer">
                            <span>Mostrando 1–8 de 8 registros</span>
                        </div>
                    </section>
                    <p class="subtitle">Los roles organizan la participación de alumnos, ponentes, directores y administradores.</p>

                </div>
            </main>
            <footer class="page-footer">
                <span>Edu-System · Gestión académica</span>
                <span>Un espacio para la educación</span>
            </footer>
        </div>
    </body>
</html>
