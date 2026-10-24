<link rel="stylesheet" href="/components/directorSidebar/directorSidebar.css">

<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $currentPage = basename($_SERVER['PHP_SELF']);
    $userName = htmlspecialchars(($_SESSION['name'] ?? 'Director') . ' ' . ($_SESSION['lastname'] ?? ''));
    $userEmail = htmlspecialchars($_SESSION['email'] ?? 'director@edu-system.com');
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
                <a href="/src/views/director/director.php" class="sidebar-link">
                    <div class="option <?php echo ($currentPage == 'director.php') ? 'active' : ''; ?>">
                        <img src="/resources/images/Home.png" alt="Dashboard" class="icon">
                        <p>Dashboard</p>
                    </div>
                </a>
            </li>
            <li class="courses-option">
                <a href="/src/views/director/director.php#coursesSection" class="sidebar-link">
                    <div class="option">
                        <img src="/resources/images/Lesson.png" alt="Courses" class="icon">
                        <p>Courses</p>
                    </div>
                </a>
            </li>
            <li class="faculty-option">
                <a href="/src/views/director/director.php#facultySection" class="sidebar-link">
                    <div class="option">
                        <img src="/resources/images/Users.png" alt="Faculty" class="icon">
                        <p>Faculty</p>
                    </div>
                </a>
            </li>
        </ul>

    </div>

</aside>
