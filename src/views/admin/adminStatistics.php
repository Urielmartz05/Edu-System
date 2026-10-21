<?php
require_once __DIR__ . '/../../controllers/auth/check_auth.php';
$user = getLoggedUser();
require_once __DIR__ . '/components/layout.php';
adminStart('statistics', $user);
