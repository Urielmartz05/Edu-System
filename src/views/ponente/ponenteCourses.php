<?php
    require_once __DIR__ . "/../../controllers/auth/check_auth.php";
    requireRole([1, 3]);
    require_once __DIR__ . "/../../models/teacherModels/teacherCoursesModel.php";
    $user = getLoggedUser();

    $teacherId = (int)$user['id'];
    $coursesModel = new TeacherCourses();

    $assignedCourses = $coursesModel->getCoursesWithDetails($teacherId);

    // Pure PHP filtering
    $search = trim($_GET['search'] ?? '');
    $statusFilter = trim($_GET['status'] ?? '');

    if (!empty($search)) {
        $assignedCourses = array_filter($assignedCourses, function($c) use ($search) {
            return stripos($c['course_name'] ?? '', $search) !== false
                || stripos($c['department_name'] ?? '', $search) !== false;
        });
    }

    if (!empty($statusFilter)) {
        $assignedCourses = array_filter($assignedCourses, function($c) use ($statusFilter) {
            $isPublished = ((int)$c['status'] === 1);
            $statusText = $isPublished ? 'Active' : 'In Review';
            return strtolower($statusText) === strtolower($statusFilter);
        });
    }

    $allTeacherCourses = $coursesModel->getCoursesWithDetails($teacherId);
    $flashSuccess = $_SESSION['flash_success'] ?? null;
    $flashError = $_SESSION['flash_error'] ?? null;
    unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    $showAddBlockModal = !empty($_GET['add_block']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses · EduX</title>
    <link rel="stylesheet" href="ponenteCourses.css">
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

                <!-- Flash Messages -->
                <?php if ($flashSuccess): ?>
                    <div style="background-color: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 14px 20px; border-radius: 12px; margin-bottom: 24px; font-size: 13px; font-weight: 500; display:flex; align-items:center; gap:10px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <span><?php echo htmlspecialchars($flashSuccess); ?></span>
                    </div>
                <?php elseif ($flashError): ?>
                    <div style="background-color: #fef2f2; border: 1px solid #fecdd3; color: #b91c1c; padding: 14px 20px; border-radius: 12px; margin-bottom: 24px; font-size: 13px; font-weight: 500; display:flex; align-items:center; gap:10px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span><?php echo htmlspecialchars($flashError); ?></span>
                    </div>
                <?php endif; ?>

                <!-- Main Courses Panel -->
                <section class="panel">
                    <div class="panel-head">
                        <div>
                            <h2>Assigned Curricular Programs</h2>
                            <p>Complete directory of courses and groups for the current academic period.</p>
                        </div>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <span class="badge-tag"><?php echo count($assignedCourses); ?> Course(s)</span>
                            <a href="ponenteCourses.php?add_block=1" class="btn-primary" style="padding: 8px 16px; border-radius: 8px; text-decoration:none; background:#8B1528; color:#fff; font-weight:700; font-size:13px; display:inline-flex; align-items:center; gap:6px; box-shadow: 0 2px 4px rgba(139,21,40,0.25);">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                <span>Add Block</span>
                            </a>
                        </div>
                    </div>

                    <form method="GET" action="ponenteCourses.php" class="filters-bar">
                        <input type="search" name="search" class="search-input" id="courseSearch" placeholder="Search by course name or department..." value="<?php echo htmlspecialchars($search); ?>">
                        <select name="status" class="filter-select" id="statusFilter">
                            <option value="">All statuses</option>
                            <option value="Active" <?php echo ($statusFilter === 'Active') ? 'selected' : ''; ?>>Active</option>
                            <option value="In Review" <?php echo ($statusFilter === 'In Review') ? 'selected' : ''; ?>>In Review</option>
                        </select>
                        <button type="submit" class="btn-primary" style="padding: 10px 18px; border-radius: 8px; border:none; background:#881337; color:#fff; font-weight:600; cursor:pointer;">Filter</button>
                        <?php if (!empty($search) || !empty($statusFilter)): ?>
                            <a href="ponenteCourses.php" style="padding: 10px 16px; background:#f1f5f9; border-radius:8px; text-decoration:none; color:#475569; font-weight:600; font-size:13px;">Reset</a>
                        <?php endif; ?>
                    </form>

                    <div class="courses-grid" id="coursesGrid">
                        <?php if (!empty($assignedCourses)): ?>
                            <?php foreach ($assignedCourses as $c):
                                $prog = (float)($c['progress'] ?? 0);
                                $isPublished = ((int)$c['status'] === 1);
                                $statusText = $isPublished ? 'Active' : 'In Review';
                                $statusClass = $isPublished ? 'active' : 'pending';
                            ?>
                                <a href="ponenteCourseContent.php?course_id=<?php echo (int)$c['id']; ?>" class="course-card" style="text-decoration:none; color:inherit; display:flex;">
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
                                </a>
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

    <!-- Create Block Modal (Pure PHP query parameter ?add_block=1) -->
    <?php if ($showAddBlockModal): ?>
        <div style="position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(15,23,42,0.5); display:flex; align-items:center; justify-content:center; z-index:1000; padding:20px;">
            <div style="background:#fff; border-radius:14px; width:100%; max-width:480px; padding:24px 28px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);">
                <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:16px; padding-bottom:14px; border-bottom:1px solid #f1f5f9;">
                    <div>
                        <h3 style="font-size:16px; font-weight:700; color:#0f172a; margin:0;">Add Syllabus Block</h3>
                        <p style="font-size:12px; color:#64748b; margin:4px 0 0 0;">Create a new instructional block for one of your assigned courses.</p>
                    </div>
                    <a href="ponenteCourses.php" style="background:none; border:none; font-size:22px; line-height:1; color:#94a3b8; text-decoration:none; cursor:pointer;">&times;</a>
                </div>
                <form action="../../controllers/admin/manageBlock.php" method="POST" style="margin-top:18px;"><?php echo csrfField(); ?>
                    <input type="hidden" name="action" value="create_block">
                    <input type="hidden" name="return_url" value="ponenteCourses.php">

                    <div style="margin-bottom:16px;">
                        <label for="course_select" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:6px;">Target Course *</label>
                        <select id="course_select" name="course_id" style="width:100%; height:40px; padding:0 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; color:#1e293b; background:#fff;" required>
                            <option value="">Select course...</option>
                            <?php foreach ($allTeacherCourses as $tc): ?>
                                <option value="<?php echo (int)$tc['id']; ?>">
                                    <?php echo htmlspecialchars($tc['course_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="margin-bottom:20px;">
                        <label for="block_name" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:6px;">Block / Unit Title *</label>
                        <input type="text" id="block_name" name="block_name" placeholder="e.g. Unit 3: Advanced Architecture & Testing" maxlength="60" style="width:100%; height:40px; padding:0 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; color:#1e293b; box-sizing:border-box;" required>
                        <span style="font-size:11.5px; color:#64748b; margin-top:4px; display:block;">You will be designated as the instructor for this block.</span>
                    </div>

                    <div style="display:flex; justify-content:flex-end; gap:10px;">
                        <a href="ponenteCourses.php" style="padding:8px 16px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; color:#475569; text-decoration:none; background:#fff; font-weight:600;">Cancel</a>
                        <button type="submit" style="padding:8px 18px; background:#8B1528; color:#fff; border:none; border-radius:8px; font-size:13px; font-weight:700; cursor:pointer;">Create Block</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>
