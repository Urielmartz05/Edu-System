<?php
// Presentation shared by admin views. Authentication remains in the existing controller.
function adminStart($page, $user) {
    $titles = ['dashboard' => 'Panel general', 'users' => 'Usuarios', 'courses' => 'Cursos', 'statistics' => 'Estadísticas'];
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?php echo $titles[$page]; ?> · Edu-System</title><link rel="stylesheet" href="admin.css"><script src="admin.js" defer></script></head>
<body data-page="<?php echo $page; ?>">
<a class="skip-link" href="#main">Saltar al contenido</a>
<?php include __DIR__ . '/../../../../ui/header/header.php'; ?>
<?php include __DIR__ . '/../../../../ui/adminSideBar/adminSideBar.php'; ?>
<div class="workspace">
<main id="main" tabindex="-1"><div class="demo-notice"><span class="notice-dot"></span>Vista de demostración <span>Los datos son ilustrativos. Los cambios solo se conservan en esta pestaña.</span></div><noscript><section class="insight"><strong>Activa JavaScript para consultar el panel.</strong><p>Las tablas, los indicadores y los formularios de esta vista requieren JavaScript.</p></section></noscript><div id="page-content"></div></main>
<footer class="page-footer"><span>Edu-System · Gestión académica</span><span>Un espacio para la educación</span></footer>
</div>
<dialog id="editor" aria-labelledby="dialog-title"></dialog><div class="toast" role="status" aria-live="polite" hidden></div>
</body></html>
<?php }
