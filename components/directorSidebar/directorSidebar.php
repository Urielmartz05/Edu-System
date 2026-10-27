<link rel="stylesheet" href="/components/directorSidebar/directorSidebar.css">

<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $currentSidebarNav = basename($_SERVER['PHP_SELF']);
    $userName = htmlspecialchars(trim(($_SESSION['name'] ?? 'Director') . ' ' . ($_SESSION['lastname'] ?? '')));
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
                <a href="/src/views/director/director.php" class="sidebar-link" title="Dashboard">
                    <div class="option <?php echo ($currentSidebarNav == 'director.php') ? 'active' : ''; ?>">
                        <img src="/resources/images/Home.png" alt="Dashboard" class="icon">
                        <p>Dashboard</p>
                    </div>
                </a>
            </li>
            <li class="courses-option">
                <a href="/src/views/director/directorCourses.php" class="sidebar-link" title="Courses">
                    <div class="option <?php echo in_array($currentSidebarNav, ['directorCourses.php', 'directorCourseContent.php']) ? 'active' : ''; ?>">
                        <img src="/resources/images/Lesson.png" alt="Courses" class="icon">
                        <p>Courses</p>
                    </div>
                </a>
            </li>
            <li class="faculty-option">
                <a href="/src/views/director/directorFaculty.php" class="sidebar-link" title="Faculty">
                    <div class="option <?php echo ($currentSidebarNav == 'directorFaculty.php') ? 'active' : ''; ?>">
                        <img src="/resources/images/Faculty.svg" alt="Faculty" class="icon">
                        <p>Faculty</p>
                    </div>
                </a>
            </li>
            <li class="students-option">
                <a href="/src/views/director/directorGrades.php?tab=students" class="sidebar-link" title="Students">
                    <div class="option <?php echo ($currentSidebarNav == 'directorGrades.php' && (($_GET['tab'] ?? '') === 'students' || isset($_GET['student_id']))) ? 'active' : ''; ?>">
                        <img src="/resources/images/Students.svg" alt="Students" class="icon">
                        <p>Students</p>
                    </div>
                </a>
            </li>
            <li class="assignments-option">
                <a href="/src/views/director/directorAssignments.php" class="sidebar-link" title="Assignments">
                    <div class="option <?php echo ($currentSidebarNav == 'directorAssignments.php') ? 'active' : ''; ?>">
                        <img src="/resources/images/Assignments.svg" alt="Assignments" class="icon">
                        <p>Assignments</p>
                    </div>
                </a>
            </li>
            <li class="grades-option">
                <a href="/src/views/director/directorGrades.php" class="sidebar-link" title="Academic Grades">
                    <div class="option <?php echo ($currentSidebarNav == 'directorGrades.php' && ($_GET['tab'] ?? '') !== 'students' && !isset($_GET['student_id'])) ? 'active' : ''; ?>">
                        <img src="/resources/images/Grades.svg" alt="Grades" class="icon">
                        <p>Grades</p>
                    </div>
                </a>
            </li>
        </ul>

    </div>

</aside>
