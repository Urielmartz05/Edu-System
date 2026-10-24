<?php
    require_once __DIR__ . "/../../controllers/auth/check_auth.php";
    require_once __DIR__ . "/../../models/teacherModels/teacherCoursesModel.php";
    require_once __DIR__ . "/../../models/teacherModels/teacherDashboardModel.php";
    $user = getLoggedUser();

    $teacherId = (int)$user['id'];
    $coursesModel = new TeacherCourses();
    $dashboardModel = new TeacherDashboard();

    $assignedCourses = $coursesModel->getCoursesWithDetails($teacherId);
    $totalCourses = $dashboardModel->getAssignedCoursesCount($teacherId);
    $totalStudents = $dashboardModel->getEnrolledStudentsCount($teacherId);
    $totalBlocks = $dashboardModel->getAssignedBlocksCount($teacherId);
    $avgProgress = $dashboardModel->getAverageProgress($teacherId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses · Edu-System</title>
    <link rel="stylesheet" href="courses.css">
</head>
<body>
    <?php include __DIR__ . '/../../../ui/header/header.php'; ?>
    <?php include __DIR__ . '/../../../components/teacherSidebar/teacherSidebar.php'; ?>

    <div class="workspace">
        <main>
            <div id="page-content">
                <!-- Page Heading -->
                <section class="page-heading">
                    <div>
                        <p class="eyebrow">FACULTY & INSTRUCTOR PORTAL · PERIOD 2026-B</p>
                        <h1>Course Directory & Syllabi</h1>
                        <p class="subtitle">Search, filter, and inspect all curricular programs and syllabus modules assigned to your teaching profile.</p>
                    </div>
                </section>

                <!-- Main Courses Panel -->
                <section class="panel">
                    <div class="panel-head">
                        <div>
                            <h2>Assigned Curricular Programs</h2>
                            <p>Complete directory of courses and groups for the current academic period.</p>
                        </div>
                        <span class="badge-tag"><?php echo count($assignedCourses); ?> Course(s)</span>
                    </div>

                    <div class="filters-bar">
                        <input type="search" class="search-input" id="courseSearch" placeholder="Search by course name or department..." onkeyup="filterCourses()">
                        <select class="filter-select" id="statusFilter" onchange="filterCourses()">
                            <option value="">All statuses</option>
                            <option value="Active">Active</option>
                            <option value="In Review">In Review</option>
                        </select>
                    </div>

                    <div class="courses-grid" id="coursesGrid">
                        <?php if (!empty($assignedCourses)): ?>
                            <?php foreach ($assignedCourses as $c): 
                                $prog = (float)($c['progress'] ?? 0);
                                $isPublished = ((int)$c['status'] === 1);
                                $statusText = $isPublished ? 'Active' : 'In Review';
                                $statusClass = $isPublished ? 'active' : 'pending';
                            ?>
                                <article class="course-card" data-status="<?php echo $statusText; ?>" onclick="window.location.href='ponenteCourseContent.php?course_id=<?php echo (int)$c['id']; ?>'" tabindex="0">
                                    <div class="card-top">
                                        <span class="badge-dept"><?php echo htmlspecialchars($c['department_name'] ?? 'General'); ?></span>
                                        <span class="badge-status <?php echo $statusClass; ?>">
                                            <span class="status-dot"></span>
                                            <?php echo $statusText; ?>
                                        </span>
                                    </div>

                                    <h3 class="course-title"><?php echo htmlspecialchars($c['course_name']); ?></h3>

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
                                        <div class="meta-item" title="Modules assigned to you">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                            <span><?php echo (int)($c['my_blocks_count'] ?? 1); ?> module(s) taught</span>
                                        </div>
                                        <div class="meta-item" title="Enrolled students">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                            <span><?php echo (int)$c['student_count']; ?> students</span>
                                        </div>
                                        <div class="meta-item" title="Academic credits">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
                                            <span><?php echo (int)($c['credits'] ?? 0); ?> credits</span>
                                        </div>
                                    </div>

                                    <div class="card-footer">
                                        <span class="btn-enter-course">
                                            <span>Open Course Workspace</span>
                                            <svg class="arrow-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                        </span>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon-wrap">
                                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                </div>
                                <h3>No courses assigned to your instructor profile</h3>
                                <p>You have not been assigned as an instructor to any academic syllabus modules yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script>
        function filterCourses() {
            const query = document.getElementById('courseSearch').value.toLowerCase().trim();
            const status = document.getElementById('statusFilter').value;
            const cards = document.querySelectorAll('#coursesGrid .course-card');

            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                const cardStatus = card.getAttribute('data-status') || '';
                const matchesQuery = text.includes(query);
                const matchesStatus = !status || cardStatus === status;

                card.style.display = (matchesQuery && matchesStatus) ? '' : 'none';
            });
        }
    </script>
</body>
</html>
