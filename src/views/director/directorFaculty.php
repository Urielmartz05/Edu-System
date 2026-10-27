<?php
    require_once __DIR__ . "/../../controllers/auth/check_auth.php";
    requireRole([1, 2]);
    require_once __DIR__ . "/../../models/directorModels/directorDashboardModel.php";
    $user = getLoggedUser();

    $directorModel = new DirectorDashboard();
    $dept = $directorModel->getDirectorDepartment($user['id']);
    $deptId = (int)$dept['department_id'];

    $facultyCount = $directorModel->getFacultyCount($deptId);
    $coursesCount = $directorModel->getCoursesCount($deptId);
    $studentsCount = $directorModel->getStudentsCount($deptId);
    $avgProgress = $directorModel->getAverageProgress($deptId);

    $departmentFaculty = $directorModel->getDepartmentFaculty($deptId);

    // Search filtering
    $search = trim($_GET['search'] ?? '');
    if (!empty($search)) {
        $departmentFaculty = array_filter($departmentFaculty, function($f) use ($search) {
            return stripos($f['teacher_name'] ?? '', $search) !== false
                || stripos($f['email'] ?? '', $search) !== false
                || stripos($f['block_names'] ?? '', $search) !== false;
        });
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty & Teaching Staff · EduX</title>
    <link rel="stylesheet" href="directorFaculty.css">
</head>
<body>
    <?php include_once __DIR__ . "/../../../ui/header/header.php"; ?>
    <?php include_once __DIR__ . "/../../../components/directorSidebar/directorSidebar.php"; ?>

    <div class="workspace">
        <main>
            <div id="page-content">
                <!-- Page Heading -->
                <section class="page-heading">
                    <div>
                        <span class="eyebrow">DEPARTMENT FACULTY ROSTER · <?php echo htmlspecialchars(strtoupper($dept['department_name'])); ?></span>
                        <h1>Faculty & Teaching Staff</h1>
                        <p class="subtitle">Directory of professors, assigned subject modules, instructional workloads, and coursework auditing within your academic division.</p>
                    </div>
                </section>

                <!-- KPI Grid -->
                <section class="kpi-grid">
                    <article class="kpi">
                        <span class="kpi-label">Active Faculty</span>
                        <strong class="kpi-value"><?php echo $facultyCount; ?></strong>
                        <span class="kpi-hint">Instructors in division</span>
                    </article>

                    <article class="kpi">
                        <span class="kpi-label">Division Programs</span>
                        <strong class="kpi-value"><?php echo $coursesCount; ?></strong>
                        <span class="kpi-hint">Curricular offerings</span>
                    </article>

                    <article class="kpi">
                        <span class="kpi-label">Enrolled Students</span>
                        <strong class="kpi-value"><?php echo $studentsCount; ?></strong>
                        <span class="kpi-hint">Learners taught</span>
                    </article>

                    <article class="kpi">
                        <span class="kpi-label">Average Progress</span>
                        <strong class="kpi-value"><?php echo $avgProgress; ?>%</strong>
                        <span class="kpi-hint">Syllabus completion</span>
                    </article>
                </section>

                <!-- Main Faculty Panel -->
                <section class="panel">
                    <div class="panel-head">
                        <div>
                            <h2>Faculty Staff & Module Allocation</h2>
                            <p>Instructors currently teaching syllabus units in <?php echo htmlspecialchars($dept['department_name']); ?>.</p>
                        </div>
                        <span class="badge-tag"><?php echo count($departmentFaculty); ?> Faculty Member(s)</span>
                    </div>

                    <div class="filters-bar">
                        <form method="GET" action="directorFaculty.php" class="filter-form">
                            <input type="search" name="search" class="search-input" placeholder="Search faculty by name, email, or subject module..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn-filter">Search</button>
                            <?php if (!empty($search)): ?>
                                <a href="directorFaculty.php" class="btn-clear">Reset</a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <div class="table-wrap">
                        <table>
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
                                            <td><strong style="color: #0f172a; font-size: 14px;"><?php echo htmlspecialchars($f['teacher_name']); ?></strong></td>
                                            <td><span style="color: #475569;"><?php echo htmlspecialchars($f['email']); ?></span></td>
                                            <td><span class="faculty-tag"><?php echo htmlspecialchars($f['block_names']); ?></span></td>
                                            <td><strong><?php echo (int)$f['assigned_blocks_count']; ?></strong> module(s)</td>
                                            <td style="text-align: right;">
                                                <div style="display:inline-flex; align-items:center; gap:6px;">
                                                    <a href="directorCourses.php?assign_teacher_id=<?php echo (int)$f['id']; ?>" class="btn-action" style="background:#8B1528; color:#ffffff; text-decoration:none;" title="Assign this instructor to a course program">Assign Course</a>
                                                    <a href="directorAssignments.php?search=<?php echo urlencode($f['teacher_name']); ?>" class="btn-action btn-audit" title="Audit coursework assignments created by instructor">Audit Work</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" style="text-align:center; padding:32px; color:#64748b;">No faculty members found matching your search.</td>
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
