<?php
    require_once __DIR__ . "/../../controllers/auth/check_auth.php";
    requireRole([4]);
    $user = getLoggedUser();

    require_once __DIR__ . "/../../models/studentModels/studentCoursesModel.php";
    require_once __DIR__ . "/../../models/assignmentModels/assignmentModel.php";

    $studentCoursesModel = new StudentCourses();
    $assignmentModel = new AssignmentModel();
    $blockId = (int)($_GET['block_id'] ?? 0);

    if ($blockId <= 0) {
        header("Location: alumnoCourses.php");
        exit;
    }

    $block = $studentCoursesModel->getBlockDetails($blockId, $user['id']);

    if (!$block) {
        header("Location: alumnoCourses.php?error=block_not_found");
        exit;
    }

    $files = $studentCoursesModel->getBlockFiles($blockId);
    $links = $studentCoursesModel->getBlockLinks($blockId);
    $isCompleted = !empty($block['is_completed']);
    $courseProgress = $studentCoursesModel->getDynamicCourseProgress($user['id'], $block['course_id']);

    // Check assignments for this module
    $allCourseTasks = $assignmentModel->getStudentAssignments($user['id'], $block['course_id']);
    $moduleTasks = array_filter($allCourseTasks, function($t) use ($blockId) {
        return (int)($t['block_id'] ?? 0) === $blockId;
    });
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($block['block_name']); ?> · Edu-System</title>
    <link rel="stylesheet" href="blockContent.css">
