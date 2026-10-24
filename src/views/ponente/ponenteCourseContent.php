<?php
    require_once __DIR__ . "/../../controllers/auth/check_auth.php";
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
    $students = $teacherCoursesModel->getCourseStudents($courseId);

    // Keep only teacher's assigned blocks
    $myBlocks = [];
    $totalFiles = 0;

    foreach ($blocks as $b) {
        if (!empty($b['is_my_block'])) {
            $filesCount = count($b['files'] ?? []);
            $totalFiles += $filesCount;
            $myBlocks[] = $b;
        }
    }

    // Extract student emails for mailto broadcast
    $studentEmails = array_filter(array_column($students, 'email'));
    $studentEmailsString = implode(',', $studentEmails);

    $flashSuccess = $_SESSION['flash_success'] ?? null;
    $flashError = $_SESSION['flash_error'] ?? null;
    unset($_SESSION['flash_success'], $_SESSION['flash_error']);

    $statusClass = ((int)($course['status'] ?? 0) === 1) ? 'active' : 'pending';
    $statusText  = ((int)($course['status'] ?? 0) === 1) ? 'Active' : 'In Review';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['course_name']); ?> · Instructor Workspace</title>
    <link rel="stylesheet" href="courseContent.css">
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
                    <article class="kpi" onclick="switchWorkspaceTab('students')" style="cursor: pointer;" title="View Enrolled Students">
                        <div class="kpi-header">
                            <h2 class="kpi-label">Class Enrollment</h2>
                            <div class="kpi-icon-wrap icon-emerald">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value"><?php echo count($students); ?></strong>
                        <span class="kpi-hint">Click to inspect class roster</span>
                    </article>

                    <article class="kpi" onclick="switchWorkspaceTab('modules')" style="cursor: pointer;" title="View Instructional Modules">
                        <div class="kpi-header">
                            <h2 class="kpi-label">Teaching Modules</h2>
                            <div class="kpi-icon-wrap icon-blue">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="7" r="4"/><path d="M5.5 21v-2a6.5 6.5 0 0 1 13 0v2"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value"><?php echo count($myBlocks); ?></strong>
                        <span class="kpi-hint">Syllabus units instructed</span>
                    </article>

                    <article class="kpi" onclick="switchWorkspaceTab('modules')" style="cursor: pointer;" title="View Uploaded Documents">
                        <div class="kpi-header">
                            <h2 class="kpi-label">Study Materials</h2>
                            <div class="kpi-icon-wrap icon-amber">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value"><?php echo $totalFiles; ?></strong>
                        <span class="kpi-hint">Published documents</span>
                    </article>

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

                <!-- Workspace Navigation Tabs -->
                <nav class="workspace-tabs-bar" role="tablist">
                    <button type="button" class="tab-btn active" id="tabBtnModules" onclick="switchWorkspaceTab('modules')" role="tab" aria-selected="true">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                        <span>Instructional Modules & Materials</span>
                        <span class="tab-counter"><?php echo count($myBlocks); ?></span>
                    </button>
                    <button type="button" class="tab-btn" id="tabBtnStudents" onclick="switchWorkspaceTab('students')" role="tab" aria-selected="false">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span>Enrolled Students Directory</span>
                        <span class="tab-counter"><?php echo count($students); ?></span>
                    </button>
                </nav>

                <!-- ========================================== -->
                <!-- TAB 1: INSTRUCTIONAL MODULES (ACCORDION)   -->
                <!-- ========================================== -->
                <div id="tabContentModules" class="tab-panel active">
                    <section class="panel">
                        <!-- Toolbar for 20+ modules scale -->
                        <div class="panel-toolbar">
                            <div class="toolbar-search-wrap">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                <input type="search" id="moduleSearchInput" class="toolbar-search-input" placeholder="Search modules by title or keyword..." oninput="filterModules()">
                            </div>
                            <div class="toolbar-actions">
                                <button type="button" class="btn-toggle-all" id="btnToggleAllModules" onclick="toggleAllModules()">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="7 13 12 18 17 13"/><polyline points="7 6 12 11 17 6"/></svg>
                                    <span id="toggleAllModulesText">Expand All</span>
                                </button>
                            </div>
                        </div>

                        <!-- Modules Accordion List -->
                        <div class="accordion-container" id="modulesAccordionContainer">
                            <?php if (!empty($myBlocks)): ?>
                                <?php foreach ($myBlocks as $idx => $block): 
                                    $blockId = (int)$block['id'];
                                    $files = $block['files'] ?? [];
                                    $filesCount = count($files);
                                    // Expand first 2 modules by default for immediate context
                                    $isDefaultOpen = ($idx < 2);
                                ?>
                                    <div class="accordion-item <?php echo $isDefaultOpen ? 'open' : ''; ?>" id="accordionItem-<?php echo $blockId; ?>" data-module-name="<?php echo htmlspecialchars(strtolower($block['block_name'])); ?>">
                                        <!-- Accordion Header -->
                                        <div class="accordion-header" tabindex="0" role="button" aria-expanded="<?php echo $isDefaultOpen ? 'true' : 'false'; ?>" onclick="toggleModuleAccordion(<?php echo $blockId; ?>)" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();toggleModuleAccordion(<?php echo $blockId; ?>);}">
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

                                            <div class="accordion-header-actions" onclick="event.stopPropagation();">
                                                <button type="button" class="btn-add-file-block" onclick="openUploadModal(<?php echo $blockId; ?>)" title="Attach document to this module">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                                    <span>Attach File</span>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Accordion Collapsible Content -->
                                        <div class="accordion-body" id="accordionBody-<?php echo $blockId; ?>">
                                            <div class="accordion-body-inner">
                                                <?php if (!empty($files)): ?>
                                                    <div class="files-grid">
                                                        <?php foreach ($files as $file): ?>
                                                            <div class="file-item">
                                                                <a href="<?php echo htmlspecialchars($file['file_path']); ?>" target="_blank" download class="file-info" title="Download <?php echo htmlspecialchars($file['file_name']); ?>">
                                                                    <div class="file-icon">
                                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                                                    </div>
                                                                    <span class="file-name"><?php echo htmlspecialchars($file['file_name']); ?></span>
                                                                </a>
                                                                <div class="file-actions">
                                                                    <a href="<?php echo htmlspecialchars($file['file_path']); ?>" target="_blank" download class="btn-download-icon" title="Download Material">
                                                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                                                    </a>
                                                                    <a href="../../controllers/teacher/manageCourseFiles.php?action=delete&file_id=<?php echo (int)$file['id']; ?>&course_id=<?php echo $courseId; ?>" onclick="return confirm('Remove \'<?php echo htmlspecialchars(addslashes($file['file_name'])); ?>\' from this module?');" class="btn-delete-file" title="Remove material">
                                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="empty-files-box">
                                                        <p>No study materials attached to this module yet.</p>
                                                        <button type="button" class="btn-inline-upload" onclick="openUploadModal(<?php echo $blockId; ?>)">
                                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                                            <span>Upload Learning Document</span>
                                                        </button>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state">No direct instruction modules assigned to your profile in this course.</div>
                            <?php endif; ?>
                        </div>

                        <!-- No modules match search empty feedback -->
                        <div id="noModulesMatchBox" class="empty-state" style="display: none;">
                            No syllabus modules match your search filter.
                        </div>
                    </section>
                </div>

                <!-- ========================================== -->
                <!-- TAB 2: ENROLLED STUDENTS (PAGINATED)       -->
                <!-- ========================================== -->
                <div id="tabContentStudents" class="tab-panel">
                    <section class="panel">
                        <!-- Toolbar for 50+ students scale -->
                        <div class="panel-toolbar">
                            <div class="toolbar-search-wrap">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                <input type="search" id="studentSearchInput" class="toolbar-search-input" placeholder="Search student by name, student ID or email..." oninput="filterStudents()">
                            </div>

                            <div class="toolbar-actions">
                                <select id="studentStatusFilter" class="toolbar-select" onchange="filterStudents()">
                                    <option value="">All Account Statuses</option>
                                    <option value="verified">Verified Only</option>
                                    <option value="pending">Pending Only</option>
                                </select>

                                <?php if (!empty($studentEmailsString)): ?>
                                    <a href="mailto:?bcc=<?php echo htmlspecialchars($studentEmailsString); ?>&subject=<?php echo urlencode('[' . $course['course_name'] . '] Notice from Instructor'); ?>" class="btn-outline-dark-action" title="Send email notice to all enrolled students">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                        <span>Email Class (BCC)</span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Students Table Wrap -->
                        <div class="table-wrap">
                            <?php if (!empty($students)): ?>
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
                                    <tbody id="studentsTableBody">
                                        <?php foreach ($students as $stu): ?>
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
                                            <tr class="student-row" 
                                                data-name="<?php echo htmlspecialchars(strtolower($fullName)); ?>" 
                                                data-id="<?php echo htmlspecialchars(strtolower($controlId)); ?>" 
                                                data-email="<?php echo htmlspecialchars(strtolower($email)); ?>" 
                                                data-status="<?php echo $isVerified ? 'verified' : 'pending'; ?>">
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
                                                    <a href="mailto:<?php echo htmlspecialchars($email); ?>?subject=<?php echo urlencode('[' . $course['course_name'] . '] Instructor Message'); ?>" class="btn-contact-student" title="Send individual email">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                                        <span>Contact</span>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="empty-state">No students are currently enrolled in this course group.</div>
                            <?php endif; ?>
                        </div>

                        <!-- No students match search empty feedback -->
                        <div id="noStudentsMatchBox" class="empty-state" style="display: none;">
                            No students match your search criteria.
                        </div>

                        <!-- Pagination Footer for 50+ students scale -->
                        <div class="pagination-footer" id="studentsPaginationFooter">
                            <span class="pagination-info" id="paginationInfo">Showing 1 to 10 of <?php echo count($students); ?> students</span>
                            <div class="pagination-controls">
                                <button type="button" class="btn-page" id="btnPrevPage" onclick="changeStudentPage(-1)" disabled>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                                    <span>Previous</span>
                                </button>
                                <span class="page-indicator" id="pageIndicator">Page 1 of 1</span>
                                <button type="button" class="btn-page" id="btnNextPage" onclick="changeStudentPage(1)">
                                    <span>Next</span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                                </button>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>

    <!-- Upload Material Modal -->
    <div class="modal-backdrop" id="uploadModal" onclick="if(event.target === this) closeUploadModal();">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3>Attach Learning Resource</h3>
                <button type="button" class="btn-close-modal" onclick="closeUploadModal()">&times;</button>
            </div>
            <form action="../../controllers/teacher/manageCourseFiles.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload">
                <input type="hidden" name="course_id" value="<?php echo $courseId; ?>">

                <div class="modal-body">
                    <div class="form-group">
                        <label for="blockSelect">Target Syllabus Module *</label>
                        <select name="block_id" id="blockSelect" class="form-control" required>
                            <?php foreach ($teacherBlocks as $tb): ?>
                                <option value="<?php echo (int)$tb['id']; ?>"><?php echo htmlspecialchars($tb['block_name']); ?></option>
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
                    <button type="button" class="btn-cancel-modal" onclick="closeUploadModal()">Cancel</button>
                    <button type="submit" class="btn-submit-modal">Upload & Publish</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Interactive Client Scripts for Scalability -->
    <script>
        /* ====================================================
           1. WORKSPACE TABS SWITCHING
           ==================================================== */
        function switchWorkspaceTab(tab) {
            const btnModules = document.getElementById('tabBtnModules');
            const btnStudents = document.getElementById('tabBtnStudents');
            const contentModules = document.getElementById('tabContentModules');
            const contentStudents = document.getElementById('tabContentStudents');

            if (tab === 'students') {
                btnModules.classList.remove('active');
                btnModules.setAttribute('aria-selected', 'false');
                btnStudents.classList.add('active');
                btnStudents.setAttribute('aria-selected', 'true');

                contentModules.classList.remove('active');
                contentStudents.classList.add('active');
                window.location.hash = 'students';
                filterStudents(); // Refresh pagination calculations
            } else {
                btnStudents.classList.remove('active');
                btnStudents.setAttribute('aria-selected', 'false');
                btnModules.classList.add('active');
                btnModules.setAttribute('aria-selected', 'true');

                contentStudents.classList.remove('active');
                contentModules.classList.add('active');
                window.location.hash = 'modules';
            }
        }

        /* ====================================================
           2. ACCORDION MODULES INTERACTION (20+ Scale)
           ==================================================== */
        function toggleModuleAccordion(blockId) {
            const item = document.getElementById('accordionItem-' + blockId);
            if (item) {
                item.classList.toggle('open');
            }
        }

        let allExpanded = false;
        function toggleAllModules() {
            const items = document.querySelectorAll('.accordion-item');
            const btnText = document.getElementById('toggleAllModulesText');
            allExpanded = !allExpanded;

            items.forEach(item => {
                if (allExpanded) {
                    item.classList.add('open');
                } else {
                    item.classList.remove('open');
                }
            });

            if (btnText) {
                btnText.textContent = allExpanded ? 'Collapse All' : 'Expand All';
            }
        }

        function filterModules() {
            const query = (document.getElementById('moduleSearchInput').value || '').toLowerCase().trim();
            const items = document.querySelectorAll('.accordion-item');
            let matchCount = 0;

            items.forEach(item => {
                const name = item.getAttribute('data-module-name') || '';
                if (!query || name.includes(query)) {
                    item.style.display = '';
                    matchCount++;
                    // If searching, automatically expand matching items
                    if (query) {
                        item.classList.add('open');
                    }
                } else {
                    item.style.display = 'none';
                }
            });

            const noMatchBox = document.getElementById('noModulesMatchBox');
            if (noMatchBox) {
                noMatchBox.style.display = (matchCount === 0 && items.length > 0) ? 'block' : 'none';
            }
        }

        /* ====================================================
           3. ENROLLED STUDENTS PAGINATION & FILTER (50+ Scale)
           ==================================================== */
        const ROWS_PER_PAGE = 10;
        let currentStudentPage = 1;
        let visibleStudentRows = [];

        function filterStudents() {
            const query = (document.getElementById('studentSearchInput').value || '').toLowerCase().trim();
            const statusFilter = (document.getElementById('studentStatusFilter').value || '').toLowerCase().trim();
            const allRows = Array.from(document.querySelectorAll('.student-row'));

            visibleStudentRows = allRows.filter(row => {
                const name = row.getAttribute('data-name') || '';
                const id = row.getAttribute('data-id') || '';
                const email = row.getAttribute('data-email') || '';
                const status = row.getAttribute('data-status') || '';

                const matchesQuery = (!query || name.includes(query) || id.includes(query) || email.includes(query));
                const matchesStatus = (!statusFilter || status === statusFilter);

                return matchesQuery && matchesStatus;
            });

            currentStudentPage = 1;
            renderStudentPagination();
        }

        function renderStudentPagination() {
            const allRows = document.querySelectorAll('.student-row');
            allRows.forEach(row => row.style.display = 'none');

            const total = visibleStudentRows.length;
            const totalPages = Math.max(1, Math.ceil(total / ROWS_PER_PAGE));

            if (currentStudentPage > totalPages) currentStudentPage = totalPages;
            if (currentStudentPage < 1) currentStudentPage = 1;

            const startIndex = (currentStudentPage - 1) * ROWS_PER_PAGE;
            const endIndex = Math.min(startIndex + ROWS_PER_PAGE, total);

            for (let i = startIndex; i < endIndex; i++) {
                if (visibleStudentRows[i]) {
                    visibleStudentRows[i].style.display = '';
                }
            }

            // Update UI Counters & Buttons
            const info = document.getElementById('paginationInfo');
            const pageIndicator = document.getElementById('pageIndicator');
            const btnPrev = document.getElementById('btnPrevPage');
            const btnNext = document.getElementById('btnNextPage');
            const noMatchBox = document.getElementById('noStudentsMatchBox');
            const paginationFooter = document.getElementById('studentsPaginationFooter');

            if (total === 0) {
                if (noMatchBox) noMatchBox.style.display = 'block';
                if (paginationFooter) paginationFooter.style.display = 'none';
            } else {
                if (noMatchBox) noMatchBox.style.display = 'none';
                if (paginationFooter) paginationFooter.style.display = 'flex';
                if (info) info.textContent = `Showing ${startIndex + 1} to ${endIndex} of ${total} students`;
                if (pageIndicator) pageIndicator.textContent = `Page ${currentStudentPage} of ${totalPages}`;
                if (btnPrev) btnPrev.disabled = (currentStudentPage === 1);
                if (btnNext) btnNext.disabled = (currentStudentPage >= totalPages);
            }
        }

        function changeStudentPage(offset) {
            currentStudentPage += offset;
            renderStudentPagination();
        }

        /* ====================================================
           4. MODAL DIALOG CONTROLS & ESCAPE LISTENER
           ==================================================== */
        function openUploadModal(blockId) {
            const modal = document.getElementById('uploadModal');
            if (blockId) {
                const select = document.getElementById('blockSelect');
                if (select) select.value = blockId;
            }
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            const titleInput = document.getElementById('fileTitle');
            if (titleInput) setTimeout(() => titleInput.focus(), 60);
        }

        function closeUploadModal() {
            const modal = document.getElementById('uploadModal');
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }

        // Global Escape key listener for accessible modal closing
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeUploadModal();
            }
        });

        // Initialize on DOM load
        document.addEventListener('DOMContentLoaded', () => {
            if (window.location.hash === '#students') {
                switchWorkspaceTab('students');
            } else {
                filterStudents();
            }
        });
    </script>
</body>
</html>
