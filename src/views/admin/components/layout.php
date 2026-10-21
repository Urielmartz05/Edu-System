<?php
// Presentation shared by admin views. Authentication remains in the existing controller.
function adminStart($page, $user) {
    $titles = ['dashboard' => 'Panel general', 'users' => 'Usuarios', 'courses' => 'Cursos', 'statistics' => 'Estadísticas'];
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?php echo $titles[$page]; ?> · Edu-System</title><link rel="stylesheet" href="admin.css"></head>
<body data-page="<?php echo $page; ?>">
<a class="skip-link" href="#main">Saltar al contenido</a>
<?php include __DIR__ . '/../../../../ui/header/header.php'; ?>
<?php include __DIR__ . '/../../../../ui/adminSideBar/adminSideBar.php'; ?>
<div class="workspace">
<main id="main" tabindex="-1"><div class="demo-notice"><span class="notice-dot"></span>Vista de demostración <span>Datos ilustrativos. Las acciones de gestión se habilitarán al conectar el backend.</span></div><div id="page-content"><?php
    $views = ['dashboard' => 'dashboard.php', 'users' => 'users.php', 'courses' => 'courses.php', 'statistics' => 'statistics.php'];
    include __DIR__ . '/' . $views[$page];
?></div></main>
<footer class="page-footer"><span>Edu-System · Gestión académica</span><span>Un espacio para la educación</span></footer>
</div>

</body></html>
<?php }
