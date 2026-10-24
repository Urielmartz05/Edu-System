<?php
    require_once __DIR__ . "/../../controllers/auth/check_auth.php";
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Director Portal · Edu-System</title>
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
                    <article class="kpi">
                        <h2 class="kpi-label">Enrolled Students</h2>
                        <strong class="kpi-value"><?php echo $studentsCount; ?></strong>
                        <span class="kpi-hint">Total division headcount</span>
                    </article>
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
                        <span class="badge-tag"><?php echo count($departmentCourses); ?> Program(s)</span>
                    </div>

                    <div class="filters-bar">
                        <input type="search" class="search-input" id="courseSearch" placeholder="Search division courses or instructors..." onkeyup="filterCourses()">
                    </div>

                    <div class="table-wrap">
                        <table id="coursesTable">
                            <thead>
                                <tr>
                                    <th style="width: 26%;">COURSE PROGRAM</th>
                                    <th style="width: 10%;">CREDITS</th>
                                    <th style="width: 10%;">MODULES</th>
                                    <th style="width: 20%;">ASSIGNED FACULTY</th>
                                    <th style="width: 10%;">STUDENTS</th>
                                    <th style="width: 14%;">SYLLABUS PROGRESS</th>
                                    <th style="width: 10%; text-align: right;">STATUS</th>
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
                                            <td style="text-align: right;">
                                                <span class="badge <?php echo $isPublished ? 'active' : 'pending'; ?>">
                                                    <?php echo $isPublished ? 'Active' : 'In Review'; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" style="text-align:center; padding:24px; color:#64748b;">No courses assigned to this department yet.</td>
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
                        <input type="search" class="search-input" id="facultySearch" placeholder="Search faculty by name or email..." onkeyup="filterFaculty()">
                    </div>

                    <div class="table-wrap">
                        <table id="facultyTable">
                            <thead>
                                <tr>
                                    <th style="width: 25%;">INSTRUCTOR</th>
                                    <th style="width: 25%;">INSTITUTIONAL EMAIL</th>
                                    <th style="width: 35%;">ASSIGNED SUBJECT MODULES</th>
                                    <th style="width: 15%; text-align: right;">TOTAL MODULES</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($departmentFaculty)): ?>
                                    <?php foreach ($departmentFaculty as $f): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($f['teacher_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($f['email']); ?></td>
                                            <td><span class="faculty-tag"><?php echo htmlspecialchars($f['block_names']); ?></span></td>
                                            <td style="text-align: right; font-weight: 600;"><?php echo (int)$f['assigned_blocks_count']; ?> module(s)</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" style="text-align:center; padding:24px; color:#64748b;">No faculty members assigned to division modules yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script>
        function filterCourses() {
            const query = document.getElementById('courseSearch').value.toLowerCase().trim();
            const rows = document.querySelectorAll('#coursesTable tbody tr');
            rows.forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
            });
        }

        function filterFaculty() {
            const query = document.getElementById('facultySearch').value.toLowerCase().trim();
            const rows = document.querySelectorAll('#facultyTable tbody tr');
            rows.forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
            });
        }
    </script>
</body>
</html>
