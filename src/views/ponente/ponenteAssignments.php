<?php
    require_once __DIR__ . "/../../controllers/auth/check_auth.php";
    requireRole([1, 3]);
    require_once __DIR__ . "/../../models/assignmentModels/assignmentModel.php";
    require_once __DIR__ . "/../../models/teacherModels/teacherCoursesModel.php";

    $user = getLoggedUser();
    $teacherId = (int)($user['id'] ?? 0);
    $userRole = (int)$user['role'];

    $assignmentModel = new AssignmentModel();
    $teacherCoursesModel = new TeacherCourses();

    $courseFilter = isset($_GET['course_id']) && $_GET['course_id'] !== '' ? (int)$_GET['course_id'] : null;
    $action = $_GET['action'] ?? '';
    $reviewId = isset($_GET['review_id']) ? (int)$_GET['review_id'] : null;
    $search = trim($_GET['search'] ?? '');

    // Teacher assigned courses
    $teacherCourses = $teacherCoursesModel->getCoursesWithDetails($teacherId);

    // Teacher assignments
    $assignments = $assignmentModel->getTeacherAssignments($teacherId, $courseFilter);

    // Apply search filter if active
    if (!empty($search)) {
        $assignments = array_filter($assignments, function($a) use ($search) {
            return (stripos($a['title'], $search) !== false)
                || (stripos($a['course_name'] ?? '', $search) !== false)
                || (stripos($a['block_name'] ?? '', $search) !== false);
        });
    }

    // KPI Metrics calculation
    $kpiTotalAssignments = count($assignments);
    $kpiTotalSubmissions = 0;
    $kpiPendingGrading = 0;

    foreach ($assignments as $a) {
        $kpiTotalSubmissions += (int)($a['total_submissions'] ?? 0);
        $pending = (int)($a['total_submissions'] ?? 0) - (int)($a['graded_submissions'] ?? 0);
        if ($pending > 0) {
            $kpiPendingGrading += $pending;
        }
    }

    // If review view is active
    $reviewAssignment = null;
    $submissions = [];
    if ($reviewId > 0) {
        $reviewAssignment = $assignmentModel->getAssignmentDetails($reviewId);
        if ($reviewAssignment && $userRole !== 1 && (int)$reviewAssignment['teacher_id'] !== $teacherId) {
            $reviewAssignment = null;
        }
        if ($reviewAssignment) {
            $submissions = $assignmentModel->getAssignmentSubmissions($reviewId);
        }
    }

    // If creation view is active, get blocks for selected course
    $selectedCourseBlocks = [];
    $selectedNewCourseId = isset($_GET['new_course_id']) ? (int)$_GET['new_course_id'] : (isset($_GET['course_id']) ? (int)$_GET['course_id'] : ($courseFilter ?: ($teacherCourses[0]['id'] ?? 0)));
    if ($action === 'new' && $selectedNewCourseId > 0) {
        $selectedCourseBlocks = $teacherCoursesModel->getCourseBlocksWithFiles($selectedNewCourseId, $teacherId);
        if ($userRole !== 1) {
            $selectedCourseBlocks = array_filter($selectedCourseBlocks, fn($block) => !empty($block['is_my_block']));
        }
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
    <title>Faculty Assignments & Submissions · Edu-System</title>
    <link rel="stylesheet" href="ponenteAssignments.css">
</head>
<body>
    <?php include __DIR__ . '/../../../ui/header/header.php'; ?>
    <?php include __DIR__ . '/../../../components/teacherSidebar/teacherSidebar.php'; ?>

    <div class="workspace">
        <main>
            <div id="page-content">

                <!-- Flash Alerts -->
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

                <!-- Page Heading Hero (Strictly Free of Buttons) -->
                <section class="page-heading">
                    <div>
                        <span class="eyebrow">FACULTY ACADEMIC MANAGEMENT · INSTRUCTOR PORTAL</span>
                        <h1>Assignments & Evaluation Hub</h1>
                        <p class="subtitle">Design academic coursework, assign module deliverables, inspect student submissions, and record academic evaluations.</p>
                    </div>
                </section>

                <!-- Faculty KPIs Summary Grid -->
                <div class="metrics-grid">
                    <div class="metric-card">
                        <span class="metric-label">Active Assignments</span>
                        <span class="metric-value"><?php echo $kpiTotalAssignments; ?></span>
                        <span class="metric-desc">Created across your curriculum</span>
                    </div>
                    <div class="metric-card">
                        <span class="metric-label">Submissions Received</span>
                        <span class="metric-value submitted-val"><?php echo $kpiTotalSubmissions; ?></span>
                        <span class="metric-desc">Delivered by enrolled students</span>
                    </div>
                    <div class="metric-card">
                        <span class="metric-label">Pending Evaluation</span>
                        <span class="metric-value pending-val"><?php echo $kpiPendingGrading; ?></span>
                        <span class="metric-desc">Submissions awaiting grading</span>
                    </div>
                </div>

                <!-- Assignment Creation Panel (Triggered via ?action=new) -->
                <?php if ($action === 'new'): ?>
                    <section class="panel form-panel">
                        <div class="panel-head">
                            <div>
                                <h2>Create New Coursework Assignment</h2>
                                <p>Provide title, instructions, due date, and assign to one of your course modules.</p>
                            </div>
                            <a href="ponenteAssignments.php" class="btn-secondary-link">Cancel</a>
                        </div>

                        <!-- Course selector to refresh available blocks -->
                        <form method="GET" action="ponenteAssignments.php" class="module-selector-bar">
                            <input type="hidden" name="action" value="new">
                            <label for="new_course_id">Target Course:</label>
                            <select id="new_course_id" name="new_course_id" class="select-control">
                                <?php foreach ($teacherCourses as $c): ?>
                                    <option value="<?php echo $c['id']; ?>" <?php echo $selectedNewCourseId === (int)$c['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c['course_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn-filter">Select Course</button>
                        </form>

                        <form method="POST" action="../../controllers/teacher/manageAssignments.php" enctype="multipart/form-data" class="assignment-create-form"><?php echo csrfField(); ?>
                            <input type="hidden" name="action" value="create">
                            <input type="hidden" name="course_id" value="<?php echo $selectedNewCourseId; ?>">

                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label for="block_id">Course Module / Syllabus Block *</label>
                                    <select id="block_id" name="block_id" class="select-control" required>
                                        <option value="">-- Choose Module --</option>
                                        <?php foreach ($selectedCourseBlocks as $block): ?>
                                            <option value="<?php echo $block['id']; ?>">
                                                <?php echo htmlspecialchars($block['block_name']); ?> <?php echo !empty($block['is_my_block']) ? '(My Module)' : ''; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="title">Assignment Title *</label>
                                    <input type="text" id="title" name="title" required placeholder="e.g. Midterm Research Essay" class="text-input">
                                </div>
                            </div>

                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label for="max_score">Maximum Score (Points) *</label>
                                    <input type="number" id="max_score" name="max_score" step="0.01" min="1" max="999.99" value="100.00" required class="text-input">
                                </div>

                                <div class="form-group">
                                    <label for="due_date">Due Date and Time *</label>
                                    <input type="datetime-local" id="due_date" name="due_date" required class="text-input">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="description">Instructions and Evaluation Guidelines</label>
                                <textarea id="description" name="description" rows="5" placeholder="Detail the objectives, submission requirements, format, and criteria..." class="textarea-input"></textarea>
                            </div>

                            <div class="form-group">
                                <label for="attachment">Attach Prompt / Rubric Document (Optional - Max 25MB)</label>
                                <div class="file-upload-box" style="border:2px dashed #cbd5e1; border-radius:10px; padding:14px; background:#f8fafc; transition:all 0.2s ease;">
                                    <input type="file" id="attachment" name="attachment" class="file-input" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.zip,.rar,.png,.jpg,.jpeg" style="display:block; width:100%; cursor:pointer;">
                                    <div id="file-selected-status" style="display:none; margin-top:8px; padding:6px 12px; background:#f0fdf4; border:1px solid #86efac; border-radius:6px; font-size:12px; color:#166534; align-items:center; gap:6px;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                        <span><strong>Selected:</strong> <span id="file-name-display"></span> (<span id="file-size-display"></span>)</span>
                                    </div>
                                    <p style="font-size:11.5px; color:#64748b; margin-top:6px; margin-bottom:0;">Supported formats: PDF, Word, PowerPoint, Excel, Images, ZIP (max 25MB).</p>
                                </div>
                            </div>

                            <div class="form-actions-row">
                                <button type="submit" class="btn-action-primary">Publish Assignment</button>
                                <a href="ponenteAssignments.php" class="btn-cancel">Discard</a>
                            </div>
                        </form>
                    </section>
                <?php endif; ?>

                <!-- Review and Grading Panel (Triggered via ?review_id=X) -->
                <?php if ($reviewAssignment): ?>
                    <section class="panel review-panel">
                        <div class="panel-head">
                            <div>
                                <span class="module-chip"><?php echo htmlspecialchars($reviewAssignment['course_name'] ?? ''); ?> · <?php echo htmlspecialchars($reviewAssignment['block_name'] ?? ''); ?></span>
                                <h2>Review Submissions: <?php echo htmlspecialchars($reviewAssignment['title']); ?></h2>
                                <p>Maximum Score: <?php echo number_format($reviewAssignment['max_score'], 1); ?> pts | Due Date: <?php echo date('M d, Y · h:i A', strtotime($reviewAssignment['due_date'])); ?></p>
                                <?php if (!empty($reviewAssignment['file_path'])): ?>
                                    <div style="margin-top:10px;">
                                        <a <?php echo resourceLinkAttributes($reviewAssignment['file_path'], 'assignment', $reviewAssignment['id']); ?> target="_blank" download class="has-attachment-badge" style="font-size:12px; padding:5px 12px; text-decoration:none;">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                            <span>Download Attached Prompt (<?php echo htmlspecialchars(strtoupper(pathinfo($reviewAssignment['file_path'], PATHINFO_EXTENSION)) ?: 'PDF'); ?>)</span>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <a href="ponenteAssignments.php" class="btn-secondary-link">Back to Assignments</a>
                        </div>

                        <div class="table-wrap">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Delivered Date</th>
                                        <th>Student Notes</th>
                                        <th>Deliverable File</th>
                                        <th>Grade & Feedback</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($submissions)): ?>
                                        <tr>
                                            <td colspan="5" class="empty-state-cell">
                                                <p>No student submissions received for this assignment yet.</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($submissions as $sub):
                                            $isGraded = ($sub['status'] === 'graded');
                                        ?>
                                            <tr id="<?php echo $sub['submission_id'] !== null ? 'sub_' . (int)$sub['submission_id'] : 'student_' . (int)$sub['student_id']; ?>">
                                                <td>
                                                    <div class="student-name-cell">
                                                        <strong><?php echo htmlspecialchars($sub['student_name']); ?></strong>
                                                        <span class="student-email"><?php echo htmlspecialchars($sub['student_email']); ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="delivery-date"><?php echo $sub['submitted_at'] ? date('M d, Y · h:i A', strtotime($sub['submitted_at'])) : 'Not submitted'; ?></span>
                                                    <span class="status-pill status-<?php echo htmlspecialchars($sub['status'] ?? 'pending'); ?>">
                                                        <?php echo ucfirst(htmlspecialchars($sub['status'] ?? 'pending')); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if (!empty($sub['submission_text'])): ?>
                                                        <div class="notes-preview"><?php echo nl2br(htmlspecialchars($sub['submission_text'])); ?></div>
                                                    <?php else: ?>
                                                        <span class="na-label">No notes</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($sub['file_path'])): ?>
                                                        <a <?php echo resourceLinkAttributes($sub['file_path'], 'submission', $sub['submission_id']); ?> target="_blank" download class="file-download-link">
                                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                                            Download Work
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="na-label">No file</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="grade-action-col">
                                                    <!-- Single grading form per student / submission -->
                                                    <form method="POST" action="../../controllers/teacher/manageAssignments.php" class="grade-inline-form"><?php echo csrfField(); ?>
                                                        <input type="hidden" name="action" value="grade">
                                                        <input type="hidden" name="submission_id" value="<?php echo $sub['submission_id'] !== null ? (int)$sub['submission_id'] : 0; ?>">
                                                        <input type="hidden" name="student_id" value="<?php echo (int)$sub['student_id']; ?>">
                                                        <input type="hidden" name="assignment_id" value="<?php echo (int)$reviewAssignment['id']; ?>">

                                                        <div class="score-input-wrap">
                                                            <label for="score_<?php echo (int)$sub['student_id']; ?>">Score (Max: <?php echo $reviewAssignment['max_score']; ?>):</label>
                                                            <input type="number" id="score_<?php echo (int)$sub['student_id']; ?>" name="score" step="0.5" min="0" max="<?php echo $reviewAssignment['max_score']; ?>" value="<?php echo $sub['score'] !== null ? htmlspecialchars($sub['score']) : ''; ?>" required class="inline-score-input" placeholder="0 - <?php echo $reviewAssignment['max_score']; ?>">
                                                        </div>

                                                        <textarea name="feedback" rows="2" placeholder="Write feedback notes for the student..." class="inline-feedback-input"><?php echo htmlspecialchars($sub['feedback'] ?? ''); ?></textarea>

                                                        <button type="submit" class="btn-save-grade">
                                                            <?php echo $isGraded ? 'Update Grade' : 'Assign Grade'; ?>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- Assignments Overview Panel -->
                <section class="panel">
                    <div class="panel-head">
                        <div>
                            <h2>Faculty Coursework Directory</h2>
                            <p>All assignments created across your assigned curricular modules.</p>
                        </div>
                    </div>

                    <!-- Panel Toolbar with Creation Action and Filters -->
                    <div class="panel-toolbar">
                        <a href="ponenteAssignments.php?action=new<?php echo $courseFilter ? '&new_course_id='.$courseFilter : ''; ?>" class="btn-action-primary">
                            + Create Assignment
                        </a>

                        <form method="GET" action="ponenteAssignments.php" class="filter-form">
                            <select name="course_id" class="select-control">
                                <option value="">All My Courses</option>
                                <?php foreach ($teacherCourses as $c): ?>
                                    <option value="<?php echo $c['id']; ?>" <?php echo $courseFilter === (int)$c['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c['course_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search title or module..." class="search-input">
                            <button type="submit" class="btn-filter">Filter</button>
                            <?php if (!empty($search) || !empty($courseFilter)): ?>
                                <a href="ponenteAssignments.php" class="btn-clear">Reset</a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <!-- Assignments Table Wrap -->
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Assignment Title</th>
                                    <th>Course & Module</th>
                                    <th>Due Date</th>
                                    <th>Max Score</th>
                                    <th>Submissions</th>
                                    <th>Graded</th>
                                    <th style="text-align: right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($assignments)): ?>
                                    <tr>
                                        <td colspan="7" class="empty-state-cell">
                                            <p>No coursework assignments found. Click "+ Create Assignment" to publish your first deliverable.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($assignments as $a):
                                        $pendingGrading = (int)($a['total_submissions'] ?? 0) - (int)($a['graded_submissions'] ?? 0);
                                        $isPastDue = (strtotime($a['due_date']) < time());
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="task-title-cell">
                                                    <strong><?php echo htmlspecialchars($a['title']); ?></strong>
                                                    <?php if (!empty($a['file_path'])): ?>
                                                        <a <?php echo resourceLinkAttributes($a['file_path'], 'assignment', $a['id']); ?> target="_blank" download class="has-attachment-badge" title="Click to download and view prompt file">
                                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                                            <span>Attached Prompt (<?php echo htmlspecialchars(strtoupper(pathinfo($a['file_path'], PATHINFO_EXTENSION)) ?: 'PDF'); ?>)</span>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="course-cell">
                                                    <strong><?php echo htmlspecialchars($a['course_name'] ?? ''); ?></strong>
                                                    <span class="block-subtitle"><?php echo htmlspecialchars($a['block_name'] ?? ''); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="due-cell <?php echo $isPastDue ? 'past-due' : ''; ?>">
                                                    <span><?php echo date('M d, Y', strtotime($a['due_date'])); ?></span>
                                                    <small><?php echo date('h:i A', strtotime($a['due_date'])); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <strong><?php echo number_format($a['max_score'], 1); ?></strong> pts
                                            </td>
                                            <td>
                                                <span class="submission-count-pill">
                                                    <?php echo (int)($a['total_submissions'] ?? 0); ?> / <?php echo (int)($a['total_enrolled_students'] ?? 0); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($pendingGrading > 0): ?>
                                                    <span class="badge-pending-grad"><?php echo $pendingGrading; ?> Pending</span>
                                                <?php else: ?>
                                                    <span class="badge-all-graded">All Graded</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="text-align: right;">
                                                <div class="row-actions-group">
                                                    <a href="ponenteAssignments.php?review_id=<?php echo $a['id']; ?>" class="btn-review-action">
                                                        Review Submissions
                                                    </a>
                                                    <form method="POST" action="../../controllers/teacher/manageAssignments.php?action=delete&assignment_id=<?php echo $a['id']; ?>&course_id=<?php echo $a['course_id']; ?>" style="display:inline;"><?php echo csrfField(); ?><button type="submit"  class="btn-delete-link">
                                                        Delete
                                                    </button></form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

            </div>
        </main>
    </div>
    <script>
        const fileInput = document.getElementById('attachment');
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                const statusDiv = document.getElementById('file-selected-status');
                const nameDisplay = document.getElementById('file-name-display');
                const sizeDisplay = document.getElementById('file-size-display');
                if (this.files && this.files.length > 0) {
                    const file = this.files[0];
                    const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
                    if (file.size > 25 * 1024 * 1024) {
                        alert('File exceeds the 25MB size limit.');
                        this.value = '';
                        if (statusDiv) statusDiv.style.display = 'none';
                        return;
                    }
                    if (nameDisplay) nameDisplay.textContent = file.name;
                    if (sizeDisplay) sizeDisplay.textContent = sizeMb + ' MB';
                    if (statusDiv) statusDiv.style.display = 'inline-flex';
                } else {
                    if (statusDiv) statusDiv.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
