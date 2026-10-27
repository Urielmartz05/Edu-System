<link rel="stylesheet" href="/ui/adminSideBar/adminSideBar.css">

<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $currentSidebarNav = basename($_SERVER['PHP_SELF']);
    $userName = htmlspecialchars(trim(($_SESSION['name'] ?? 'Admin') . ' ' . ($_SESSION['lastname'] ?? '')));
    $userEmail = htmlspecialchars($_SESSION['email'] ?? 'admin@edu-system.com');
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
                <a href="/src/views/admin/adminDashboard.php" class="sidebar-link" title="Dashboard">
                    <div class="option <?php echo ($currentSidebarNav == 'adminDashboard.php') ? 'active' : ''; ?>">
                        <img src="/resources/images/Home.png" alt="Dashboard" class="icon">
                        <p>Dashboard</p>
                    </div>
                </a>
            </li>
            <li class="create-option">
                <a href="/src/views/admin/adminCreate.php" class="sidebar-link" title="Creation Hub">
                    <div class="option <?php echo ($currentSidebarNav == 'adminCreate.php') ? 'active' : ''; ?>">
                        <img src="/resources/images/Create.svg" alt="Creation Hub" class="icon">
                        <p>Creation Hub</p>
                    </div>
                </a>
            </li>
            <li class="users-option">
                <a href="/src/views/admin/adminUsers.php" class="sidebar-link" title="Users">
                    <div class="option <?php echo ($currentSidebarNav == 'adminUsers.php') ? 'active' : ''; ?>">
                        <img src="/resources/images/Users.png" alt="Users" class="icon">
                        <p>Users</p>
                    </div>
                </a>
            </li>
            <li class="courses-option">
                <a href="/src/views/admin/adminCourses.php" class="sidebar-link" title="Courses">
                    <div class="option <?php echo in_array($currentSidebarNav, ['adminCourses.php', 'adminCourseContent.php']) ? 'active' : ''; ?>">
                        <img src="/resources/images/Lesson.png" alt="Courses" class="icon">
                        <p>Courses</p>
                    </div>
                </a>
            </li>
            <li class="assignments-option">
                <a href="/src/views/admin/adminAssignments.php" class="sidebar-link" title="Assignments">
                    <div class="option <?php echo ($currentSidebarNav == 'adminAssignments.php') ? 'active' : ''; ?>">
                        <img src="/resources/images/Assignments.svg" alt="Assignments" class="icon">
                        <p>Assignments</p>
                    </div>
                </a>
            </li>
            <li class="grades-option">
                <a href="/src/views/admin/adminGrades.php" class="sidebar-link" title="Grades">
                    <div class="option <?php echo ($currentSidebarNav == 'adminGrades.php') ? 'active' : ''; ?>">
                        <img src="/resources/images/Grades.svg" alt="Grades" class="icon">
                        <p>Grades</p>
                    </div>
                </a>
            </li>
            <li class="statistics-option">
                <a href="/src/views/admin/adminStatistics.php" class="sidebar-link" title="Statistics">
                    <div class="option <?php echo ($currentSidebarNav == 'adminStatistics.php') ? 'active' : ''; ?>">
                        <img src="/resources/images/Stats.png" alt="Statistics" class="icon">
                        <p>Statistics</p>
                    </div>
                </a>
            </li>
        </ul>

    </div>

</aside>
