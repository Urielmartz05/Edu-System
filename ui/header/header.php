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

<header>
    <nav>
        <div class="logo">
            <p class="school-name"></p>
        </div>

        <div class="more-info">

        <?php 
            if (isset($_SESSION['user_id'])) {
                echo "<div class='user-session'>
                        <p class='user'>" . htmlspecialchars($roleType ?: $_SESSION['name']) . "!</p>
                        <a href='/src/controllers/logout/logout.php' class='logout-link'>Log out</a>
                      </div>";
            }

            else {
                echo '<ul>
                        <li>
                            <a href="/index.php">Log in</a>
                        </li>
                        <li>
                            <a href="/src/views/signup/signup.php">Sign up</a>
                        </li>
                    </ul>'; 
            }
        ?>
        </div>
    </nav>

</header>