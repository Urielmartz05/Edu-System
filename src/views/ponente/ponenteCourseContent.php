<?php
    require_once __DIR__ . "/../../controllers/auth/check_auth.php";
    requireRole([1, 3]);
    $user = getLoggedUser();

    require_once __DIR__ . "/../../models/teacherModels/teacherCoursesModel.php";

    $teacherCoursesModel = new TeacherCourses();
    $courseId = (int)($_GET['course_id'] ?? 0);

    if ($courseId <= 0) {
        header("Location: ponenteCourses.php");
        exit;
    }

    $course = $teacherCoursesModel->getCourseDetails($courseId, $user['id']);

    if (!$course) {
        header("Location: ponenteCourses.php?error=not_found");
        exit;
    }

    $blocks = $teacherCoursesModel->getCourseBlocksWithFiles($courseId, $user['id']);
    $teacherBlocks = $teacherCoursesModel->getTeacherBlocksForCourse($user['id'], $courseId);
    $allStudents = $teacherCoursesModel->getCourseStudents($courseId);

    // Filter teacher's assigned blocks
    $myBlocks = [];
    $totalFiles = 0;

    foreach ($blocks as $b) {
        if (!empty($b['is_my_block'])) {
            $filesCount = count($b['files'] ?? []);
            $totalFiles += $filesCount;
            $myBlocks[] = $b;
        }
    }

    // Tab state resolution (Pure PHP)
    $activeTab = ($_GET['tab'] ?? 'modules') === 'students' ? 'students' : 'modules';

    // Module search filter (Pure PHP)
    $moduleQuery = trim($_GET['module_query'] ?? '');
    if (!empty($moduleQuery)) {
        $myBlocks = array_filter($myBlocks, function($b) use ($moduleQuery) {
            return stripos($b['block_name'] ?? '', $moduleQuery) !== false;
        });
    }

    // Resolve block to rename if modal triggered
    $renameBlockId = (int)($_GET['rename_block_id'] ?? 0);
    $renameBlock = null;
    if ($renameBlockId > 0) {
        foreach ($blocks as $mb) {
            if ((int)$mb['id'] === $renameBlockId && !empty($mb['is_my_block'])) {
                $renameBlock = $mb;
                break;
            }
        }
    }

    // Student roster search and status filtering (Pure PHP)
    $studentQuery = trim($_GET['student_query'] ?? '');
    $studentStatus = trim($_GET['student_status'] ?? '');
    $filteredStudents = $allStudents;

    if (!empty($studentQuery)) {
        $filteredStudents = array_filter($filteredStudents, function($s) use ($studentQuery) {
            $full = ($s['name'] ?? '') . ' ' . ($s['lastname'] ?? '');
            $controlId = '#STU-' . (int)($s['id'] ?? 0);
            return stripos($full, $studentQuery) !== false
                || stripos($s['email'] ?? '', $studentQuery) !== false
                || stripos($controlId, $studentQuery) !== false;
        });
    }

    if (!empty($studentStatus)) {
        $filteredStudents = array_filter($filteredStudents, function($s) use ($studentStatus) {
            $isVer = ((int)($s['verified'] ?? 0) === 1);
            return ($studentStatus === 'verified' && $isVer) || ($studentStatus === 'pending' && !$isVer);
        });
    }

    // Student pagination (Pure PHP)
    $totalFilteredStudents = count($filteredStudents);
    $perPage = 10;
    $totalPages = max(1, (int)ceil($totalFilteredStudents / $perPage));
    $currentStudentPage = max(1, min((int)($_GET['p'] ?? 1), $totalPages));
    $studentsPage = array_slice($filteredStudents, ($currentStudentPage - 1) * $perPage, $perPage);

    // Extract student emails for mailto broadcast
    $studentEmails = array_filter(array_column($allStudents, 'email'));
    $studentEmailsString = implode(',', $studentEmails);

    // Check if upload or link modal is requested via PHP query param
    $uploadBlockId = (int)($_GET['upload_block_id'] ?? 0);
    $addLinkBlockId = (int)($_GET['add_link_block_id'] ?? 0);

    $flashSuccess = $_SESSION['flash_success'] ?? null;
    $flashError = $_SESSION['flash_error'] ?? null;
    unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    $statusText  = ((int)($course['status'] ?? 0) === 1) ? 'Active' : 'In Review';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['course_name']); ?> · Instructor Workspace</title>
    <link rel="stylesheet" href="ponenteCourseContent.css">
