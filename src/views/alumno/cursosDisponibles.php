<?php
    require_once __DIR__ . "/../../controllers/auth/check_auth.php";
    require_once __DIR__ . "/../../models/studentModels/studentEnrollmentModel.php";
    $user = getLoggedUser();

    $enrollmentModel = new StudentEnrollment();
    $availableCourses = $enrollmentModel->getAvailableCourses($user['id']);

    $totalAvailable = count($availableCourses);
    $departments = array_unique(array_filter(array_column($availableCourses, 'department')));

    $error = $_GET['error'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Courses · Edu-System</title>
    <link rel="stylesheet" href="cursosDisponibles.css">
</head>
<body>
    <?php include __DIR__ . '/../../../ui/header/header.php'; ?>

    <div class="standalone-container">
        <main>
            <div id="page-content">

                <?php if ($error === 'enrollment_failed'): ?>
                    <div class="alert-error">
                        <svg class="alert-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <p>Enrollment could not be processed. Please try again.</p>
                    </div>
                <?php endif; ?>

                <!-- Heading -->
                <section class="page-heading">
                    <div class="heading-content">
                        <span class="eyebrow">ACADEMIC OFFERINGS</span>
                        <h1>Available Course Catalog</h1>
                        <p class="subtitle">Explore institutional programs and enroll immediately to unlock learning modules, syllabi, and study resources.</p>
                    </div>
                    <div class="heading-actions">
                        <a href="misCursos.php" class="btn-secondary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                            <span>Back to My Courses</span>
                        </a>
                    </div>
                </section>

                <!-- Search and Filters Bar -->
                <section class="filters-bar">
                    <div class="search-box">
                        <svg class="search-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="search" id="searchInput" placeholder="Search by course title..." onkeyup="filterCourses()">
                    </div>
                    <div class="dept-filter">
                        <select id="deptSelect" onchange="filterCourses()">
                            <option value="">All Departments</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo htmlspecialchars($dept); ?>"><?php echo htmlspecialchars($dept); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </section>

                <!-- Available Courses Grid -->
                <section class="catalog-section">
                    <div class="section-meta">
                        <p>Showing <strong id="courseCount"><?php echo $totalAvailable; ?></strong> available course(s) for enrollment</p>
                    </div>

                    <?php if (!empty($availableCourses)): ?>
                        <div class="courses-grid" id="coursesGrid">
                            <?php foreach ($availableCourses as $course): ?>
                                <article class="course-card" data-title="<?php echo strtolower(htmlspecialchars($course['course_name'])); ?>" data-dept="<?php echo htmlspecialchars($course['department'] ?? ''); ?>">
                                    <div class="card-top">
                                        <span class="badge-dept"><?php echo htmlspecialchars($course['department'] ?? 'General'); ?></span>
                                        <span class="badge-tag">Available</span>
                                    </div>

                                    <h3 class="course-title"><?php echo htmlspecialchars($course['course_name']); ?></h3>

                                    <div class="card-meta">
                                        <div class="meta-item">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                            <span><?php echo (int)$course['total_blocks']; ?> learning modules</span>
                                        </div>
                                        <div class="meta-item">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
                                            <span><?php echo (int)$course['credits']; ?> academic credits</span>
                                        </div>
                                        <div class="meta-item">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                            <span>100% Online Modality</span>
                                        </div>
                                    </div>

                                    <div class="card-actions">
                                        <form action="../../controllers/alumno/enrollCourse.php" method="POST" class="enroll-form">
                                            <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                            <button type="submit" class="btn-enroll">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                                <span>Enroll in Course</span>
                                            </button>
                                        </form>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon-wrap">
                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                            <h3>You are enrolled in all available courses</h3>
                            <p>There are no additional programs available for enrollment at this time. You can continue progressing in your active courses.</p>
                            <a href="misCursos.php" class="btn-primary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                                <span>Back to My Courses</span>
                            </a>
                        </div>
                    <?php endif; ?>
                </section>

            </div>
        </main>
    </div>

    <script>
        function filterCourses() {
            const searchVal = document.getElementById('searchInput').value.toLowerCase().trim();
            const deptVal = document.getElementById('deptSelect').value;
            const cards = document.querySelectorAll('.courses-grid .course-card');
            let visibleCount = 0;

            cards.forEach(card => {
                const title = card.getAttribute('data-title') || '';
                const dept = card.getAttribute('data-dept') || '';

                const matchesSearch = title.includes(searchVal);
                const matchesDept = !deptVal || dept === deptVal;

                if (matchesSearch && matchesDept) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            const countEl = document.getElementById('courseCount');
            if (countEl) countEl.textContent = visibleCount;
        }
    </script>
</body>
</html>