</head>
<body>
    <?php include __DIR__ . '/../../../ui/header/header.php'; ?>
    <?php include __DIR__ . '/../../../components/studentSidebar/studentSidebar.php'; ?>

    <div class="workspace">
        <main>
            <div id="page-content">

                <!-- Page Heading Hero -->
                <section class="page-heading">
                    <div>
                        <span class="eyebrow"><?php echo htmlspecialchars($block['course_name']); ?> · <?php echo htmlspecialchars($block['department_name']); ?></span>
                        <h1><?php echo htmlspecialchars($block['block_name']); ?></h1>
                        <p class="subtitle">Led by instructor <?php echo htmlspecialchars($block['teacher_name']); ?>. Access attached learning resources, syllabus objectives, and reference materials.</p>
                    </div>
                </section>

                <!-- Module Actions Toolbar -->
                <div class="panel-toolbar" style="margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                    <a href="alumnoCourses.php" class="btn-back" style="text-decoration:none;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                        <span>Back to Modules</span>
                    </a>
                    <form action="../../controllers/alumno/toggleBlockProgress.php" method="POST" style="display:inline;"><?php echo csrfField(); ?>
                        <input type="hidden" name="block_id" value="<?php echo (int)$block['id']; ?>">
                        <button type="submit" id="btnToggleProgress" class="btn-toggle-progress <?php echo $isCompleted ? 'is-completed' : ''; ?>" title="<?php echo $isCompleted ? 'Click to mark as Incomplete' : 'Click to mark as Completed'; ?>">
                            <?php if ($isCompleted): ?>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                <span id="btnText">Completed ✓</span>
                            <?php else: ?>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                                <span id="btnText">Mark as Completed</span>
                            <?php endif; ?>
                        </button>
                    </form>
                </div>

                <!-- KPI Grid -->
                <section class="kpi-grid">
                    <article class="kpi">
                        <div class="kpi-header">
                            <h2 class="kpi-label">Module Unit</h2>
                            <div class="kpi-icon-wrap icon-maroon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value">Block #<?php echo (int)$block['id']; ?></strong>
                        <span class="kpi-hint">Curriculum structure</span>
                    </article>
                    <article class="kpi">
                        <div class="kpi-header">
                            <h2 class="kpi-label">Credits Value</h2>
                            <div class="kpi-icon-wrap icon-blue">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value"><?php echo (int)$block['credits']; ?></strong>
                        <span class="kpi-hint">Course credits</span>
                    </article>
                    <article class="kpi">
                        <div class="kpi-header">
                            <h2 class="kpi-label">Digital Files</h2>
                            <div class="kpi-icon-wrap icon-emerald">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value"><?php echo count($files); ?></strong>
                        <span class="kpi-hint">Attached study resources</span>
                    </article>
                    <article class="kpi">
                        <div class="kpi-header">
                            <h2 class="kpi-label">Progress Status</h2>
                            <div class="kpi-icon-wrap <?php echo $isCompleted ? 'icon-emerald' : 'icon-amber'; ?>" id="kpiProgressIcon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value" id="kpiProgressStatus"><?php echo $isCompleted ? 'Completed' : 'In Progress'; ?></strong>
                        <span class="kpi-hint" id="kpiProgressHint"><?php echo $courseProgress; ?>% syllabus completed</span>
                    </article>
                </section>

                <!-- Content Grid -->
                <div class="content-grid">
                    <!-- Left: Learning Resources & Attached Files -->
                    <section class="panel">
                        <div class="panel-head">
                            <div>
                                <h2>Study Materials & Attached Resources</h2>
                                <p>Digital files, reading guides, and lecture documents provided by the instructor.</p>
                            </div>
                            <span class="badge-tag" style="background:#eff6ff; color:#2563eb; font-weight:600; padding:6px 12px; border-radius:20px; font-size:12px;">
                                <?php echo count($files); ?> Resource(s)
                            </span>
                        </div>
                        <div class="panel-body">
                            <?php if (!empty($files)): ?>
                                <div class="file-list">
                                    <?php foreach ($files as $file): ?>
                                        <div class="file-item">
                                            <div class="file-info">
                                                <div class="file-icon">
                                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                                </div>
                                                <div>
                                                    <div class="file-name"><?php echo htmlspecialchars($file['file_name']); ?></div>
                                                    <div class="file-meta">Subject Resource • Block #<?php echo (int)$block['id']; ?></div>
                                                </div>
                                            </div>
                                            <div style="display:flex; align-items:center; gap:8px;">
                                                <a href="../../controllers/files/downloadFile.php?file_id=<?php echo (int)$file['id']; ?>&mode=download" class="btn-download" title="Download Material">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                                    <span>Download</span>
                                                </a>
                                                <a href="../../controllers/files/downloadFile.php?file_id=<?php echo (int)$file['id']; ?>&mode=view" target="_blank" class="btn-download" style="background:#f1f5f9; color:#334155; border:1px solid #cbd5e1;" title="Open in new tab">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                                    <span>Open</span>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-files">
                                    <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                                    <h3>No files uploaded yet</h3>
                                    <p>Your instructor has not uploaded digital attachments for this block yet. Check back soon or contact your instructor directly.</p>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($links)): ?>
                                <div style="margin-top: 24px; border-top: 1px dashed #e2e8f0; padding-top: 18px;">
                                    <h3 style="font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                        <span>External Web Resources & Video Lectures</span>
                                    </h3>
                                    <div class="file-list">
                                        <?php foreach ($links as $link): ?>
                                            <div class="file-item">
                                                <div class="file-info">
                                                    <div class="file-icon" style="background:#eff6ff; color:#2563eb;">
                                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                                                    </div>
                                                    <div>
                                                        <div class="file-name"><?php echo htmlspecialchars($link['title']); ?></div>
                                                        <div class="file-meta"><?php echo htmlspecialchars(parse_url($link['url'], PHP_URL_HOST) ?: 'Web Resource'); ?> · <?php echo ucfirst($link['link_type'] ?: 'Link'); ?></div>
                                                    </div>
                                                </div>
                                                <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" rel="noopener noreferrer" class="btn-download" style="background:#0284c7; text-decoration:none;" title="Open external resource">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                                    <span>Open Link</span>
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>

                    <!-- Right: Instructor & Syllabus Info -->
                    <div style="display:flex; flex-direction:column; gap:24px;">
                        <section class="panel">
                            <div class="panel-head">
                                <div>
                                    <h2>Assigned Instructor</h2>
                                    <p>Academic faculty in charge</p>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="instructor-card">
                                    <div class="instructor-profile">
                                        <div class="instructor-avatar">
                                            <?php echo strtoupper(substr($block['teacher_name'] ?? 'T', 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div class="instructor-name"><?php echo htmlspecialchars($block['teacher_name']); ?></div>
                                            <div class="instructor-role">Course Instructor</div>
                                        </div>
                                    </div>
                                    <p style="font-size:12.5px; color:#64748b; line-height:1.5;">
                                        For questions regarding syllabus topics, assignments, or evaluations for this block, contact your instructor via email.
                                    </p>
                                    <a href="mailto:<?php echo htmlspecialchars($block['teacher_email']); ?>" class="btn-contact">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                        <span><?php echo htmlspecialchars($block['teacher_email']); ?></span>
                                    </a>
                                </div>
                            </div>
                        </section>

                        <section class="panel">
                            <div class="panel-head">
                                <div>
                                    <h2>Module Deliverables</h2>
                                    <p>Coursework & academic tasks</p>
                                </div>
                            </div>
                            <div class="panel-body" style="font-size:13px; color:#64748b; line-height:1.6;">
                                <p>Active tasks for this unit: <strong><?php echo count($moduleTasks); ?></strong></p>
                                <a href="alumnoAssignments.php?course_id=<?php echo $block['course_id']; ?>&search=<?php echo urlencode($block['block_name']); ?>" class="btn-contact" style="margin-top:12px; background:#8B1528; color:#ffffff; border-color:#8B1528;" title="View all coursework for this module">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="m9 14 2 2 4-4"/></svg>
                                    <span>View Tasks & Submissions</span>
                                </a>
                            </div>
                        </section>

                        <section class="panel">
                            <div class="panel-head">
                                <div>
                                    <h2>Module Syllabus</h2>
                                    <p>Academic division details</p>
                                </div>
                            </div>
                            <div class="panel-body" style="font-size:13px; color:#64748b; line-height:1.6;">
                                <p><strong>Department:</strong> <?php echo htmlspecialchars($block['department_name']); ?></p>
                                <p style="margin-top:8px;"><strong>Course Program:</strong> <?php echo htmlspecialchars($block['course_name']); ?></p>
                                <p style="margin-top:8px;"><strong>Academic Weight:</strong> <?php echo (int)$block['credits']; ?> Credit Units</p>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>
</html>
