<link rel="stylesheet" href="/components/teacherSidebar/teacherSidebar.css">

<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $currentSidebarNav = basename($_SERVER['PHP_SELF']);
    $userName = htmlspecialchars(trim(($_SESSION['name'] ?? 'Faculty') . ' ' . ($_SESSION['lastname'] ?? '')));
    $userEmail = htmlspecialchars($_SESSION['email'] ?? 'instructor@edu-system.com');
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
                <a href="/src/views/ponente/ponente.php" class="sidebar-link" title="Dashboard">
                    <div class="option <?php echo ($currentSidebarNav == 'ponente.php') ? 'active' : ''; ?>">
                        <img src="/resources/images/Home.png" alt="Dashboard" class="icon">
                        <p>Dashboard</p>
                    </div>
                </a>
            </li>
            <li class="courses-option">
                <a href="/src/views/ponente/ponenteCourses.php" class="sidebar-link" title="My Courses">
                    <div class="option <?php echo in_array($currentSidebarNav, ['ponenteCourses.php', 'ponenteCourseContent.php']) ? 'active' : ''; ?>">
                        <img src="/resources/images/Lesson.png" alt="My courses" class="icon">
                        <p>My courses</p>
                    </div>
                </a>
            </li>
            <li class="assignments-option">
                <a href="/src/views/ponente/ponenteAssignments.php" class="sidebar-link" title="Assignments">
                    <div class="option <?php echo ($currentSidebarNav == 'ponenteAssignments.php') ? 'active' : ''; ?>">
                        <img src="/resources/images/Assignments.svg" alt="Assignments" class="icon">
                        <p>Assignments</p>
                    </div>
                </a>
            </li>
            <li class="grades-option">
                <a href="/src/views/ponente/ponenteGrades.php" class="sidebar-link" title="Gradebook">
                    <div class="option <?php echo ($currentSidebarNav == 'ponenteGrades.php') ? 'active' : ''; ?>">
                        <img src="/resources/images/Grades.svg" alt="Grades" class="icon">
                        <p>Grades</p>
                    </div>
                </a>
            </li>
        </ul>

    </div>

</aside>
