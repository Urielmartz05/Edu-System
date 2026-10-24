<?php
    require_once __DIR__ . '/../../controllers/auth/check_auth.php';
    require_once __DIR__ . '/../../models/adminModels/adminCoursesModel.php';
    $user = getLoggedUser();

    $adminCourses = new AdminCourses();
    $totalCourses = $adminCourses->getTotalCoursesCount();
    $publishedCount = $adminCourses->getPublishedCoursesCount();
    $reviewCount = $adminCourses->getReviewCoursesCount();
    $enrollmentsCount = $adminCourses->getTotalEnrollmentsCount();
    $coursesList = $adminCourses->getAllCourses();
    $departmentsList = $adminCourses->getDepartmentsList();
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
        <link rel="stylesheet" href="courses.css">
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
                        <div class="actions">
                            <a href="adminCreate.php?tab=course" class="btn btn-primary">
                                <svg style="width:15px; height:15px; stroke:currentColor; stroke-width:2.2;" viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                <span>Create Course</span>
                            </a>
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
                        </div>
                        <div class="filters">
                            <label class="search">
                                <span class="filter-label">Search by course, instructor or department</span>
                                <input class="field" id="search" type="search" placeholder="Search by course, instructor or code…" onkeyup="filterCourses()">
                            </label>
                            <label>
                                <span class="filter-label">Status</span>
                                <select id="statusFilter" onchange="filterCourses()">
                                    <option value="">All statuses</option>
                                    <option value="Published">Published</option>
                                    <option value="Under Review">Under Review</option>
                                </select>
                            </label>
                        </div>
                        <div class="table-wrap">
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
                                            <tr data-status="<?php echo $statusText; ?>" tabindex="0" onclick="window.location.href='adminCourseContent.php?course_id=<?php echo urlencode($c['id']); ?>'" onkeydown="if(event.key==='Enter') window.location.href='adminCourseContent.php?course_id=<?php echo urlencode($c['id']); ?>'" title="Click to inspect <?php echo htmlspecialchars($c['course_name']); ?>">
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
                                                        <a href="adminCourseContent.php?course_id=<?php echo urlencode($c['id']); ?>" class="btn-table-action" onclick="event.stopPropagation();" title="View Course Content & Modules">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                                            <span>Inspect</span>
                                                        </a>
                                                        <button type="button" class="btn-table-action" onclick="event.stopPropagation(); openEditCourseModal(<?php echo htmlspecialchars(json_encode($c)); ?>)" title="Edit Course Specifications">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                            <span>Edit</span>
                                                        </button>
                                                        <a href="../../controllers/admin/toggleCourseStatus.php?course_id=<?php echo urlencode($c['id']); ?>" class="btn-table-action" onclick="event.stopPropagation();" title="<?php echo $isPublished ? 'Change to Under Review' : 'Publish Course'; ?>">
                                                            <?php if ($isPublished): ?>
                                                                <span style="color:#d97706; font-size:11px;">Pause</span>
                                                            <?php else: ?>
                                                                <span style="color:#16a34a; font-size:11px;">Publish</span>
                                                            <?php endif; ?>
                                                        </a>
                                                        <button type="button" class="btn-table-action" onclick="event.stopPropagation(); confirmDeleteCourse(<?php echo (int)$c['id']; ?>, '<?php echo htmlspecialchars(addslashes($c['course_name'])); ?>')" style="color:#dc2626;" title="Delete Course">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                            <span>Delete</span>
                                                        </button>
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

        <!-- Edit Course In-Place Modal -->
        <div id="editCourseModal" class="modal-overlay" style="display:none;">
            <div class="modal-card">
                <div class="modal-header">
                    <div>
                        <h3 style="font-size:16px; font-weight:700; color:#0f172a;">Edit Course Specifications</h3>
                        <p style="font-size:12px; color:#64748b; margin-top:2px;">Update course title, department, credits, or publication status.</p>
                    </div>
                    <button type="button" class="btn-modal-close" onclick="closeEditCourseModal()">&times;</button>
                </div>
                <form action="../../controllers/admin/manageCourseActions.php" method="POST" style="margin-top:18px;">
                    <input type="hidden" name="action" value="update_course">
                    <input type="hidden" name="course_id" id="edit_course_id">

                    <div style="margin-bottom:14px;">
                        <label for="edit_course_name" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:6px;">Course Title *</label>
                        <input type="text" id="edit_course_name" name="course_name" class="modal-field" maxlength="60" required>
                    </div>

                    <div style="margin-bottom:14px;">
                        <label for="edit_department_id" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:6px;">Academic Department *</label>
                        <select id="edit_department_id" name="department_id" class="modal-field" required>
                            <?php foreach ($departmentsList as $d): ?>
                                <option value="<?php echo (int)$d['id']; ?>">
                                    <?php echo htmlspecialchars($d['department']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:20px;">
                        <div>
                            <label for="edit_credits" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:6px;">Credits *</label>
                            <input type="number" id="edit_credits" name="credits" class="modal-field" min="1" max="60" required>
                        </div>
                        <div>
                            <label for="edit_status" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:6px;">Publication Status</label>
                            <select id="edit_status" name="status" class="modal-field">
                                <option value="1">Published</option>
                                <option value="0">Under Review</option>
                            </select>
                        </div>
                    </div>

                    <div style="display:flex; justify-content:flex-end; gap:10px;">
                        <button type="button" class="btn btn-secondary" onclick="closeEditCourseModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function filterCourses() {
                const query = document.getElementById('search').value.toLowerCase().trim();
                const status = document.getElementById('statusFilter').value;
                const rows = document.querySelectorAll('#coursesTable tbody tr');
                let visibleCount = 0;

                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    const rowStatus = row.getAttribute('data-status') || '';
                    const matchesQuery = text.includes(query);
                    const matchesStatus = !status || rowStatus === status;

                    if (matchesQuery && matchesStatus) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                document.getElementById('recordCount').textContent = `Showing ${visibleCount} course(s)`;
            }

            function openEditCourseModal(course) {
                document.getElementById('edit_course_id').value = course.id;
                document.getElementById('edit_course_name').value = course.course_name;
                document.getElementById('edit_department_id').value = course.department_id;
                document.getElementById('edit_credits').value = course.credits;
                document.getElementById('edit_status').value = course.status;
                document.getElementById('editCourseModal').style.display = 'flex';
            }

            function closeEditCourseModal() {
                document.getElementById('editCourseModal').style.display = 'none';
            }

            function confirmDeleteCourse(courseId, courseName) {
                if (confirm(`Are you sure you want to permanently delete course "${courseName}"?\n\nThis will also remove all associated modules, attached study files, and student enrollments.`)) {
                    window.location.href = `../../controllers/admin/manageCourseActions.php?action=delete_course&course_id=${encodeURIComponent(courseId)}`;
                }
            }

            window.addEventListener('click', function(e) {
                const modal = document.getElementById('editCourseModal');
                if (e.target === modal) {
                    closeEditCourseModal();
                }
            });

            window.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeEditCourseModal();
                }
            });
        </script>
    </body>
</html>
