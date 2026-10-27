<?php
    require_once __DIR__ . '/../../controllers/auth/check_auth.php';
    requireRole([1]);
    require_once __DIR__ . '/../../models/adminModels/adminCoursesModel.php';

    $adminCourses = new AdminCourses();
    $totalCourses = $adminCourses->getTotalCoursesCount();
    $publishedCount = $adminCourses->getPublishedCoursesCount();
    $reviewCount = $adminCourses->getReviewCoursesCount();
    $enrollmentsCount = $adminCourses->getTotalEnrollmentsCount();
    $coursesList = $adminCourses->getAllCourses();
    $departmentsList = $adminCourses->getDepartmentsList();

    // Pure PHP search and status filtering
    $search = trim($_GET['search'] ?? '');
    $statusFilter = trim($_GET['status'] ?? '');

    if (!empty($search)) {
        $coursesList = array_filter($coursesList, function($c) use ($search) {
            return stripos($c['course_name'] ?? '', $search) !== false
                || stripos($c['department_name'] ?? '', $search) !== false
                || stripos($c['teacher_name'] ?? '', $search) !== false;
        });
    }

    if (!empty($statusFilter)) {
        $coursesList = array_filter($coursesList, function($c) use ($statusFilter) {
            $isPublished = ((int)$c['status'] === 1);
            $statusText = $isPublished ? 'Published' : 'Under Review';
            return strtolower($statusText) === strtolower($statusFilter);
        });
    }

    // Pure PHP Edit Course resolution
    $editCourseId = (int)($_GET['edit_course_id'] ?? 0);
    $courseToEdit = null;
    if ($editCourseId > 0) {
        $courseToEdit = $adminCourses->getCourseDetails($editCourseId);
    }

    // Pure PHP Delete Confirmation
    $confirmDeleteId = (int)($_GET['confirm_delete_id'] ?? 0);
    $courseToDelete = null;
    if ($confirmDeleteId > 0) {
        $courseToDelete = $adminCourses->getCourseDetails($confirmDeleteId);
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
        <title>Courses · Edu-System</title>
        <link rel="stylesheet" href="adminCourses.css">
    </head>
    <body>
        <?php include __DIR__ . '/../../../ui/header/header.php'; ?>
        <?php include __DIR__ . '/../../../ui/adminSideBar/adminSideBar.php'; ?>

        <div class="workspace">
            <main>
                <div id="page-content">
                    <!-- Heading -->
                    <section class="page-heading">
                        <div>
                            <p class="eyebrow">ACADEMIC MANAGEMENT</p>
                            <h1>Course Catalog</h1>
                            <p class="subtitle">Organize academic offerings, manage departments, and monitor course enrollments.</p>
                        </div>
                    </section>

                    <?php if ($flashSuccess): ?>
                        <div style="background-color: #f0fdf4; border: 1px solid #86efac; color: #15803d; padding: 14px 20px; border-radius: 10px; margin-bottom: 24px; font-size: 13px; font-weight: 500;">
                            <strong>Success:</strong> <?php echo htmlspecialchars($flashSuccess); ?>
                        </div>
                    <?php elseif ($flashError): ?>
                        <div style="background-color: #fef2f2; border: 1px solid #fecdd3; color: #b91c1c; padding: 14px 20px; border-radius: 10px; margin-bottom: 24px; font-size: 13px; font-weight: 500;">
                            <strong>Error:</strong> <?php echo htmlspecialchars($flashError); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($courseToDelete): ?>
                        <div style="background-color: #fff1f2; border: 1px solid #fecdd3; color: #9f1239; padding: 18px 24px; border-radius: 10px; margin-bottom: 24px; font-size: 13px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                            <div>
                                <strong>Delete Confirmation:</strong> Are you sure you want to permanently delete course "<strong><?php echo htmlspecialchars($courseToDelete['course_name']); ?></strong>"? All syllabus modules and student enrollments will also be removed.
                            </div>
                            <div style="display:flex; gap:10px;">
                                <a href="adminCourses.php" class="btn btn-secondary" style="padding:6px 14px; text-decoration:none;">Cancel</a>
                                <form method="POST" action="../../controllers/admin/manageCourseActions.php" style="display:inline;">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="action" value="delete_course">
                                    <input type="hidden" name="course_id" value="<?php echo (int)$courseToDelete['id']; ?>">
                                    <button type="submit" class="btn btn-primary" style="background:#dc2626; border-color:#dc2626; padding:6px 14px; text-decoration:none;">Confirm Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- KPI Grid -->
                    <section class="kpi-grid">
                        <article class="kpi">
                            <h2 class="kpi-label">Courses in Catalog</h2>
                            <strong class="kpi-value"><?php echo $totalCourses; ?></strong>
                        </article>
                        <article class="kpi">
                            <h2 class="kpi-label">Published</h2>
                            <strong class="kpi-value"><?php echo $publishedCount; ?></strong>
                        </article>
                        <article class="kpi">
                            <h2 class="kpi-label">Under Review</h2>
                            <strong class="kpi-value"><?php echo $reviewCount; ?></strong>
                        </article>
                        <article class="kpi">
                            <h2 class="kpi-label">Total Enrollments</h2>
                            <strong class="kpi-value"><?php echo $enrollmentsCount; ?></strong>
                        </article>
                    </section>

                    <!-- Main Panel -->
                    <section class="panel">
                        <div class="panel-head">
                            <div>
                                <h2>Academic Offerings</h2>
                                <p>Review enrollment capacity, assigned instructors, and publication status.</p>
                            </div>
                            <div style="display:flex; gap:10px; align-items:center;">
                                <a href="adminCreate.php?tab=section" class="btn btn-secondary" style="text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                                    <svg style="width:15px; height:15px; stroke:currentColor; stroke-width:2;" viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="3" width="7" height="7"></rect>
                                        <rect x="14" y="3" width="7" height="7"></rect>
                                        <rect x="14" y="14" width="7" height="7"></rect>
                                        <rect x="3" y="14" width="7" height="7"></rect>
                                    </svg>
                                    <span>Manage Sections</span>
                                </a>
                                <a href="adminCreate.php?tab=course" class="btn btn-primary" style="text-decoration:none;">
                                    <svg style="width:15px; height:15px; stroke:currentColor; stroke-width:2.2;" viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                    <span>Create Course</span>
                                </a>
                            </div>
                        </div>

                        <!-- Pure PHP Filter Form -->
                        <form method="GET" action="adminCourses.php" class="filters">
                            <label class="search">
                                <span class="filter-label">Search by course, instructor or department</span>
                                <input class="field" name="search" id="search" type="search" placeholder="Search by course, instructor or code…" value="<?php echo htmlspecialchars($search); ?>">
                            </label>
                            <label>
                                <span class="filter-label">Status</span>
                                <select name="status" id="statusFilter">
                                    <option value="">All statuses</option>
                                    <option value="Published" <?php echo ($statusFilter === 'Published') ? 'selected' : ''; ?>>Published</option>
                                    <option value="Under Review" <?php echo ($statusFilter === 'Under Review') ? 'selected' : ''; ?>>Under Review</option>
                                </select>
                            </label>
                            <button type="submit" class="btn btn-primary" style="padding:10px 18px; align-self:flex-end;">Filter</button>
                            <?php if (!empty($search) || !empty($statusFilter)): ?>
                                <a href="adminCourses.php" class="btn btn-secondary" style="padding:10px 16px; align-self:flex-end; text-decoration:none;">Reset</a>
                            <?php endif; ?>
                        </form>

                        <div class="table-wrap" role="region" aria-label="Course catalog" tabindex="0">
                            <table id="coursesTable">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 32%;" class="col-course">COURSE</th>
                                        <th scope="col" style="width: 18%;" class="col-dept">DEPARTMENT</th>
                                        <th scope="col" style="width: 18%;" class="col-teacher">INSTRUCTOR</th>
                                        <th scope="col" style="width: 10%;" class="col-students">ENROLLED</th>
                                        <th scope="col" style="width: 10%;" class="col-status">STATUS</th>
                                        <th scope="col" style="width: 12%; text-align: right;" class="col-actions">ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($coursesList)): ?>
                                        <?php foreach ($coursesList as $c):
                                            $isPublished = ((int)$c['status'] === 1);
                                            $statusText = $isPublished ? 'Published' : 'Under Review';
                                            $statusClass = $isPublished ? 'active' : 'pending';
                                        ?>
                                            <tr>
                                                <td class="col-course">
                                                    <strong><?php echo htmlspecialchars($c['course_name']); ?></strong>
                                                    <span style="display:block; font-size:12px; color:#64748b;"><?php echo (int)($c['credits'] ?? 0); ?> Academic Credits</span>
                                                </td>
                                                <td class="col-dept"><?php echo htmlspecialchars($c['department_name'] ?? 'General'); ?></td>
                                                <td class="col-teacher"><?php echo htmlspecialchars($c['teacher_name'] ?? 'Unassigned'); ?></td>
                                                <td class="col-students"><?php echo (int)$c['student_count']; ?> students</td>
                                                <td class="col-status">
                                                    <span class="badge <?php echo $statusClass; ?>">
                                                        <?php echo $statusText; ?>
                                                    </span>
                                                </td>
                                                <td class="col-actions" style="text-align: right;">
                                                    <div style="display:inline-flex; align-items:center; gap:6px;">
                                                        <a href="adminCourseContent.php?course_id=<?php echo urlencode($c['id']); ?>" class="btn-table-action" title="View Course Content & Modules">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                                            <span>Inspect</span>
                                                        </a>
                                                        <a href="adminCourses.php?edit_course_id=<?php echo (int)$c['id']; ?>" class="btn-table-action" title="Edit Course Specifications">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                            <span>Edit</span>
                                                        </a>
                                                        <form method="POST" action="../../controllers/admin/toggleCourseStatus.php?course_id=<?php echo urlencode($c['id']); ?>" style="display:inline;"><?php echo csrfField(); ?><button type="submit"  class="btn-table-action" title="<?php echo $isPublished ? 'Change to Under Review' : 'Publish Course'; ?>">
                                                            <?php if ($isPublished): ?>
                                                                <span style="color:#d97706; font-size:11px;">Pause</span>
                                                            <?php else: ?>
                                                                <span style="color:#16a34a; font-size:11px;">Publish</span>
                                                            <?php endif; ?>
                                                        </button></form>
                                                        <a href="adminCourses.php?confirm_delete_id=<?php echo (int)$c['id']; ?>" class="btn-table-action" style="color:#dc2626;" title="Delete Course">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                            <span>Delete</span>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" style="text-align:center; padding:24px; color:#64748b;">No courses found in catalog.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="table-footer">
                            <span id="recordCount">Showing <?php echo count($coursesList); ?> course(s)</span>
                        </div>
                    </section>
                </div>
            </main>
        </div>

        <!-- Edit Course In-Place Modal (Pure PHP) -->
        <?php if ($courseToEdit): ?>
            <div id="editCourseModal" class="modal-overlay" style="display:flex;">
                <div class="modal-card">
                    <div class="modal-header">
                        <div>
                            <h3 style="font-size:16px; font-weight:700; color:#0f172a;">Edit Course Specifications</h3>
                            <p style="font-size:12px; color:#64748b; margin-top:2px;">Update course title, department, credits, or publication status.</p>
                        </div>
                        <a href="adminCourses.php" class="btn-modal-close" style="text-decoration:none;">&times;</a>
                    </div>
                    <form action="../../controllers/admin/manageCourseActions.php" method="POST" style="margin-top:18px;"><?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="update_course">
                        <input type="hidden" name="course_id" value="<?php echo (int)$courseToEdit['id']; ?>">

                        <div style="margin-bottom:14px;">
                            <label for="edit_course_name" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:6px;">Course Title *</label>
                            <input type="text" id="edit_course_name" name="course_name" class="modal-field" maxlength="60" value="<?php echo htmlspecialchars($courseToEdit['course_name']); ?>" required>
                        </div>

                        <div style="margin-bottom:14px;">
                            <label for="edit_department_id" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:6px;">Academic Department *</label>
                            <select id="edit_department_id" name="department_id" class="modal-field" required>
                                <?php foreach ($departmentsList as $d): ?>
                                    <option value="<?php echo (int)$d['id']; ?>" <?php echo ((int)$courseToEdit['department_id'] === (int)$d['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($d['department']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:20px;">
                            <div>
                                <label for="edit_credits" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:6px;">Credits *</label>
                                <input type="number" id="edit_credits" name="credits" class="modal-field" min="1" max="60" value="<?php echo (int)$courseToEdit['credits']; ?>" required>
                            </div>
                            <div>
                                <label for="edit_status" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:6px;">Publication Status</label>
                                <select id="edit_status" name="status" class="modal-field">
                                    <option value="1" <?php echo ((int)$courseToEdit['status'] === 1) ? 'selected' : ''; ?>>Published</option>
                                    <option value="0" <?php echo ((int)$courseToEdit['status'] === 0) ? 'selected' : ''; ?>>Under Review</option>
                                </select>
                            </div>
                        </div>

                        <div style="display:flex; justify-content:flex-end; gap:10px;">
                            <a href="adminCourses.php" class="btn btn-secondary" style="text-decoration:none;">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </body>
</html>
