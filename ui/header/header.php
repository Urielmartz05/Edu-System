<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
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
                        <p class='user'>Welcome, " . htmlspecialchars($_SESSION['name']) . "!</p>
                        <a href='/logout/logout.php' class='logout-link'>Log out</a>
                      </div>";
            }

            else {
                echo '<ul>
                        <li>
                            <a href="/index.php">Log in</a>
                        </li>
                        <li>
                            <a href="/signup/signup.php">Sign up</a>
                        </li>
                    </ul>'; 
            }
        ?>
        </div>
    </nav>

</header>