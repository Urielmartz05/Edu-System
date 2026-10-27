<?php
    require_once __DIR__ . "/../../controllers/auth/check_auth.php";
    requireRole([4]);
    require_once __DIR__ . "/../../models/assignmentModels/assignmentModel.php";
    require_once __DIR__ . "/../../models/studentModels/studentEnrollmentModel.php";

    $user = getLoggedUser();
    $studentId = (int)($user['id'] ?? 0);

    $assignmentModel = new AssignmentModel();
    $enrollmentModel = new StudentEnrollment();

    // Retrieve active student assignments
    $statusFilter = trim($_GET['status'] ?? 'all');
    $courseFilter = isset($_GET['course_id']) && $_GET['course_id'] !== '' ? (int)$_GET['course_id'] : null;
    $selectedTaskId = isset($_GET['task_id']) ? (int)$_GET['task_id'] : null;
    $search = trim($_GET['search'] ?? '');

    $allAssignments = $assignmentModel->getStudentAssignments($studentId, $courseFilter);
    $enrolledCourses = $enrollmentModel->getEnrolledCourses($studentId);

    // Compute status counts for KPI badges
    $counts = [
        'all' => count($allAssignments),
        'pending' => 0,
        'submitted' => 0,
        'graded' => 0
    ];

    foreach ($allAssignments as $item) {
        $st = $item['task_state'] ?? $item['submission_status'] ?? 'pending';
        if ($st === 'overdue') $st = 'pending';
        if (isset($counts[$st])) {
            $counts[$st]++;
        }
    }

    // Filter assignments by status tab
    $filteredAssignments = array_filter($allAssignments, function($item) use ($statusFilter, $search) {
        $taskState = $item['task_state'] ?? $item['submission_status'] ?? 'pending';
        $matchStatus = true;
        if ($statusFilter !== 'all') {
            $matchStatus = ($taskState === $statusFilter) || ($statusFilter === 'pending' && $taskState === 'overdue');
        }

        $matchSearch = true;
        if (!empty($search)) {
            $courseName = $item['course_name'] ?? $item['course_title'] ?? '';
            $blockName = $item['block_name'] ?? $item['block_title'] ?? '';
            $matchSearch = (stripos($item['title'], $search) !== false)
                || (stripos($courseName, $search) !== false)
                || (stripos($blockName, $search) !== false);
        }

        return $matchStatus && $matchSearch;
    });

    // If a task is selected for viewing or submission, fetch details
    $selectedTask = null;
    $studentSubmission = null;
    if ($selectedTaskId > 0) {
        foreach ($allAssignments as $task) {
            if ((int)$task['id'] === $selectedTaskId) {
                $selectedTask = $task;
                $studentSubmission = $assignmentModel->getStudentSubmission($selectedTaskId, $studentId);
                break;
            }
        }

        // If not found in the course-filtered list, look across all student courses
        if (!$selectedTask && $courseFilter !== null) {
            $allStudentTasksUnfiltered = $assignmentModel->getStudentAssignments($studentId, null);
            foreach ($allStudentTasksUnfiltered as $task) {
                if ((int)$task['id'] === $selectedTaskId) {
                    $selectedTask = $task;
                    $studentSubmission = $assignmentModel->getStudentSubmission($selectedTaskId, $studentId);
                    break;
                }
            }
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
    <title>Coursework & Assignments · EduX</title>
    <link rel="stylesheet" href="alumnoAssignments.css">
</head>
<body>
    <?php include __DIR__ . '/../../../ui/header/header.php'; ?>
    <?php include __DIR__ . '/../../../components/studentSidebar/studentSidebar.php'; ?>

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
                        <span class="eyebrow">ACADEMIC WORKSPACE · STUDENT PORTAL</span>
                        <h1>Coursework & Assignments</h1>
                        <p class="subtitle">Review assigned deliverables, submit coursework, check deadlines, and access grading feedback.</p>
                    </div>
                </section>

                <!-- KPI Metrics Summary Grid -->
                <div class="metrics-grid">
                    <div class="metric-card">
                        <span class="metric-label">Total Assigned</span>
                        <span class="metric-value"><?php echo $counts['all']; ?></span>
                        <span class="metric-desc">Curricular tasks registered</span>
                    </div>
                    <div class="metric-card">
                        <span class="metric-label">Pending Submissions</span>
                        <span class="metric-value pending-val"><?php echo $counts['pending']; ?></span>
                        <span class="metric-desc">Awaiting your delivery</span>
                    </div>
                    <div class="metric-card">
                        <span class="metric-label">Delivered / In Review</span>
                        <span class="metric-value submitted-val"><?php echo $counts['submitted']; ?></span>
                        <span class="metric-desc">Submitted for evaluation</span>
                    </div>
                    <div class="metric-card">
                        <span class="metric-label">Evaluated / Graded</span>
                        <span class="metric-value graded-val"><?php echo $counts['graded']; ?></span>
                        <span class="metric-desc">With feedback and final score</span>
                    </div>
                </div>

                <!-- Detail / Submission Drawer Modal (If a task is selected) -->
                <?php if ($selectedTask): ?>
                    <section class="panel detail-panel">
                        <div class="panel-head">
                            <div>
                                <span class="module-chip"><?php echo htmlspecialchars($selectedTask['course_name'] ?? $selectedTask['course_title'] ?? ''); ?> · <?php echo htmlspecialchars($selectedTask['block_name'] ?? $selectedTask['block_title'] ?? ''); ?></span>
                                <h2><?php echo htmlspecialchars($selectedTask['title']); ?></h2>
                                <p>Instructor: <?php echo htmlspecialchars($selectedTask['teacher_name']); ?> | Due: <?php echo date('M d, Y · h:i A', strtotime($selectedTask['due_date'])); ?></p>
                            </div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <a href="../../controllers/alumno/selectCourse.php?course_id=<?php echo (int)$selectedTask['course_id']; ?>&amp;target=courses" class="btn-secondary-link" title="Jump to syllabus units">Course Syllabus &rarr;</a>
                                <a href="alumnoAssignments.php<?php echo $statusFilter !== 'all' ? '?status=' . urlencode($statusFilter) : ''; ?>" class="btn-secondary-link">Close Details</a>
                            </div>
                        </div>

                        <div class="task-detail-body">
                            <div class="instruction-box">
                                <h3>Task Instructions</h3>
                                <div class="task-description-text">
                                    <?php echo nl2br(htmlspecialchars($selectedTask['description'] ?: 'No additional description provided.')); ?>
                                </div>

                                <?php if (!empty($selectedTask['file_path'])): ?>
                                    <div class="attachment-attachment-box">
                                        <span class="att-title">Teacher Reference Material:</span>
                                        <a <?php echo resourceLinkAttributes($selectedTask['file_path'], 'assignment', $selectedTask['id']); ?> target="_blank" download class="file-download-link">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                            Download Prompt File
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Submission Status & Upload Form -->
                            <div class="submission-box">
                                <h3>Your Submission</h3>
                                <?php if ($studentSubmission): ?>
                                    <div class="current-submission-info">
                                        <div class="status-badge-row">
                                            <span class="status-badge badge-<?php echo htmlspecialchars($studentSubmission['status']); ?>">
                                                <?php echo ucfirst(htmlspecialchars($studentSubmission['status'])); ?>
                                            </span>
                                            <span class="submission-date">Submitted: <?php echo date('M d, Y · h:i A', strtotime($studentSubmission['submitted_at'])); ?></span>
                                        </div>

                                        <?php if (!empty($studentSubmission['submission_text'])): ?>
                                            <div class="submission-notes">
                                                <strong>Your Notes:</strong>
                                                <p><?php echo nl2br(htmlspecialchars($studentSubmission['submission_text'])); ?></p>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($studentSubmission['file_path'])): ?>
                                            <div class="submitted-file-row">
                                                <strong>Your File:</strong>
                                                <a <?php echo resourceLinkAttributes($studentSubmission['file_path'], 'submission', $studentSubmission['id']); ?> target="_blank" download class="file-download-link">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                                    View Uploaded Document
                                                </a>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($studentSubmission['status'] === 'graded'): ?>
                                            <div class="grading-feedback-box">
                                                <div class="score-display">
                                                    <span class="score-number"><?php echo htmlspecialchars($studentSubmission['score']); ?></span>
                                                    <span class="score-denom">/ <?php echo htmlspecialchars($selectedTask['max_score']); ?> pts</span>
                                                </div>
                                                <?php if (!empty($studentSubmission['feedback'])): ?>
                                                    <div class="feedback-text">
                                                        <strong>Teacher Feedback:</strong>
                                                        <p><?php echo nl2br(htmlspecialchars($studentSubmission['feedback'])); ?></p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- If not graded yet, show submission or resubmission form -->
                                <?php if (!$studentSubmission || $studentSubmission['status'] !== 'graded'): ?>
                                    <form method="POST" action="../../controllers/alumno/submitAssignment.php" enctype="multipart/form-data" class="submission-form"><?php echo csrfField(); ?>
                                        <input type="hidden" name="assignment_id" value="<?php echo $selectedTask['id']; ?>">

                                        <div class="form-group">
                                            <label for="submission_text">Deliverable Notes / Answers</label>
                                            <textarea id="submission_text" name="submission_text" rows="4" placeholder="Enter comments, summary or deliverable link..."><?php echo htmlspecialchars($studentSubmission['submission_text'] ?? ''); ?></textarea>
                                        </div>

                                        <div class="form-group">
                                            <label for="submission_file">Deliverable Document (PDF, ZIP, Office, Images - Max 25MB)</label>
                                            <input type="file" id="submission_file" name="submission_file" class="file-input">
                                        </div>

                                        <button type="submit" class="btn-action-primary">
                                            <?php echo $studentSubmission ? 'Update Submission' : 'Submit Coursework'; ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- Main Assignments List Panel -->
                <section class="panel">
                    <div class="panel-head">
                        <div>
                            <h2>Assigned Tasks</h2>
                            <p>Filter by submission status or select a specific course.</p>
                        </div>
                    </div>

                    <!-- Filter Toolbar (Pure PHP GET Forms) -->
                    <div class="panel-toolbar">
                        <div class="status-tabs">
                            <a href="alumnoAssignments.php?status=all<?php echo $courseFilter ? '&course_id='.$courseFilter : ''; ?>" class="tab-link <?php echo $statusFilter === 'all' ? 'active-tab' : ''; ?>">All (<?php echo $counts['all']; ?>)</a>
                            <a href="alumnoAssignments.php?status=pending<?php echo $courseFilter ? '&course_id='.$courseFilter : ''; ?>" class="tab-link <?php echo $statusFilter === 'pending' ? 'active-tab' : ''; ?>">Pending (<?php echo $counts['pending']; ?>)</a>
                            <a href="alumnoAssignments.php?status=submitted<?php echo $courseFilter ? '&course_id='.$courseFilter : ''; ?>" class="tab-link <?php echo $statusFilter === 'submitted' ? 'active-tab' : ''; ?>">Submitted (<?php echo $counts['submitted']; ?>)</a>
                            <a href="alumnoAssignments.php?status=graded<?php echo $courseFilter ? '&course_id='.$courseFilter : ''; ?>" class="tab-link <?php echo $statusFilter === 'graded' ? 'active-tab' : ''; ?>">Graded (<?php echo $counts['graded']; ?>)</a>
                        </div>

                        <form method="GET" action="alumnoAssignments.php" class="filter-form">
                            <input type="hidden" name="status" value="<?php echo htmlspecialchars($statusFilter); ?>">

                            <select name="course_id" class="select-control">
                                <option value="">All My Courses</option>
                                <?php foreach ($enrolledCourses as $course): ?>
                                    <option value="<?php echo $course['id']; ?>" <?php echo $courseFilter === (int)$course['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($course['course_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search task title..." class="search-input">
                            <button type="submit" class="btn-filter">Search</button>
                            <?php if (!empty($search) || !empty($courseFilter)): ?>
                                <a href="alumnoAssignments.php?status=<?php echo urlencode($statusFilter); ?>" class="btn-clear">Reset</a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <!-- Assignments Table Wrap -->
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Task Title & Course</th>
                                    <th>Syllabus Block</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Score</th>
                                    <th style="text-align: right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($filteredAssignments)): ?>
                                    <tr>
                                        <td colspan="6" class="empty-state-cell">
                                            <p>No assignments found matching the current filters.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($filteredAssignments as $task):
                                        $subStatus = $task['task_state'] ?? $task['submission_status'] ?? 'pending';
                                        $isOverdue = $subStatus === 'overdue';
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="task-title-cell">
                                                    <strong><?php echo htmlspecialchars($task['title']); ?></strong>
                                                    <span class="course-subtitle"><?php echo htmlspecialchars($task['course_name'] ?? $task['course_title'] ?? ''); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="block-badge"><?php echo htmlspecialchars($task['block_name'] ?? $task['block_title'] ?? ''); ?></span>
                                            </td>
                                            <td>
                                                <div class="due-cell <?php echo $isOverdue ? 'overdue-date' : ''; ?>">
                                                    <span><?php echo date('M d, Y', strtotime($task['due_date'])); ?></span>
                                                    <small><?php echo date('h:i A', strtotime($task['due_date'])); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="status-pill status-<?php echo htmlspecialchars($subStatus); ?>">
                                                    <?php echo ucfirst(htmlspecialchars($subStatus)); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($subStatus === 'graded' && $task['score'] !== null): ?>
                                                    <strong><?php echo htmlspecialchars($task['score']); ?></strong> / <?php echo htmlspecialchars($task['max_score']); ?>
                                                <?php else: ?>
                                                    <span class="na-text">-- / <?php echo htmlspecialchars($task['max_score']); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="text-align: right;">
                                                <a href="alumnoAssignments.php?task_id=<?php echo $task['id']; ?>&status=<?php echo urlencode($statusFilter); ?>" class="btn-row-action">
                                                    <?php echo in_array($subStatus, ['pending', 'overdue'], true) ? 'Submit Work' : 'View Details'; ?>
                                                </a>
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
</body>
</html>