</head>
<body>
    <?php include_once __DIR__ . "/../../../ui/header/header.php"; ?>
    <?php include_once __DIR__ . "/../../../components/teacherSidebar/teacherSidebar.php"; ?>

    <div class="workspace">
        <main>
            <div id="page-content">

                <!-- Flash Messages -->
                <?php if (!empty($flashSuccess)): ?>
                    <div class="alert-banner alert-success">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        <span><?php echo htmlspecialchars($flashSuccess); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($flashError)): ?>
                    <div class="alert-banner alert-error">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span><?php echo htmlspecialchars($flashError); ?></span>
                    </div>
                <?php endif; ?>

                <!-- Page Heading Hero (Instructor Command Workspace) -->
                <section class="page-heading">
                    <div>
                        <span class="eyebrow">ACADEMIC INSTRUCTION · <?php echo htmlspecialchars($course['department_name']); ?> · <?php echo (int)$course['credits']; ?> CREDITS</span>
                        <h1><?php echo htmlspecialchars($course['course_name']); ?></h1>
                        <p class="subtitle">Curricular management workspace. Manage syllabus units, upload learning resources, and communicate directly with enrolled students.</p>
                    </div>
                </section>

                <!-- Instructor KPI Quick Cards -->
                <section class="kpi-grid">
                    <a href="ponenteCourseContent.php?course_id=<?php echo $courseId; ?>&tab=students" class="kpi" style="text-decoration:none; color:inherit; display:block;" title="View Enrolled Students">
                        <div class="kpi-header">
                            <h2 class="kpi-label">Class Enrollment</h2>
                            <div class="kpi-icon-wrap icon-emerald">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value"><?php echo count($allStudents); ?></strong>
                        <span class="kpi-hint">Click to inspect class roster</span>
                    </a>

                    <a href="ponenteCourseContent.php?course_id=<?php echo $courseId; ?>&tab=modules" class="kpi" style="text-decoration:none; color:inherit; display:block;" title="View Instructional Modules">
                        <div class="kpi-header">
                            <h2 class="kpi-label">Teaching Modules</h2>
                            <div class="kpi-icon-wrap icon-blue">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="7" r="4"/><path d="M5.5 21v-2a6.5 6.5 0 0 1 13 0v2"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value"><?php echo count($myBlocks); ?></strong>
                        <span class="kpi-hint">Syllabus units instructed</span>
                    </a>

                    <a href="ponenteCourseContent.php?course_id=<?php echo $courseId; ?>&tab=modules" class="kpi" style="text-decoration:none; color:inherit; display:block;" title="View Uploaded Documents">
                        <div class="kpi-header">
                            <h2 class="kpi-label">Study Materials</h2>
                            <div class="kpi-icon-wrap icon-amber">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value"><?php echo $totalFiles; ?></strong>
                        <span class="kpi-hint">Published documents</span>
                    </a>

                    <article class="kpi">
                        <div class="kpi-header">
                            <h2 class="kpi-label">Syllabus Progress</h2>
                            <div class="kpi-icon-wrap icon-blue">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value"><?php echo number_format((float)$course['progress'], 1); ?>%</strong>
                        <span class="kpi-hint">Curricular status: <?php echo $statusText; ?></span>
                    </article>
                </section>

                <!-- Workspace Navigation Tabs (Pure PHP Links) -->
                <nav class="workspace-tabs-bar" role="tablist">
                    <a href="ponenteCourseContent.php?course_id=<?php echo $courseId; ?>&tab=modules" class="tab-btn <?php echo ($activeTab === 'modules') ? 'active' : ''; ?>" style="text-decoration:none;" role="tab">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                        <span>Instructional Modules & Materials</span>
                        <span class="tab-counter"><?php echo count($myBlocks); ?></span>
                    </a>
                    <a href="ponenteCourseContent.php?course_id=<?php echo $courseId; ?>&tab=students" class="tab-btn <?php echo ($activeTab === 'students') ? 'active' : ''; ?>" style="text-decoration:none;" role="tab">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span>Enrolled Students Directory</span>
                        <span class="tab-counter"><?php echo count($allStudents); ?></span>
                    </a>
                </nav>

                <?php if ($activeTab === 'modules'): ?>
                    <!-- ========================================== -->
                    <!-- TAB 1: INSTRUCTIONAL MODULES (ACCORDION)   -->
                    <!-- ========================================== -->
                    <div id="tabContentModules" class="tab-panel active">
                        <section class="panel">
                            <!-- Pure PHP Toolbar for Module Search and Course Actions -->
                            <div class="panel-toolbar" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                                <form method="GET" action="ponenteCourseContent.php" class="toolbar-search-wrap" style="flex:1; max-width:380px;">
                                    <input type="hidden" name="course_id" value="<?php echo $courseId; ?>">
                                    <input type="hidden" name="tab" value="modules">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                    <input type="search" name="module_query" class="toolbar-search-input" placeholder="Search modules by title or keyword..." value="<?php echo htmlspecialchars($moduleQuery); ?>">
                                </form>
                                <?php if (!empty($moduleQuery)): ?>
                                    <a href="ponenteCourseContent.php?course_id=<?php echo $courseId; ?>&tab=modules" style="font-size:12px; color:#475569; text-decoration:none; padding:6px 12px; background:#f1f5f9; border-radius:6px; font-weight:600;">Reset</a>
                                <?php endif; ?>

                                <div class="toolbar-course-actions" style="display:flex; align-items:center; gap:8px;">
                                    <a href="ponenteCourseContent.php?course_id=<?php echo $courseId; ?>&tab=modules&add_block=1" class="btn-create-task" style="display:inline-flex; align-items:center; gap:6px; padding:8px 16px; background:#8B1528; color:#ffffff; font-size:13px; font-weight:700; text-decoration:none; border-radius:8px; box-shadow:0 2px 4px rgba(139,21,40,0.25);" title="Create new syllabus block">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                        <span>Add Block</span>
                                    </a>
                                    <a href="ponenteAssignments.php?action=new&new_course_id=<?php echo $courseId; ?>" class="btn-create-task" style="display:inline-flex; align-items:center; gap:6px; padding:8px 14px; background:#1e293b; color:#ffffff; font-size:12.5px; font-weight:600; text-decoration:none; border-radius:8px;" title="Publish coursework assignment for this course">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                        <span>Add Assignment</span>
                                    </a>
                                    <a href="ponenteGrades.php?course_id=<?php echo $courseId; ?>" class="btn-gradebook-link" style="display:inline-flex; align-items:center; gap:6px; padding:8px 14px; background:#f1f5f9; color:#334155; font-size:12.5px; font-weight:600; text-decoration:none; border-radius:8px; border:1px solid #e2e8f0;" title="Inspect Course Gradebook Matrix">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/><circle cx="12" cy="12" r="2"/></svg>
                                        <span>Course Gradebook</span>
                                    </a>
                                </div>
                            </div>

                            <!-- Modules Accordion List (HTML5 Native Details/Summary) -->
                            <div class="accordion-container" id="modulesAccordionContainer">
                                <?php if (!empty($myBlocks)): ?>
                                    <?php foreach ($myBlocks as $idx => $block):
                                        $blockId = (int)$block['id'];
                                        $files = $block['files'] ?? [];
                                        $filesCount = count($files);
                                        $isOpen = ($idx < 2) || !empty($moduleQuery);
                                    ?>
                                        <details class="accordion-item" <?php echo $isOpen ? 'open' : ''; ?>>
                                            <!-- Accordion Header -->
                                            <summary class="accordion-header">
                                                <div class="accordion-title-wrap">
                                                    <div class="accordion-chevron">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                                                    </div>
                                                    <span class="block-num">#<?php echo sprintf('%02d', $idx + 1); ?></span>
                                                    <h3 class="accordion-title"><?php echo htmlspecialchars($block['block_name']); ?></h3>
                                                    <span class="pill-files-count">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                                        <?php echo $filesCount; ?> <?php echo ($filesCount === 1) ? 'file' : 'files'; ?>
                                                    </span>
                                                </div>

                                                <div class="accordion-header-actions">
                                                    <a href="ponenteCourseContent.php?course_id=<?php echo $courseId; ?>&tab=modules&upload_block_id=<?php echo $blockId; ?>" class="btn-add-file-block" style="text-decoration:none;" title="Attach document to this module">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                                        <span>Attach File</span>
                                                    </a>
                                                    <a href="ponenteCourseContent.php?course_id=<?php echo $courseId; ?>&tab=modules&add_link_block_id=<?php echo $blockId; ?>" class="btn-add-file-block" style="text-decoration:none; background:#eff6ff; color:#2563eb; border-color:#bfdbfe;" title="Attach web reference or video lecture">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                                        <span>Attach Link</span>
                                                    </a>
                                                    <a href="ponenteCourseContent.php?course_id=<?php echo $courseId; ?>&tab=modules&rename_block_id=<?php echo $blockId; ?>" class="btn-add-file-block" style="text-decoration:none; background:#f0fdfa; color:#0f766e; border-color:#99f6e4;" onclick="event.stopPropagation();" title="Edit / Rename this module">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Edit Name</span>
                                                    </a>
                                                    <form method="POST" action="../../controllers/admin/manageCourseActions.php" style="display:inline; margin:0;" onclick="event.stopPropagation();" onsubmit="event.stopPropagation(); return confirm('Delete this block and all attached files permanently?');">
                                                        <?php echo csrfField(); ?>
                                                        <input type="hidden" name="action" value="delete_block">
                                                        <input type="hidden" name="block_id" value="<?php echo (int)$blockId; ?>">
                                                        <input type="hidden" name="course_id" value="<?php echo (int)$courseId; ?>">
                                                        <button type="submit" class="btn-add-file-block" style="background:#ffffff; color:#dc2626; border:1px solid #fca5a5; cursor:pointer; font-weight:600;" title="Delete this block">
                                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                            <span>Delete Block</span>
                                                        </button>
                                                    </form>
                                                </div>
                                            </summary>

                                            <!-- Accordion Collapsible Content -->
                                            <div class="accordion-body">
                                                <div class="accordion-body-inner">
                                                    <?php if (!empty($files)): ?>
                                                        <div class="files-grid">
                                                            <?php foreach ($files as $file): ?>
                                                                <div class="file-item">
                                                                    <a href="../../controllers/files/downloadFile.php?file_id=<?php echo (int)$file['id']; ?>&mode=download" class="file-info" title="Download <?php echo htmlspecialchars($file['file_name']); ?>">
                                                                        <div class="file-icon">
                                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                                                        </div>
                                                                        <span class="file-name"><?php echo htmlspecialchars($file['file_name']); ?></span>
                                                                    </a>
                                                                    <div class="file-actions">
                                                                        <a href="../../controllers/files/downloadFile.php?file_id=<?php echo (int)$file['id']; ?>&mode=download" class="btn-download-icon" title="Download Material">
                                                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                                                        </a>
                                                                        <form method="POST" action="../../controllers/teacher/manageCourseFiles.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this resource?');">
                                                                            <?php echo csrfField(); ?>
                                                                            <input type="hidden" name="action" value="delete">
                                                                            <input type="hidden" name="file_id" value="<?php echo (int)$file['id']; ?>">
                                                                            <input type="hidden" name="course_id" value="<?php echo (int)$courseId; ?>">
                                                                            <button type="submit" class="btn-delete-file" title="Remove material">
                                                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                                                            </button>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="empty-files-box">
                                                            <p>No study materials attached to this module yet.</p>
                                                        </div>
                                                    <?php endif; ?>

                                                    <?php 
                                                        $blockLinks = $block['links'] ?? [];
                                                        if (!empty($blockLinks)):
                                                    ?>
                                                        <div style="margin-top: 18px; border-top: 1px dashed #e2e8f0; padding-top: 14px;">
                                                            <div style="font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                                                <span>External Web & Video Resources (<?php echo count($blockLinks); ?>)</span>
                                                            </div>
                                                            <div class="files-grid">
                                                                <?php foreach ($blockLinks as $link): ?>
                                                                    <div class="file-item">
                                                                        <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" rel="noopener noreferrer" class="file-info" title="Open external link">
                                                                            <div class="file-icon" style="background:#eff6ff; color:#2563eb;">
                                                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                                                                            </div>
                                                                            <span class="file-name"><?php echo htmlspecialchars($link['title']); ?></span>
                                                                        </a>
                                                                        <div class="file-actions">
                                                                            <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" rel="noopener noreferrer" class="btn-download-icon" style="background:#eff6ff; color:#2563eb; border-color:#bfdbfe;" title="Open Link">
                                                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                                                            </a>
                                                                            <form method="POST" action="../../controllers/teacher/manageCourseLinks.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this link?');">
                                                                                <?php echo csrfField(); ?>
                                                                                <input type="hidden" name="action" value="delete">
                                                                                <input type="hidden" name="link_id" value="<?php echo (int)$link['id']; ?>">
                                                                                <input type="hidden" name="course_id" value="<?php echo (int)$courseId; ?>">
                                                                                <button type="submit" class="btn-delete-file" title="Remove link">
                                                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                                                                </button>
                                                                            </form>
                                                                        </div>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </details>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="empty-state" style="text-align:center; padding:36px 20px;">
                                        <p style="margin-bottom:12px; color:#64748b; font-size:14px;">No syllabus blocks assigned or created yet in this course.</p>
                                        <a href="ponenteCourseContent.php?course_id=<?php echo $courseId; ?>&tab=modules&add_block=1" style="display:inline-flex; align-items:center; gap:6px; padding:9px 18px; background:#8B1528; color:#ffffff; font-size:13px; font-weight:700; text-decoration:none; border-radius:8px;">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                            <span>Add Block</span>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </section>
                    </div>

                <?php else: ?>
                    <!-- ========================================== -->
                    <!-- TAB 2: ENROLLED STUDENTS (PURE PHP)        -->
                    <!-- ========================================== -->
                    <div id="tabContentStudents" class="tab-panel active">
                        <section class="panel">
                            <!-- Pure PHP Toolbar for Students Search & Filter -->
                            <form method="GET" action="ponenteCourseContent.php" class="panel-toolbar">
                                <input type="hidden" name="course_id" value="<?php echo $courseId; ?>">
                                <input type="hidden" name="tab" value="students">

                                <div class="toolbar-search-wrap">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                    <input type="search" name="student_query" class="toolbar-search-input" placeholder="Search student by name, student ID or email..." value="<?php echo htmlspecialchars($studentQuery); ?>">
                                </div>

                                <div class="toolbar-actions">
                                    <select name="student_status" class="toolbar-select">
                                        <option value="">All Account Statuses</option>
                                        <option value="verified" <?php echo ($studentStatus === 'verified') ? 'selected' : ''; ?>>Verified Only</option>
                                        <option value="pending" <?php echo ($studentStatus === 'pending') ? 'selected' : ''; ?>>Pending Only</option>
                                    </select>

                                    <button type="submit" class="btn-primary" style="padding: 8px 16px; border-radius: 6px; border:none; background:#881337; color:#fff; font-weight:600; cursor:pointer;">Filter</button>

                                    <?php if (!empty($studentQuery) || !empty($studentStatus)): ?>
                                        <a href="ponenteCourseContent.php?course_id=<?php echo $courseId; ?>&tab=students" style="padding: 8px 14px; background:#f1f5f9; border-radius:6px; text-decoration:none; color:#475569; font-weight:600; font-size:13px;">Reset</a>
                                    <?php endif; ?>

                                    <?php if (!empty($studentEmailsString)): ?>
                                        <a href="mailto:?bcc=<?php echo htmlspecialchars($studentEmailsString); ?>&subject=<?php echo urlencode('[' . $course['course_name'] . '] Notice from Instructor'); ?>" class="btn-outline-dark-action" style="text-decoration:none;" title="Send email notice to all enrolled students">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                            <span>Email Class (BCC)</span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>

                            <!-- Students Table Wrap -->
                            <div class="table-wrap">
                                <?php if (!empty($studentsPage)): ?>
                                    <table id="studentsTable">
                                        <thead>
                                            <tr>
                                                <th style="width: 26%;">STUDENT</th>
                                                <th style="width: 14%;">CONTROL ID</th>
                                                <th style="width: 22%;">INSTITUTIONAL EMAIL</th>
                                                <th style="width: 18%;">COURSE PROGRESS</th>
                                                <th style="width: 10%;">ACCOUNT</th>
                                                <th style="width: 10%; text-align: right;">ACTION</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($studentsPage as $stu): ?>
                                                <?php
                                                    $isVerified = ((int)($stu['verified'] ?? 0) === 1);
                                                    $badgeClass = $isVerified ? 'active' : 'pending';
                                                    $badgeText  = $isVerified ? 'Verified' : 'Pending';
                                                    $initials   = strtoupper(substr($stu['name'] ?? 'S', 0, 1) . substr($stu['lastname'] ?? '', 0, 1));
                                                    $fullName   = trim(($stu['name'] ?? '') . ' ' . ($stu['lastname'] ?? ''));
                                                    $controlId  = '#STU-' . (int)$stu['id'];
                                                    $email      = $stu['email'] ?? '';
                                                    $stuProgress = (int)($stu['progress_percent'] ?? 0);
                                                    $completedBlocks = (int)($stu['completed_blocks'] ?? 0);
                                                    $totalBlocksCount = (int)($stu['total_blocks'] ?? 0);
                                                ?>
                                                <tr class="student-row">
                                                    <td>
                                                        <div class="user-cell">
                                                            <div class="user-avatar"><?php echo htmlspecialchars($initials); ?></div>
                                                            <div>
                                                                <div style="font-weight: 600; color: #0f172a;"><?php echo htmlspecialchars($fullName); ?></div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span style="font-weight: 600; color: #475569; font-size: 12.5px;"><?php echo htmlspecialchars($controlId); ?></span>
                                                    </td>
                                                    <td>
                                                        <a href="mailto:<?php echo htmlspecialchars($email); ?>" style="color: #64748b; text-decoration: none; font-size: 13px;">
                                                            <?php echo htmlspecialchars($email); ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <div style="display:flex; align-items:center; gap:8px;">
                                                            <div style="flex:1; height:6px; background:#e2e8f0; border-radius:3px; overflow:hidden;">
                                                                <div style="width:<?php echo min(100, $stuProgress); ?>%; height:100%; background:<?php echo ($stuProgress >= 100) ? '#16a34a' : '#8B1528'; ?>; border-radius:3px;"></div>
                                                            </div>
                                                            <span style="font-size:12px; font-weight:600; color:#334155; min-width:32px;"><?php echo $stuProgress; ?>%</span>
                                                        </div>
                                                        <span style="font-size:11px; color:#94a3b8;"><?php echo $completedBlocks; ?>/<?php echo $totalBlocksCount; ?> blocks</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge <?php echo $badgeClass; ?>"><?php echo $badgeText; ?></span>
                                                    </td>
                                                    <td style="text-align: right;">
                                                        <a href="mailto:<?php echo htmlspecialchars($email); ?>?subject=<?php echo urlencode('[' . $course['course_name'] . '] Instructor Message'); ?>" class="btn-contact-student" style="text-decoration:none;" title="Send individual email">
                                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                                            <span>Contact</span>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <div class="empty-state">No students match your search filter in this course group.</div>
                                <?php endif; ?>
                            </div>

                            <!-- Pure PHP Pagination Footer -->
                            <?php if ($totalFilteredStudents > 0): ?>
                                <div class="pagination-footer">
                                    <span class="pagination-info">
                                        Showing <?php echo (($currentStudentPage - 1) * $perPage) + 1; ?> to <?php echo min($currentStudentPage * $perPage, $totalFilteredStudents); ?> of <?php echo $totalFilteredStudents; ?> students
                                    </span>
                                    <div class="pagination-controls">
                                        <?php if ($currentStudentPage > 1): ?>
                                            <a href="ponenteCourseContent.php?course_id=<?php echo $courseId; ?>&tab=students&p=<?php echo $currentStudentPage - 1; ?>&student_query=<?php echo urlencode($studentQuery); ?>&student_status=<?php echo urlencode($studentStatus); ?>" class="btn-page" style="text-decoration:none;">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                                                <span>Previous</span>
                                            </a>
                                        <?php else: ?>
                                            <button type="button" class="btn-page" disabled>
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                                                <span>Previous</span>
                                            </button>
                                        <?php endif; ?>

                                        <span class="page-indicator">Page <?php echo $currentStudentPage; ?> of <?php echo $totalPages; ?></span>

                                        <?php if ($currentStudentPage < $totalPages): ?>
                                            <a href="ponenteCourseContent.php?course_id=<?php echo $courseId; ?>&tab=students&p=<?php echo $currentStudentPage + 1; ?>&student_query=<?php echo urlencode($studentQuery); ?>&student_status=<?php echo urlencode($studentStatus); ?>" class="btn-page" style="text-decoration:none;">
                                                <span>Next</span>
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                                            </a>
                                        <?php else: ?>
                                            <button type="button" class="btn-page" disabled>
                                                <span>Next</span>
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </section>
                    </div>
                <?php endif; ?>

            </div>
        </main>
    </div>

    <!-- Upload Material Modal (Controlled via Pure PHP query parameter ?upload_block_id=...) -->
    <?php if ($uploadBlockId > 0): ?>
        <div class="modal-backdrop" id="uploadModal" style="display: flex;">
            <div class="modal-dialog">
                <div class="modal-header">
                    <h3>Attach Learning Resource</h3>
                    <a href="ponenteCourseContent.php?course_id=<?php echo $courseId; ?>&tab=<?php echo $activeTab; ?>" class="btn-close-modal" style="text-decoration:none;">&times;</a>
                </div>
                <form action="../../controllers/teacher/manageCourseFiles.php" method="POST" enctype="multipart/form-data"><?php echo csrfField(); ?>
                    <input type="hidden" name="action" value="upload">
                    <input type="hidden" name="course_id" value="<?php echo $courseId; ?>">

                    <div class="modal-body">
                        <div class="form-group">
                            <label for="blockSelect">Target Syllabus Module *</label>
                            <select name="block_id" id="blockSelect" class="form-control" required>
                                <?php foreach ($teacherBlocks as $tb): ?>
                                    <option value="<?php echo (int)$tb['id']; ?>" <?php echo ((int)$tb['id'] === $uploadBlockId) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($tb['block_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="fileTitle">Document Title (Optional)</label>
                            <input type="text" name="file_title" id="fileTitle" class="form-control" placeholder="e.g. Unit 1 Lecture Notes & Lab Guide">
                            <span class="form-hint">If left empty, original file name will be used.</span>
                        </div>

                        <div class="form-group">
                            <label for="fileUploadInput">Select Document / Resource *</label>
                            <input type="file" name="file" id="fileUploadInput" class="form-control" required>
                            <span class="form-hint">Supported: PDF, DOC, PPT, XLS, ZIP, Images (Max 25 MB).</span>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <a href="ponenteCourseContent.php?course_id=<?php echo $courseId; ?>&tab=<?php echo $activeTab; ?>" class="btn-cancel-modal" style="text-decoration:none;">Cancel</a>
                        <button type="submit" class="btn-submit-modal">Upload & Publish</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Attach Link Modal (Pure PHP query parameter ?add_link_block_id=...) -->
    <?php if ($addLinkBlockId > 0): ?>
        <div class="modal-backdrop" id="addLinkModal" style="display: flex;">
            <div class="modal-dialog">
                <div class="modal-header">
                    <h3>Attach External Resource / Video</h3>
                    <a href="ponenteCourseContent.php?course_id=<?php echo $courseId; ?>&tab=<?php echo $activeTab; ?>" class="btn-close-modal" style="text-decoration:none;">&times;</a>
                </div>
                <form action="../../controllers/teacher/manageCourseLinks.php" method="POST"><?php echo csrfField(); ?>
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="course_id" value="<?php echo $courseId; ?>">

                    <div class="modal-body">
                        <div class="form-group">
                            <label for="linkBlockSelect">Target Syllabus Module *</label>
                            <select name="block_id" id="linkBlockSelect" class="form-control" required>
                                <?php foreach ($teacherBlocks as $tb): ?>
                                    <option value="<?php echo (int)$tb['id']; ?>" <?php echo ((int)$tb['id'] === $addLinkBlockId) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($tb['block_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="linkTitle">Resource Title *</label>
                            <input type="text" name="title" id="linkTitle" class="form-control" placeholder="e.g. YouTube: Complete OOP Walkthrough" required>
                        </div>

                        <div class="form-group">
                            <label for="linkUrl">Destination Web URL *</label>
                            <input type="url" name="url" id="linkUrl" class="form-control" placeholder="https://www.youtube.com/watch?v=..." required>
                            <span class="form-hint">Must begin with https:// or http://</span>
                        </div>

                        <div class="form-group">
                            <label for="linkType">Resource Category</label>
                            <select name="link_type" id="linkType" class="form-control">
                                <option value="video">Video Lecture (YouTube, Zoom, Vimeo)</option>
                                <option value="document">Online Documentation / Cloud Document</option>
                                <option value="external" selected>General Web Resource / Article</option>
                                <option value="other">Other Reference</option>
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <a href="ponenteCourseContent.php?course_id=<?php echo $courseId; ?>&tab=<?php echo $activeTab; ?>" class="btn-cancel-modal" style="text-decoration:none;">Cancel</a>
                        <button type="submit" class="btn-submit-modal">Attach Link</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Create Block Modal (Pure PHP query parameter ?add_block=1) -->
    <?php if (!empty($_GET['add_block'])): ?>
        <div class="modal-backdrop" id="addBlockModal" style="display: flex;">
            <div class="modal-dialog">
                <div class="modal-header">
                    <h3>Add Syllabus Block</h3>
                    <a href="ponenteCourseContent.php?course_id=<?php echo $courseId; ?>&tab=<?php echo $activeTab; ?>" class="btn-close-modal" style="text-decoration:none;">&times;</a>
                </div>
                <form action="../../controllers/admin/manageBlock.php" method="POST"><?php echo csrfField(); ?>
                    <input type="hidden" name="action" value="create_block">
                    <input type="hidden" name="course_id" value="<?php echo $courseId; ?>">
                    <input type="hidden" name="return_url" value="ponenteCourseContent.php?course_id=<?php echo $courseId; ?>">

                    <div class="modal-body">
                        <div class="form-group">
                            <label for="blockNameInput">Block Title *</label>
                            <input type="text" name="block_name" id="blockNameInput" class="form-control" placeholder="e.g. Unit 3: Advanced Architecture & Testing" maxlength="60" required>
                            <span class="form-hint">You will be designated as the faculty instructor for this block.</span>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <a href="ponenteCourseContent.php?course_id=<?php echo $courseId; ?>&tab=<?php echo $activeTab; ?>" class="btn-cancel-modal" style="text-decoration:none;">Cancel</a>
                        <button type="submit" class="btn-submit-modal" style="background:#8B1528;">Create Block</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Rename Block Modal (Query parameter ?rename_block_id=X) -->
    <?php if ($renameBlock): ?>
        <div class="modal-backdrop" id="renameBlockModal" style="display: flex;">
            <div class="modal-dialog">
                <div class="modal-header">
                    <h3>Edit Module Title</h3>
                    <a href="ponenteCourseContent.php?course_id=<?php echo $courseId; ?>&tab=<?php echo $activeTab; ?>" class="btn-close-modal" style="text-decoration:none;">&times;</a>
                </div>
                <form action="../../controllers/admin/manageBlock.php" method="POST"><?php echo csrfField(); ?>
                    <input type="hidden" name="action" value="rename_block">
                    <input type="hidden" name="block_id" value="<?php echo (int)$renameBlock['id']; ?>">
                    <input type="hidden" name="course_id" value="<?php echo $courseId; ?>">
                    <input type="hidden" name="return_url" value="ponenteCourseContent.php?course_id=<?php echo $courseId; ?>">

                    <div class="modal-body">
                        <div class="form-group">
                            <label for="renameBlockInput">Module Title *</label>
                            <input type="text" name="block_name" id="renameBlockInput" class="form-control" value="<?php echo htmlspecialchars($renameBlock['block_name']); ?>" maxlength="60" required autofocus>
                            <span class="form-hint">Update the descriptive title for this syllabus unit.</span>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <a href="ponenteCourseContent.php?course_id=<?php echo $courseId; ?>&tab=<?php echo $activeTab; ?>" class="btn-cancel-modal" style="text-decoration:none;">Cancel</a>
                        <button type="submit" class="btn-submit-modal" style="background:#0f766e;">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>