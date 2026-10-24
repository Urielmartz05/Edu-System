<link rel="stylesheet" href="/components/studentSidebar/studentSidebar.css">

<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $currentPage = basename($_SERVER['PHP_SELF']);
    $userName = htmlspecialchars(($_SESSION['name'] ?? 'Student') . ' ' . ($_SESSION['lastname'] ?? ''));
    $userEmail = htmlspecialchars($_SESSION['email'] ?? 'student@edu-system.com');
?>

<aside>

    <div class="user-container">
        <div class="user-info">
            <div class="profile-picture">
                <img src="/resources/images/userIcon.svg" alt="User Icon" class="user-icon">
            </div>
            <div class="user-details">
                <p><?php echo $userName; ?></p>
                <p><?php echo $userEmail; ?></p>
            </div>
        </div>
    </div>

    <div class="options-container">

        <ul>
            <li class="dashboard-option">
                <a href="/src/views/alumno/alumnoDashboard.php" class="sidebar-link">
                    <div class="option <?php echo ($currentPage == 'alumno.php' || $currentPage == 'alumnoDashboard.php') ? 'active' : ''; ?>">
                        <img src="/resources/images/Home.png" alt="Dashboard" class="icon">
                        <p>Dashboard</p>
                    </div>
                </a>
            </li>
            <li class="courses-option">
                <a href="/src/views/alumno/misCursos.php" class="sidebar-link">
                    <div class="option <?php echo in_array($currentPage, ['misCursos.php', 'alumnoCourses.php', 'blockContent.php']) ? 'active' : ''; ?>">
                        <img src="/resources/images/Lesson.png" alt="My courses" class="icon">
                        <p>My courses</p>
                    </div>
                </a>
            </li>
        </ul>

    </div>

</aside>
