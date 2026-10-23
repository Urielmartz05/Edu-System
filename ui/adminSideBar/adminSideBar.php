<link rel="stylesheet" href="/ui/adminSideBar/adminSideBar.css">

<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $currentPage = basename($_SERVER['PHP_SELF']);
?>

<aside>

    <div class="user-container">
        <div class="user-info">
            <div class="profile-picture">
                <img src="/resources/images/userIcon.svg" alt="User Icon" class="user-icon">
            </div>
            <div class="user-details">
                <p><?php echo $_SESSION['name']; ?> <?php echo $_SESSION['lastname']; ?></p>
                <p><?php echo $_SESSION['email']; ?></p>
            </div>
        </div>
    </div>

    <div class="options-container">

        <ul>
            <li class="dashboard-option">
                <a href="/src/views/admin/adminDashboard.php" class="sidebar-link">
                    <div class="option <?php echo ($currentPage == 'adminDashboard.php') ? 'active' : ''; ?>">
                        <img src="/resources/images/Home.png" alt="Dashboard" class="icon">
                        <p>Dashboard</p>
                    </div>
                </a>
            </li>
            <li class="users-option">
                <a href="/src/views/admin/adminUsers.php" class="sidebar-link">
                    <div class="option <?php echo ($currentPage == 'adminUsers.php') ? 'active' : ''; ?>">
                        <img src="/resources/images/Users.png" alt="Users" class="icon">
                        <p>Users</p>
                    </div>
                </a>
            </li>
            <li class="courses-option">
                <a href="/src/views/admin/adminCourses.php" class="sidebar-link">
                    <div class="option <?php echo ($currentPage == 'adminCourses.php') ? 'active' : ''; ?>">
                        <img src="/resources/images/Lesson.png" alt="Courses" class="icon">
                        <p>Courses</p>
                    </div>
                </a>
            </li>
            <li class="statistics-option">
                <a href="/src/views/admin/adminStatistics.php" class="sidebar-link">
                    <div class="option <?php echo ($currentPage == 'adminStatistics.php') ? 'active' : ''; ?>">
                        <img src="/resources/images/Stats.png" alt="Statistics" class="icon">
                        <p>Statistics</p>
                    </div>
                </a>
            </li>
        </ul>

    </div>

</aside>