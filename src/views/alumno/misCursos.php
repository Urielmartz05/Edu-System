<?php
    require_once __DIR__ . "/../../controllers/auth/check_auth.php";
    requireRole([4]);
    require_once __DIR__ . "/../../models/studentModels/studentEnrollmentModel.php";
    $user = getLoggedUser();

    $enrollmentModel = new StudentEnrollment();
    $enrolledCourses = $enrollmentModel->getEnrolledCourses($user['id']);

    // Pure PHP search filter
    $search = trim($_GET['search'] ?? '');
    if (!empty($search)) {
        $enrolledCourses = array_filter($enrolledCourses, function($c) use ($search) {
            return stripos($c['course_name'] ?? '', $search) !== false
                || stripos($c['department'] ?? '', $search) !== false;
        });
    }

    $flashSuccess = $_SESSION['flash_success'] ?? null;
    $flashError = $_SESSION['flash_error'] ?? null;
    unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses · EduX</title>
    <link rel="stylesheet" href="misCursos.css">
</head>
<body>
    <?php include __DIR__ . '/../../../ui/header/header.php'; ?>
    <?php include __DIR__ . '/../../../components/studentSidebar/studentSidebar.php'; ?>

    <div class="workspace">
        <main>
            <div id="page-content">

                <!-- Flash Alerts -->
                <?php if (!empty($flashSuccess)): ?>
                    <div class="alert-banner alert-success">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        <span><?php echo htmlspecialchars($flashSuccess); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($flashError)): ?>
                    <div class="alert-banner alert-error">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span><?php echo htmlspecialchars($flashError); ?></span>
                    </div>
                <?php endif; ?>

                <!-- Portal Heading Hero -->
                <section class="page-heading">
                    <div>
                        <span class="eyebrow">ACADEMIC ENROLLMENT · PERIOD 2026-B</span>
                        <h1>My Enrolled Courses</h1>
                        <p class="subtitle">Select one of your active academic programs to enter its syllabus modules, download study materials, and monitor your progress.</p>
                    </div>
                </section>

                <!-- Grid of Enrolled Courses -->
                <section class="panel">
                    <div class="panel-head">
                        <div>
                            <h2>Assigned Curricular Programs</h2>
                            <p>Courses and study programs registered to your student profile.</p>
                        </div>
                        <span class="badge-tag"><?php echo count($enrolledCourses); ?> Course(s) Enrolled</span>
                    </div>

                    <div class="filters-bar">
                        <form method="GET" action="misCursos.php" class="search-wrap" style="width:100%; max-width:420px; display:flex; align-items:center;">
                            <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <input type="search" name="search" class="search-input" id="courseSearch" placeholder="Search by course name or department..." value="<?php echo htmlspecialchars($search); ?>">
                        </form>
                    </div>

                    <div class="courses-grid" id="coursesGrid">
                        <?php if (!empty($enrolledCourses)): ?>
                            <?php foreach ($enrolledCourses as $course):
                                $prog = (float)($course['progress'] ?? 0);
                            ?>
                                <a href="../../controllers/alumno/selectCourse.php?course_id=<?php echo (int)$course['id']; ?>&target=courses" class="course-card" style="text-decoration:none; color:inherit; display:flex;">
                                    <div class="card-top">
                                        <span class="badge-dept"><?php echo htmlspecialchars($course['department'] ?? 'General'); ?></span>
                                        <span class="badge-status active">
                                            <span class="status-dot"></span>
                                            Enrolled
                                        </span>
                                    </div>

                                    <h3 class="course-title"><?php echo htmlspecialchars($course['course_name']); ?></h3>

                                    <div class="progress-container">
                                        <div class="progress-labels">
                                            <span>Syllabus Progress</span>
                                            <strong><?php echo number_format($prog, 1); ?>%</strong>
                                        </div>
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?php echo min(100, max(0, $prog)); ?>%;"></div>
                                        </div>
                                    </div>

                                    <div class="card-meta">
                                        <div class="meta-item" title="Modules in course">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                            <span><?php echo (int)$course['total_blocks']; ?> module(s)</span>
                                        </div>
                                        <div class="meta-item" title="Academic credits">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
                                            <span><?php echo (int)$course['credits']; ?> credits</span>
                                        </div>
                                    </div>

                                    <div class="card-footer">
                                        <span class="btn-enter-course">
                                            <span>Open Course Syllabus</span>
                                            <svg class="arrow-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                        </span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon-wrap">
                                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                </div>
                                <h3>No courses assigned to your student profile</h3>
                                <p>You have not been assigned to any academic programs for this cycle yet. Please contact your academic administrator.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

            </div>
        </main>
    </div>
</body>
</html>
