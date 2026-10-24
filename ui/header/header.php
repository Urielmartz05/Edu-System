<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $roleType = '';
    if (isset($_SESSION['user_id']) && !empty($_SESSION['role_id'])) {
        include_once __DIR__ . "/../../src/controllers/role/getRoleType.php";
        $roleType = getRoleType($_SESSION['role_id']);
    }
?>

<link rel="stylesheet" href="/ui/header/header.css">

<header class="main-header">
    <nav>
        <div class="logo">
            <a href="/index.php" class="school-brand" title="Edu-System Academic Portal">
                <span class="school-brand-title">Edu-System</span>
                <span class="school-brand-pill">PORTAL</span>
            </a>
        </div>

        <div class="more-info">
        <?php 
            if (isset($_SESSION['user_id'])) {
                echo "<div class='user-session'>
                        <span class='user-role-badge'>" . htmlspecialchars(ucfirst(strtolower($roleType ?: 'User'))) . "</span>
                        <a href='/src/controllers/logout/logout.php' class='logout-link' title='Sign out of your session'>Log out</a>
                      </div>";
            } else {
                echo '<ul class="header-auth-nav">
                        <li>
                            <a href="/index.php" class="header-nav-link">Log in</a>
                        </li>
                        <li>
                            <a href="/src/views/signup/signup.php" class="header-signup-btn">Sign up</a>
                        </li>
                    </ul>'; 
            }
        ?>
        </div>
    </nav>
</header>
