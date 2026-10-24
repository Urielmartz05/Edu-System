<?php
    require_once __DIR__ . "/../../controllers/auth/check_auth.php";
    $user = getLoggedUser();

    require_once __DIR__ . "/../../models/studentModels/studentCoursesModel.php";

    $studentCoursesModel = new StudentCourses();
    $totalBlocks = $studentCoursesModel->getBlocksCount();
    $overallProgress = $studentCoursesModel->getCourseProgress();
    $credits = $studentCoursesModel->getCourseCredits();
    $course = $studentCoursesModel->getCourse();
    $blocks = $studentCoursesModel->getBlocks();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Modules · Edu-System</title>
    <link rel="stylesheet" href="courses.css">
</head>
<body>
    <?php include __DIR__ . '/../../../ui/header/header.php'; ?>
    <?php include __DIR__ . '/../../../components/studentSidebar/studentSidebar.php'; ?>

    <div class="workspace">
        <main>
            <div id="page-content">
                <!-- Breadcrumbs -->
                <nav class="breadcrumb">
                    <a href="alumnoDashboard.php">Dashboard</a>
                    <span class="breadcrumb-separator">/</span>
                    <a href="misCursos.php">My Courses</a>
                    <span class="breadcrumb-separator">/</span>
                    <span>Syllabus & Modules</span>
                </nav>

                <!-- Heading -->
                <section class="page-heading">
                    <div>
                        <span class="eyebrow">ACADEMIC PROGRAM · <?php echo htmlspecialchars($course['course_name'] ?? 'Academic Program'); ?></span>
                        <h1>Syllabus & Course Modules</h1>
                        <p class="subtitle">Explore instructional blocks, curriculum topics, assigned faculty, and completion progress for your active course.</p>
                    </div>
                    <div>
                        <a href="misCursos.php" class="btn-back">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                            <span>Back to My Courses</span>
                        </a>
                    </div>
                </section>

                <!-- Quick KPI Metrics -->
                <section class="kpi-grid">
                    <article class="kpi">
                        <div class="kpi-header">
                            <h2 class="kpi-label">Total Modules</h2>
                            <div class="kpi-icon-wrap icon-maroon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value"><?php echo $totalBlocks; ?></strong>
                        <span class="kpi-hint">Modules in syllabus</span>
                    </article>
                    <article class="kpi">
                        <div class="kpi-header">
                            <h2 class="kpi-label">Cumulative Progress</h2>
                            <div class="kpi-icon-wrap icon-emerald">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value"><?php echo $overallProgress; ?>%</strong>
                        <div class="kpi-progress-bar">
                            <div class="kpi-progress-fill" style="width: <?php echo min(100, max(0, (float)$overallProgress)); ?>%;"></div>
                        </div>
                    </article>
                    <article class="kpi">
                        <div class="kpi-header">
                            <h2 class="kpi-label">Credits</h2>
                            <div class="kpi-icon-wrap icon-blue">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value"><?php echo $credits; ?></strong>
                        <span class="kpi-hint">Academic weight</span>
                    </article>
                    <article class="kpi">
                        <div class="kpi-header">
                            <h2 class="kpi-label">Course Status</h2>
                            <div class="kpi-icon-wrap icon-emerald">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value"><?php echo ((int)($course['status'] ?? 0) === 1) ? 'Active' : 'Inactive'; ?></strong>
                        <span class="kpi-hint">Enrolled status</span>
                    </article>
                </section>

                <!-- Main Modules Panel -->
                <section class="panel">
                    <div class="panel-head">
                        <div>
                            <h2>Curriculum Modules</h2>
                            <p>Structured list of learning blocks scheduled for this course program.</p>
                        </div>
                        <span class="badge-tag">Online Modality</span>
                    </div>

                    <div class="filters-bar">
                        <div class="search-wrap">
                            <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <input type="search" class="search-input" id="moduleSearch" placeholder="Search module by title or instructor..." onkeyup="filterModules()">
                        </div>
                    </div>

                    <div class="table-wrap">
                        <table id="modulesTable">
                            <thead>
                                <tr>
                                    <th style="width: 12%;">BLOCK</th>
                                    <th style="width: 38%;">MODULE / STUDY TOPIC</th>
                                    <th style="width: 25%;">INSTRUCTOR IN CHARGE</th>
                                    <th style="width: 12%;">PROGRESS</th>
                                    <th style="width: 13%; text-align: right;">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($blocks)): ?>
                                    <?php foreach ($blocks as $block): 
                                        $blockDone = !empty($block['is_completed']);
                                    ?>
                                        <tr onclick="window.location.href='blockContent.php?block_id=<?php echo urlencode($block['id']); ?>'" title="Click to view module content and study files">
                                            <td><span class="block-badge">Block #<?php echo htmlspecialchars($block['id']); ?></span></td>
                                            <td><span class="module-title"><?php echo htmlspecialchars($block['block_name']); ?></span></td>
                                            <td><span class="teacher-tag"><?php echo htmlspecialchars($block['teacher_name']); ?></span></td>
                                            <td>
                                                <?php if ($blockDone): ?>
                                                    <span style="display:inline-flex; align-items:center; gap:4px; background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; font-size:11px; font-weight:600; padding:3px 8px; border-radius:12px;">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                        <span>Completed</span>
                                                    </span>
                                                <?php else: ?>
                                                    <span style="display:inline-flex; align-items:center; gap:4px; background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe; font-size:11px; font-weight:600; padding:3px 8px; border-radius:12px;">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                                                        <span>In Progress</span>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="text-align: right;">
                                                <a href="blockContent.php?block_id=<?php echo urlencode($block['id']); ?>" class="btn-action" onclick="event.stopPropagation();" style="text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                                                    <span>View content</span>
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="table-empty">No modules recorded for this course</td>
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
        function filterModules() {
            const query = document.getElementById('moduleSearch').value.toLowerCase().trim();
            const rows = document.querySelectorAll('#modulesTable tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        }
    </script>
</body>
</html>
