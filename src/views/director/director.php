<?php
    require_once __DIR__ . "/../../controllers/auth/check_auth.php";
    requireRole([1, 2]);
    require_once __DIR__ . "/../../models/directorModels/directorDashboardModel.php";
    $user = getLoggedUser();

    $directorModel = new DirectorDashboard();
    $dept = $directorModel->getDirectorDepartment($user['id']);
    $deptId = (int)$dept['department_id'];

    $coursesCount = $directorModel->getCoursesCount($deptId);
    $facultyCount = $directorModel->getFacultyCount($deptId);
    $studentsCount = $directorModel->getStudentsCount($deptId);
    $avgProgress = $directorModel->getAverageProgress($deptId);

    $departmentCourses = $directorModel->getDepartmentCourses($deptId);
    $departmentFaculty = $directorModel->getDepartmentFaculty($deptId);

    // Pure PHP filtering
    $courseSearch = trim($_GET['course_search'] ?? '');
    if (!empty($courseSearch)) {
        $departmentCourses = array_filter($departmentCourses, function($c) use ($courseSearch) {
            return stripos($c['course_name'] ?? '', $courseSearch) !== false
                || stripos($c['faculty_names'] ?? '', $courseSearch) !== false;
        });
    }

    $facultySearch = trim($_GET['faculty_search'] ?? '');
    if (!empty($facultySearch)) {
        $departmentFaculty = array_filter($departmentFaculty, function($f) use ($facultySearch) {
            return stripos($f['teacher_name'] ?? '', $facultySearch) !== false
                || stripos($f['email'] ?? '', $facultySearch) !== false
                || stripos($f['block_names'] ?? '', $facultySearch) !== false;
        });
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Director Portal · EduX</title>
    <link rel="stylesheet" href="director.css">
</head>
<body>
    <?php include_once __DIR__ . "/../../../ui/header/header.php"; ?>
    <?php include_once __DIR__ . "/../../../components/directorSidebar/directorSidebar.php"; ?>

    <div class="workspace">
        <main>
            <div id="page-content">
                <!-- Hero Heading -->
                <section class="page-heading">
                    <div>
                        <span class="eyebrow">ACADEMIC DIVISION · <?php echo htmlspecialchars($dept['department_name']); ?></span>
                        <h1>Department Leadership Portal</h1>
                        <p class="subtitle">Welcome, Director <?php echo htmlspecialchars($user['name'] . ' ' . $user['lastname']); ?>. Supervise academic programs, track division faculty, and monitor student enrollment.</p>
                    </div>
                </section>

                <!-- KPI Grid -->
                <section class="kpi-grid">
                    <article class="kpi">
                        <h2 class="kpi-label">Division Courses</h2>
                        <strong class="kpi-value"><?php echo $coursesCount; ?></strong>
                        <span class="kpi-hint">Curricular programs</span>
                    </article>
                    <article class="kpi">
                        <h2 class="kpi-label">Active Faculty</h2>
                        <strong class="kpi-value"><?php echo $facultyCount; ?></strong>
                        <span class="kpi-hint">Assigned instructors</span>
                    </article>
                    <a href="directorGrades.php?tab=students" class="kpi" style="text-decoration:none; color:inherit; display:block;" title="View Enrolled Students Directory">
                        <h2 class="kpi-label">Enrolled Students</h2>
                        <strong class="kpi-value"><?php echo $studentsCount; ?></strong>
                        <span class="kpi-hint">View student directory →</span>
                    </a>
                    <article class="kpi">
                        <h2 class="kpi-label">Average Progress</h2>
                        <strong class="kpi-value"><?php echo $avgProgress; ?>%</strong>
                        <span class="kpi-hint">Syllabus completion rate</span>
                    </article>
                </section>

                <!-- Courses Section -->
                <section class="panel" id="coursesSection">
                    <div class="panel-head">
                        <div>
                            <h2>Division Courses & Offerings</h2>
                            <p>All degree and certification programs under the Department of <?php echo htmlspecialchars($dept['department_name']); ?>.</p>
                        </div>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <a href="directorCourses.php" style="text-decoration:none; font-size:12px; font-weight:700; color:#8B1528; background:#fdf2f4; padding:6px 14px; border-radius:8px; border:1px solid #fecdd3;">Full Catalog &rarr;</a>
                            <span class="badge-tag"><?php echo count($departmentCourses); ?> Program(s)</span>
                        </div>
                    </div>

                    <div class="filters-bar">
                        <form method="GET" action="director.php" style="width:100%; max-width:420px; display:flex; gap:8px;">
                            <input type="search" name="course_search" class="search-input" id="courseSearch" placeholder="Search division courses or instructors..." value="<?php echo htmlspecialchars($courseSearch); ?>">
                            <?php if (!empty($facultySearch)): ?>
                                <input type="hidden" name="faculty_search" value="<?php echo htmlspecialchars($facultySearch); ?>">
                            <?php endif; ?>
                            <button type="submit" class="btn-primary" style="display:none;"></button>
                        </form>
                    </div>

                    <div class="table-wrap">
                        <table id="coursesTable">
                            <thead>
                                <tr>
                                    <th style="width: 22%;">COURSE PROGRAM</th>
                                    <th style="width: 8%;">CREDITS</th>
                                    <th style="width: 8%;">MODULES</th>
                                    <th style="width: 18%;">ASSIGNED FACULTY</th>
                                    <th style="width: 10%;">STUDENTS</th>
                                    <th style="width: 14%;">SYLLABUS PROGRESS</th>
                                    <th style="width: 8%;">STATUS</th>
                                    <th style="width: 12%; text-align: right;">ACADEMIC AUDIT</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($departmentCourses)): ?>
                                    <?php foreach ($departmentCourses as $c):
                                        $prog = (float)($c['progress'] ?? 0);
                                        $isPublished = ((int)$c['status'] === 1);
                                    ?>
                                        <tr>
                                            <td>
                                                <strong style="color: #0f172a;"><?php echo htmlspecialchars($c['course_name']); ?></strong>
                                            </td>
                                            <td><?php echo (int)($c['credits'] ?? 0); ?> Credits</td>
                                            <td><?php echo (int)$c['block_count']; ?> modules</td>
                                            <td><span class="faculty-tag"><?php echo htmlspecialchars($c['faculty_names']); ?></span></td>
                                            <td><?php echo (int)$c['student_count']; ?> enrolled</td>
                                            <td>
                                                <div style="display:flex; align-items:center; gap:8px;">
                                                    <span style="font-weight:600; min-width:38px; font-size:12px;"><?php echo $prog; ?>%</span>
                                                    <div style="flex:1; background:#e2e8f0; border-radius:6px; height:8px; overflow:hidden; min-width:45px;">
                                                        <div style="width:<?php echo min(100, max(0, $prog)); ?>%; background:#10b981; height:100%;"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $isPublished ? 'active' : 'pending'; ?>">
                                                    <?php echo $isPublished ? 'Active' : 'In Review'; ?>
                                                </span>
                                            </td>
                                            <td style="text-align: right;">
                                                <div style="display:inline-flex; align-items:center; gap:6px;">
                                                    <a href="directorCourseContent.php?course_id=<?php echo $c['id']; ?>" style="padding:4px 8px; background:#f1f5f9; color:#0f172a; font-size:11px; font-weight:700; text-decoration:none; border-radius:6px;" title="Inspect Syllabus & Study Materials">Material</a>
                                                    <a href="directorAssignments.php?course_id=<?php echo $c['id']; ?>" style="padding:4px 8px; background:#eff6ff; color:#1d4ed8; font-size:11px; font-weight:700; text-decoration:none; border-radius:6px;" title="Supervise Tasks">Tasks</a>
                                                    <a href="directorGrades.php?search=<?php echo urlencode($c['course_name']); ?>" style="padding:4px 8px; background:#ecfdf5; color:#065f46; font-size:11px; font-weight:700; text-decoration:none; border-radius:6px;" title="Audit Grades">Grades</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" style="text-align:center; padding:24px; color:#64748b;">No courses assigned to this department yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Faculty Section -->
                <section class="panel" id="facultySection">
                    <div class="panel-head">
                        <div>
                            <h2>Faculty & Teaching Staff</h2>
                            <p>Instructors currently teaching modules within this department.</p>
                        </div>
                        <span class="badge-tag"><?php echo count($departmentFaculty); ?> Faculty Member(s)</span>
                    </div>

                    <div class="filters-bar">
                        <form method="GET" action="director.php" style="width:100%; max-width:420px; display:flex; gap:8px;">
                            <input type="search" name="faculty_search" class="search-input" id="facultySearch" placeholder="Search faculty by name or email..." value="<?php echo htmlspecialchars($facultySearch); ?>">
                            <?php if (!empty($courseSearch)): ?>
                                <input type="hidden" name="course_search" value="<?php echo htmlspecialchars($courseSearch); ?>">
                            <?php endif; ?>
                            <button type="submit" class="btn-primary" style="display:none;"></button>
                        </form>
                    </div>

                    <div class="table-wrap">
                        <table id="facultyTable">
                            <thead>
                                <tr>
                                    <th style="width: 25%;">INSTRUCTOR</th>
                                    <th style="width: 25%;">INSTITUTIONAL EMAIL</th>
                                    <th style="width: 25%;">ASSIGNED SUBJECT MODULES</th>
                                    <th style="width: 12%;">TOTAL MODULES</th>
                                    <th style="width: 13%; text-align: right;">SUPERVISION</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($departmentFaculty)): ?>
                                    <?php foreach ($departmentFaculty as $f): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($f['teacher_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($f['email']); ?></td>
                                            <td><span class="faculty-tag"><?php echo htmlspecialchars($f['block_names']); ?></span></td>
                                            <td style="font-weight: 600;"><?php echo (int)$f['assigned_blocks_count']; ?> module(s)</td>
                                            <td style="text-align: right;">
                                                <a href="directorAssignments.php?search=<?php echo urlencode($f['teacher_name']); ?>" style="display:inline-block; padding:4px 10px; background:#f1f5f9; color:#0f172a; font-size:11.5px; font-weight:600; text-decoration:none; border-radius:6px; border:1px solid #cbd5e1;" title="Audit coursework assignments created by instructor">Audit Work</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" style="text-align:center; padding:24px; color:#64748b;">No faculty members assigned to division modules yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
