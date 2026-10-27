<?php
    require_once __DIR__ . '/../../controllers/auth/check_auth.php';
    requireRole([1]);
    require_once __DIR__ . '/../../config/connection.php';

    require_once __DIR__ . '/../../models/adminModels/adminCoursesModel.php';

    // Fetch existing catalog options for realistic selects
    $departments = [];
    $teachers = [];
    $courses = [];
    $departmentsWithStats = [];
    $sectionToDelete = null;

    try {
        $conn = createConnection();
        $adminCoursesModel = new AdminCourses($conn);

        $departmentsWithStats = $adminCoursesModel->getDepartmentsWithStats();
        $allDirectors = $adminCoursesModel->getAllDirectors();

        $deptRes = $conn->query("SELECT id, department FROM departments ORDER BY department ASC");
        if ($deptRes) {
            while ($row = $deptRes->fetch_assoc()) {
                $departments[] = $row;
            }
        }

        $teacherRes = $conn->query("SELECT u.id, u.name, u.lastname, u.email FROM teacher t JOIN users u ON t.id = u.id ORDER BY u.name ASC");
        if ($teacherRes) {
            while ($row = $teacherRes->fetch_assoc()) {
                $teachers[] = $row;
            }
        }

        $courseRes = $conn->query("SELECT c.id, c.course_name, COALESCE(d.department, '') AS department_name FROM course c LEFT JOIN departments d ON d.id = c.department_id ORDER BY c.course_name ASC");
        if ($courseRes) {
            while ($row = $courseRes->fetch_assoc()) {
                $courses[] = $row;
            }
        }

        $confirmDeleteSectionId = (int)($_GET['confirm_delete_section'] ?? 0);
        if ($confirmDeleteSectionId > 0) {
            $sectionToDelete = $adminCoursesModel->getDepartmentById($confirmDeleteSectionId);
            if ($sectionToDelete) {
                $sCourses = $adminCoursesModel->getCoursesByDepartmentId($sectionToDelete['id']);
                $sCourseCount = count($sCourses);
            }
        }
    } catch (Exception $e) {
        // Fallback gracefully if db error
    }

    $flashSuccess = $_SESSION['flash_success'] ?? null;
    $flashError = $_SESSION['flash_error'] ?? null;
    unset($_SESSION['flash_success'], $_SESSION['flash_error']);

    $initialTab = htmlspecialchars($_GET['tab'] ?? 'course');
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Administrative Provisioning Hub · Edu-System</title>
        <link rel="stylesheet" href="adminCreate.css">
    </head>
    <body>
        <?php include __DIR__ . '/../../../ui/header/header.php'; ?>
        <?php include __DIR__ . '/../../../ui/adminSideBar/adminSideBar.php'; ?>

        <div class="workspace">
            <main>
                <div id="page-content">

                    <!-- Page Heading -->
                    <section class="page-heading">
                        <div class="page-heading-content">
                            <p class="eyebrow">INSTITUTIONAL PROVISIONING</p>
                            <h1>Creation & Registration Hub</h1>
                            <p class="subtitle">Enroll students, configure academic syllabus offerings, appoint faculty instructors, or provision system administrators with role-specific security policies.</p>
                        </div>
                    </section>

                    <!-- Flash Feedback Alerts -->
                    <?php if ($flashSuccess): ?>
                        <div class="notice-banner" style="background-color: #f0fdf4; border-color: #86efac; color: #15803d; margin-bottom: 24px;">
                            <div class="notice-body">
                                <svg class="notice-icon" style="color: #16a34a; stroke: #16a34a;" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                <div>
                                    <strong>Operation Successful:</strong> <?php echo htmlspecialchars($flashSuccess); ?>
                                </div>
                            </div>
                            <span class="notice-badge" style="background-color: #dcfce7; color: #15803d; border-color: #bbf7d0;">Database Written</span>
                        </div>
                    <?php elseif ($flashError): ?>
                        <div class="notice-banner" style="background-color: #fef2f2; border-color: #fecdd3; color: #b91c1c; margin-bottom: 24px;">
                            <div class="notice-body">
                                <svg class="notice-icon" style="color: #dc2626; stroke: #dc2626;" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="8" x2="12" y2="12"></line>
                                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                </svg>
                                <div>
                                    <strong>Submission Error:</strong> <?php echo htmlspecialchars($flashError); ?>
                                </div>
                            </div>
                            <span class="notice-badge" style="background-color: #fee2e2; color: #991b1b; border-color: #fca5a5;">Validation Failed</span>
                        </div>
                    <?php endif; ?>

                    <!-- Executive Segmented Tab Navigation (Pure PHP Links) -->
                    <div class="tab-control-wrap" role="tablist">
                        <a href="adminCreate.php?tab=course" class="tab-btn <?php echo ($initialTab === 'course') ? 'active' : ''; ?>" style="text-decoration:none;" role="tab">
                            <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                            </svg>
                            <span>Academic Course</span>
                        </a>
                        <a href="adminCreate.php?tab=section" class="tab-btn <?php echo ($initialTab === 'section') ? 'active' : ''; ?>" style="text-decoration:none;" role="tab">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            </svg>
                            <span>Sections / Categories</span>
                        </a>
                        <a href="adminCreate.php?tab=director" class="tab-btn <?php echo ($initialTab === 'director') ? 'active' : ''; ?>" style="text-decoration:none;" role="tab">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            <span>Academic Director</span>
                        </a>
                        <a href="adminCreate.php?tab=student" class="tab-btn <?php echo ($initialTab === 'student') ? 'active' : ''; ?>" style="text-decoration:none;" role="tab">
                            <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                                <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                            </svg>
                            <span>Student Enrollment</span>
                        </a>
                        <a href="adminCreate.php?tab=teacher" class="tab-btn <?php echo ($initialTab === 'teacher') ? 'active' : ''; ?>" style="text-decoration:none;" role="tab">
                            <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="8.5" cy="7" r="4"></circle>
                                <polyline points="17 11 19 13 23 9"></polyline>
                            </svg>
                            <span>Faculty / Ponente</span>
                        </a>
                        <a href="adminCreate.php?tab=admin" class="tab-btn <?php echo ($initialTab === 'admin') ? 'active' : ''; ?>" style="text-decoration:none;" role="tab">
                            <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                            <span>Administrator</span>
                        </a>
                    </div>

                    <!-- ============================================== -->
                    <!-- TAB 1: CREATE COURSE -->
                    <!-- ============================================== -->
                    <section class="tab-content <?php echo ($initialTab === 'course') ? 'active' : ''; ?>" id="tab-course" role="tabpanel">
                        <div class="card-panel">
                            <div class="card-panel-header">
                                <div class="card-panel-header-left">
                                    <div class="card-panel-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h2>Course Curriculum Specification</h2>
                                        <p>Establish syllabus nomenclature, departmental alignment, and publication readiness.</p>
                                    </div>
                                </div>
                            </div>
                            <form class="card-panel-body" id="form-course" action="../../controllers/admin/creationProcess.php" method="POST"><?php echo csrfField(); ?>
                                <input type="hidden" name="action" value="course">
                                <div class="form-section-header">
                                    <span class="form-section-title">01. Curriculum Identification</span>
                                    <span class="form-section-subtitle">Official registrar naming conventions</span>
                                </div>
                                <div class="form-grid">
                                    <div class="form-group full-width">
                                        <label for="course_name">
                                            <span class="label-title">Course Title <span class="label-required">*</span></span>
                                            <span class="label-hint">Max 60 characters</span>
                                        </label>
                                        <input class="field" type="text" id="course_name" name="course_name" placeholder="e.g. Advanced Distributed Systems Architecture" maxlength="60" required>
                                        <span class="field-caption">Official title displayed across catalogs, academic transcripts, and student schedules.</span>
                                    </div>

                                    <div class="form-group">
                                        <label for="course_dept">
                                            <span class="label-title">Department / Division <span class="label-required">*</span></span>
                                        </label>
                                        <select id="course_dept" name="department_id" required>
                                            <option value="">Select academic division...</option>
                                            <?php if (!empty($departments)): ?>
                                                <?php foreach ($departments as $dept): ?>
                                                    <option value="<?php echo (int)$dept['id']; ?>"><?php echo htmlspecialchars($dept['department']); ?></option>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <option value="1">Computer Science & Engineering</option>
                                                <option value="2">Information Systems</option>
                                                <option value="3">Applied Sciences</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="course_credits">
                                            <span class="label-title">Academic Credits <span class="label-required">*</span></span>
                                            <span class="label-hint">ECTS / Credit Units</span>
                                        </label>
                                        <input class="field" type="number" id="course_credits" name="credits" min="1" max="15" value="4" required>
                                        <span class="field-caption">Standard institutional courses assign between 2 and 6 credits.</span>
                                    </div>
                                </div>

                                <div class="form-section-header bordered">
                                    <span class="form-section-title">02. Faculty Assignment</span>
                                    <span class="form-section-subtitle">Instructional responsibility</span>
                                </div>
                                <div class="form-grid">
                                    <div class="form-group full-width">
                                        <label for="course_teacher">
                                            <span class="label-title">Appointed Instructor</span>
                                            <span class="label-hint">Faculty Member</span>
                                        </label>
                                        <select id="course_teacher" name="teacher_id">
                                            <option value="">Assign instructor at a later date (Unassigned)...</option>
                                            <?php if (!empty($teachers)): ?>
                                                <?php foreach ($teachers as $t): ?>
                                                    <option value="<?php echo (int)$t['id']; ?>">
                                                        <?php echo htmlspecialchars($t['name'] . ' ' . ($t['lastname'] ?? '')); ?> (<?php echo htmlspecialchars($t['email']); ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <option value="1">Dr. Alan Turing — Faculty Lead</option>
                                                <option value="2">Prof. Ada Lovelace — Department Head</option>
                                            <?php endif; ?>
                                        </select>
                                        <span class="field-caption">Appointed faculty member inherits block creation and grading permissions.</span>
                                    </div>
                                    <input type="hidden" name="course_status" value="1">


                                </div>

                                <div class="form-actions-bar">
                                    <button type="reset" class="btn-secondary">Reset Form</button>
                                    <button type="submit" class="btn-primary">
                                        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="12" y1="5" x2="12" y2="19"></line>
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                        </svg>
                                        <span>Create Course Offering</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </section>

                    <!-- ============================================== -->
                    <!-- TAB: CREATE & MANAGE SECTIONS / CATEGORIES -->
                    <!-- ============================================== -->
                    <section class="tab-content <?php echo ($initialTab === 'section') ? 'active' : ''; ?>" id="tab-section" role="tabpanel">

                        <?php if ($sectionToDelete): 
                            $otherSections = array_filter($departmentsWithStats, fn($x) => (int)$x['id'] !== (int)$sectionToDelete['id']);
                        ?>
                            <div class="confirm-delete-box" style="background-color: #fff1f2; border: 1px solid #fecdd3; border-radius: 12px; padding: 20px 24px; margin-bottom: 26px; display:block;">
                                <div style="display:flex; align-items:flex-start; gap:12px; margin-bottom:14px;">
                                    <svg style="width:24px; height:24px; stroke:#dc2626; flex-shrink:0; margin-top:2px;" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="8" x2="12" y2="12"></line>
                                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                    </svg>
                                    <div>
                                        <h3 style="font-size:16px; font-weight:700; color:#9f1239; margin:0 0 4px 0;">Delete Section: "<?php echo htmlspecialchars($sectionToDelete['department']); ?>"</h3>
                                        <?php if (($sCourseCount ?? 0) > 0): ?>
                                            <p style="font-size:13px; color:#475569; margin:0;">
                                                This section currently contains <strong><?php echo (int)$sCourseCount; ?> course(s)</strong>. Please choose how you want to handle these courses before proceeding:
                                            </p>
                                        <?php else: ?>
                                            <p style="font-size:13px; color:#475569; margin:0;">
                                                This section has no active courses. Confirm deletion below to permanently remove it from the catalog.
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <form method="POST" action="../../controllers/admin/creationProcess.php">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="action" value="delete_section">
                                    <input type="hidden" name="department_id" value="<?php echo (int)$sectionToDelete['id']; ?>">

                                    <?php if (($sCourseCount ?? 0) > 0): ?>
                                        <div style="background:#ffffff; border:1px solid #fed7aa; border-radius:10px; padding:16px; margin-bottom:18px;">
                                            <div style="margin-bottom:14px;">
                                                <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; color:#0f172a; cursor:pointer;">
                                                    <input type="radio" name="delete_mode" value="reassign" checked onchange="document.getElementById('reassign-target-wrap').style.display='block';">
                                                    <span>Reassign <?php echo (int)$sCourseCount; ?> course(s) to another section (Recommended)</span>
                                                </label>
                                                <?php if (!empty($otherSections)): ?>
                                                    <div id="reassign-target-wrap" style="margin-top:8px; margin-left:24px;">
                                                        <select name="reassign_to_id" class="field" style="max-width:340px; padding:8px 12px; font-size:13px; border:1px solid #cbd5e1; border-radius:6px;">
                                                            <?php foreach ($otherSections as $os): ?>
                                                                <option value="<?php echo (int)$os['id']; ?>">
                                                                    Move to: <?php echo htmlspecialchars($os['department']); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                <?php else: ?>
                                                    <p style="font-size:12px; color:#dc2626; margin:6px 0 0 24px;">No other sections exist. Create another section first to reassign courses.</p>
                                                <?php endif; ?>
                                            </div>

                                            <div>
                                                <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; color:#dc2626; cursor:pointer;">
                                                    <input type="radio" name="delete_mode" value="cascade" onchange="document.getElementById('reassign-target-wrap').style.display='none';">
                                                    <span>Permanently delete all <?php echo (int)$sCourseCount; ?> course(s) and their modules in this section</span>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div style="display:flex; justify-content:flex-end; gap:10px; align-items:center;">
                                        <a href="adminCreate.php?tab=section" class="btn-secondary" style="padding:8px 16px; text-decoration:none; border-radius:8px; font-size:13px; font-weight:600;">Cancel</a>
                                        <button type="submit" class="btn-primary" style="background:#dc2626; border-color:#dc2626; padding:8px 18px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">
                                            Confirm & Delete Section
                                        </button>
                                    </div>
                                </form>
                            </div>
                        <?php endif; ?>

                        <div class="card-panel">
                            <div class="card-panel-header">
                                <div class="card-panel-header-left">
                                    <div class="card-panel-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="3" width="7" height="7"></rect>
                                            <rect x="14" y="3" width="7" height="7"></rect>
                                            <rect x="14" y="14" width="7" height="7"></rect>
                                            <rect x="3" y="14" width="7" height="7"></rect>
                                        </svg>
                                    </div>
                                    <div>
                                        <h2>Section & Category Classification</h2>
                                        <p>Create categories (e.g. Technology, Languages, Computer Science, Business, etc.) to organize courses into distinct catalog divisions.</p>
                                    </div>
                                </div>
                            </div>
                            <form class="card-panel-body" id="form-section" action="../../controllers/admin/creationProcess.php" method="POST"><?php echo csrfField(); ?>
                                <input type="hidden" name="action" value="section">
                                <div class="form-section-header">
                                    <span class="form-section-title">01. Section Details</span>
                                    <span class="form-section-subtitle">Academic category identifier</span>
                                </div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="department_name">
                                            <span class="label-title">Section / Category Name <span class="label-required">*</span></span>
                                            <span class="label-hint">Max 30 characters</span>
                                        </label>
                                        <input class="field" type="text" id="department_name" name="department_name" placeholder="e.g. Technology, Languages, Computer Science, Business..." maxlength="30" required>
                                        <span class="field-caption">Appears across course creation forms, catalogs, and student enrollment filters.</span>
                                    </div>

                                    <div class="form-group">
                                        <label for="section_director">
                                            <span class="label-title">Appointed Director</span>
                                            <span class="label-hint">Optional</span>
                                        </label>
                                        <select id="section_director" name="director_id">
                                            <option value="">Leave unassigned (assign later)...</option>
                                            <?php if (!empty($allDirectors)): ?>
                                                <?php foreach ($allDirectors as $dir): ?>
                                                    <option value="<?php echo (int)$dir['id']; ?>">
                                                        <?php echo htmlspecialchars($dir['name'] . ' ' . $dir['lastname']); ?> (<?php echo htmlspecialchars($dir['email']); ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                        <span class="field-caption">Supervises courses and instructors belonging to this academic division.</span>
                                    </div>
                                </div>

                                <div class="form-actions-bar">
                                    <button type="reset" class="btn-secondary">Reset Form</button>
                                    <button type="submit" class="btn-primary">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="12" y1="5" x2="12" y2="19"></line>
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                        </svg>
                                        <span>Create Section</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Existing Sections List Card -->
                        <div class="card-panel" style="margin-top: 24px;">
                            <div class="card-panel-header">
                                <div class="card-panel-header-left">
                                    <div class="card-panel-icon" style="background-color: #f1f5f9; color: #475569;">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="8" y1="6" x2="21" y2="6"></line>
                                            <line x1="8" y1="12" x2="21" y2="12"></line>
                                            <line x1="8" y1="18" x2="21" y2="18"></line>
                                            <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                            <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                            <line x1="3" y1="18" x2="3.01" y2="18"></line>
                                        </svg>
                                    </div>
                                    <div>
                                        <h2>Registered Sections & Academic Leadership</h2>
                                        <p>Manage existing classifications, review course density, and assign Academic Directors.</p>
                                    </div>
                                </div>
                                <span class="section-count-badge">
                                    <?php echo count($departmentsWithStats); ?> Total Sections
                                </span>
                            </div>
                            <div class="card-panel-body" style="padding: 0;">
                                <div class="sections-table-wrap">
                                    <table class="sections-table">
                                        <thead>
                                            <tr>
                                                <th scope="col" style="width: 28%;">SECTION / CATEGORY</th>
                                                <th scope="col" style="width: 18%;">COURSES</th>
                                                <th scope="col" style="width: 30%;">APPOINTED DIRECTOR</th>
                                                <th scope="col" style="width: 12%;">STATUS</th>
                                                <th scope="col" style="width: 12%; text-align: right;">ACTION</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($departmentsWithStats)): ?>
                                                <?php foreach ($departmentsWithStats as $d): 
                                                    $courseCount = (int)($d['course_count'] ?? 0);
                                                    $dirCount = (int)($d['director_count'] ?? 0);
                                                    $isProtected = ($courseCount > 0 || $dirCount > 0);
                                                ?>
                                                    <tr>
                                                        <td>
                                                            <div style="display:flex; align-items:center; gap:10px;">
                                                                <div class="section-tag-dot"></div>
                                                                <div>
                                                                    <strong style="color:#0f172a; font-size:13.5px;"><?php echo htmlspecialchars($d['department']); ?></strong>
                                                                    <span style="display:block; font-size:11px; color:#64748b;">ID: #<?php echo (int)$d['id']; ?></span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="count-pill">
                                                                <?php echo $courseCount; ?> <?php echo $courseCount === 1 ? 'course' : 'courses'; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <form method="POST" action="../../controllers/admin/creationProcess.php" style="margin:0; display:flex; align-items:center; gap:6px;"><?php echo csrfField(); ?>
                                                                <input type="hidden" name="action" value="assign_director">
                                                                <input type="hidden" name="department_id" value="<?php echo (int)$d['id']; ?>">
                                                                <select name="director_id" onchange="this.form.submit()" style="padding:5px 8px; font-size:12px; border:1px solid #cbd5e1; border-radius:6px; background:#ffffff; color:#1e293b; max-width:210px; cursor:pointer;" title="Change director for this section">
                                                                    <option value="">-- Unassigned --</option>
                                                                    <?php foreach ($allDirectors as $dir): ?>
                                                                        <option value="<?php echo (int)$dir['id']; ?>" <?php echo ((int)$d['director_user_id'] === (int)$dir['id']) ? 'selected' : ''; ?>>
                                                                            <?php echo htmlspecialchars($dir['name'] . ' ' . $dir['lastname']); ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </form>
                                                        </td>
                                                        <td>
                                                            <?php if ($isProtected): ?>
                                                                <span class="status-badge-inuse" title="Section has linked courses or director.">
                                                                    In Use
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="status-badge-available">
                                                                    Unassigned
                                                                </span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td style="text-align: right;">
                                                            <a href="adminCreate.php?tab=section&confirm_delete_section=<?php echo (int)$d['id']; ?>" class="btn-del-action" title="Delete section">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                                <span>Delete</span>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" style="text-align:center; padding:32px; color:#64748b;">No sections registered. Create one using the form above.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- ============================================== -->
                    <!-- TAB: CREATE ACADEMIC DIRECTOR -->
                    <!-- ============================================== -->
                    <section class="tab-content <?php echo ($initialTab === 'director') ? 'active' : ''; ?>" id="tab-director" role="tabpanel">
                        <div class="card-panel">
                            <div class="card-panel-header">
                                <div class="card-panel-header-left">
                                    <div class="card-panel-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="9" cy="7" r="4"></circle>
                                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h2>Academic Director Provisioning</h2>
                                        <p>Create institutional leadership credentials and appoint supervision over an academic section.</p>
                                    </div>
                                </div>
                            </div>
                            <form class="card-panel-body" id="form-director" action="../../controllers/admin/creationProcess.php" method="POST"><?php echo csrfField(); ?>
                                <input type="hidden" name="action" value="director">
                                <div class="form-section-header">
                                    <span class="form-section-title">01. Director Identity & Department Assignment</span>
                                    <span class="form-section-subtitle">Academic leadership credentials</span>
                                </div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="dir_name">
                                            <span class="label-title">First Name <span class="label-required">*</span></span>
                                        </label>
                                        <input class="field" type="text" id="dir_name" name="name" placeholder="e.g. Marcus" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="dir_lastname">
                                            <span class="label-title">Last Name <span class="label-required">*</span></span>
                                        </label>
                                        <input class="field" type="text" id="dir_lastname" name="lastname" placeholder="e.g. Sterling" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="dir_email">
                                            <span class="label-title">Institutional Email Address <span class="label-required">*</span></span>
                                        </label>
                                        <input class="field" type="email" id="dir_email" name="email" placeholder="e.g. msterling@edu-system.com" required>
                                        <span class="field-caption">Primary login credential for Director Leadership Portal.</span>
                                    </div>

                                    <div class="form-group">
                                        <label for="dir_department">
                                            <span class="label-title">Assigned Section / Department <span class="label-required">*</span></span>
                                            <span class="label-hint">Supervision Scope</span>
                                        </label>
                                        <select id="dir_department" name="department_id" required>
                                            <option value="">Select section to supervise...</option>
                                            <?php foreach ($departments as $dept): ?>
                                                <option value="<?php echo (int)$dept['id']; ?>"><?php echo htmlspecialchars($dept['department']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <span class="field-caption">The Director will audit courses, faculty deliverables, and grades for this division.</span>
                                    </div>
                                </div>

                                <div class="form-section-header bordered">
                                    <span class="form-section-title">02. Security Policy & Authentication</span>
                                    <span class="form-section-subtitle">Access credentials</span>
                                </div>
                                <div class="form-grid">
                                    <div class="form-group full-width">
                                        <label for="dir_password">
                                            <span class="label-title">Initial Access Key <span class="label-required">*</span></span>
                                        </label>
                                        <input class="field" type="text" id="dir_password" name="password" value="password123" required>
                                        <span class="field-caption">Temporary password. Director account is activated immediately upon creation.</span>
                                    </div>
                                </div>

                                <div class="form-actions-bar">
                                    <button type="reset" class="btn-secondary">Reset Form</button>
                                    <button type="submit" class="btn-primary">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="8.5" cy="7" r="4"></circle>
                                            <polyline points="17 11 19 13 23 9"></polyline>
                                        </svg>
                                        <span>Appoint Academic Director</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </section>

                    <!-- ============================================== -->
                    <!-- TAB 2: CREATE STUDENT -->
                    <!-- ============================================== -->
                    <section class="tab-content <?php echo ($initialTab === 'student') ? 'active' : ''; ?>" id="tab-student" role="tabpanel">
                        <div class="card-panel">
                            <div class="card-panel-header">
                                <div class="card-panel-header-left">
                                    <div class="card-panel-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                                            <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h2>Student Account Provisioning</h2>
                                        <p>Generate authenticated institutional credentials and assign academic matriculation.</p>
                                    </div>
                                </div>
                            </div>
                            <form class="card-panel-body" id="form-student" action="../../controllers/admin/creationProcess.php" method="POST"><?php echo csrfField(); ?>
                                <input type="hidden" name="action" value="student">
                                <div class="form-section-header">
                                    <span class="form-section-title">01. Personal Identity</span>
                                    <span class="form-section-subtitle">Student academic record</span>
                                </div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="student_name">
                                            <span class="label-title">First Name <span class="label-required">*</span></span>
                                        </label>
                                        <input class="field" type="text" id="student_name" name="name" placeholder="e.g. Maria Elena" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="student_lastname">
                                            <span class="label-title">Last Name <span class="label-required">*</span></span>
                                        </label>
                                        <input class="field" type="text" id="student_lastname" name="lastname" placeholder="e.g. Gonzalez Torres" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="student_email">
                                            <span class="label-title">Institutional Email Address <span class="label-required">*</span></span>
                                            <span class="label-hint">Domain verified</span>
                                        </label>
                                        <input class="field" type="email" id="student_email" name="email" placeholder="e.g. mgonzalez@edu-system.com" required>
                                        <span class="field-caption">Credentials and syllabus announcements will be routed to this address.</span>
                                    </div>

                                    <div class="form-group">
                                        <label for="student_control_id">
                                            <span class="label-title">Matriculation / Control ID</span>
                                            <span class="label-hint">Registrar Reference</span>
                                        </label>
                                        <input class="field" type="text" id="student_control_id" name="control_id" placeholder="e.g. MAT-2026-0419">
                                        <span class="field-caption">Institutional identifier mapped to internal grade sheets.</span>
                                    </div>
                                </div>

                                <div class="form-section-header bordered">
                                    <span class="form-section-title">02. Access Credentials & Initial Enrollment</span>
                                    <span class="form-section-subtitle">Authentication security</span>
                                </div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="student_password">
                                            <span class="label-title">Temporary Access Password <span class="label-required">*</span></span>
                                        </label>
                                        <input class="field" type="text" id="student_password" name="password" value="alumno123" required>
                                        <span class="field-caption">User is required to update password upon primary login session.</span>
                                    </div>

                                    <div class="form-group">
                                        <label for="student_course">
                                            <span class="label-title">Initial Course Assignment</span>
                                            <span class="label-hint">Catalog</span>
                                        </label>
                                        <select id="student_course" name="course_id">
                                            <option value="">Enroll without initial course...</option>
                                            <?php if (!empty($courses)): ?>
                                                <?php foreach ($courses as $c): ?>
                                                    <option value="<?php echo (int)$c['id']; ?>"><?php echo htmlspecialchars($c['course_name']); ?></option>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <option value="1">Introduction to Computer Science</option>
                                                <option value="2">Data Structures & Algorithms</option>
                                            <?php endif; ?>
                                        </select>
                                        <span class="field-caption">Additional enrollments can be added subsequently via the community directory.</span>
                                    </div>
                                </div>

                                <div class="form-actions-bar">
                                    <button type="reset" class="btn-secondary">Reset Form</button>
                                    <button type="submit" class="btn-primary">
                                        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="8.5" cy="7" r="4"></circle>
                                            <line x1="20" y1="8" x2="20" y2="14"></line>
                                            <line x1="23" y1="11" x2="17" y2="11"></line>
                                        </svg>
                                        <span>Enroll Student</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </section>

                    <!-- ============================================== -->
                    <!-- TAB 3: CREATE TEACHER / PONENTE -->
                    <!-- ============================================== -->
                    <section class="tab-content <?php echo ($initialTab === 'teacher') ? 'active' : ''; ?>" id="tab-teacher" role="tabpanel">
                        <div class="card-panel">
                            <div class="card-panel-header">
                                <div class="card-panel-header-left">
                                    <div class="card-panel-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="8.5" cy="7" r="4"></circle>
                                            <polyline points="17 11 19 13 23 9"></polyline>
                                        </svg>
                                    </div>
                                    <div>
                                        <h2>Faculty Appointment & Speaker Registration</h2>
                                        <p>Commission instructional staff with syllabus creation, module publishing, and grading authority.</p>
                                    </div>
                                </div>
                            </div>
                            <form class="card-panel-body" id="form-teacher" action="../../controllers/admin/creationProcess.php" method="POST"><?php echo csrfField(); ?>
                                <input type="hidden" name="action" value="teacher">
                                <div class="form-section-header">
                                    <span class="form-section-title">01. Academic Practitioner Profile</span>
                                    <span class="form-section-subtitle">Faculty registry records</span>
                                </div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="teacher_name">
                                            <span class="label-title">First Name <span class="label-required">*</span></span>
                                        </label>
                                        <input class="field" type="text" id="teacher_name" name="name" placeholder="e.g. Richard" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="teacher_lastname">
                                            <span class="label-title">Last Name <span class="label-required">*</span></span>
                                        </label>
                                        <input class="field" type="text" id="teacher_lastname" name="lastname" placeholder="e.g. Thorne-Smith" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="teacher_email">
                                            <span class="label-title">Academic Email Address <span class="label-required">*</span></span>
                                        </label>
                                        <input class="field" type="email" id="teacher_email" name="email" placeholder="e.g. rthorne@edu-system.com" required>
                                        <span class="field-caption">Designated faculty inbox for student inquiries and institutional bulletins.</span>
                                    </div>

                                    <div class="form-group">
                                        <label for="teacher_title">
                                            <span class="label-title">Academic Title / Specialty</span>
                                            <span class="label-hint">Curriculum Display</span>
                                        </label>
                                        <input class="field" type="text" id="teacher_title" name="specialty" placeholder="e.g. Ph.D. in High-Performance Computing, Senior Fellow">
                                        <span class="field-caption">Appears beneath instructor name across published syllabus banners.</span>
                                    </div>
                                </div>

                                <div class="form-section-header bordered">
                                    <span class="form-section-title">02. Curricular Assignment & Security Access</span>
                                    <span class="form-section-subtitle">Course and credential management</span>
                                </div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="teacher_course">
                                            <span class="label-title">Assigned Course</span>
                                            <span class="label-hint">Catalog</span>
                                        </label>
                                        <select id="teacher_course" name="course_id">
                                            <option value="">Assign course at a later date (Unassigned)...</option>
                                            <?php if (!empty($courses)): ?>
                                                <?php foreach ($courses as $c): ?>
                                                    <option value="<?php echo (int)$c['id']; ?>">
                                                        <?php echo htmlspecialchars($c['course_name'] . (!empty($c['department_name']) ? ' — ' . $c['department_name'] : '')); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <option value="1">Desarrollo Web Full Stack</option>
                                                <option value="2">Estructuras de Datos y Algoritmos</option>
                                            <?php endif; ?>
                                        </select>
                                        <span class="field-caption">The appointed instructor will be linked to this course syllabus.</span>
                                    </div>

                                    <div class="form-group">
                                        <label for="teacher_password">
                                            <span class="label-title">Temporary Access Password <span class="label-required">*</span></span>
                                        </label>
                                        <input class="field" type="text" id="teacher_password" name="password" value="password123" required>
                                        <span class="field-caption">Access rights automatically grant Teacher dashboard tools upon login.</span>
                                    </div>
                                </div>

                                <div class="form-actions-bar">
                                    <button type="reset" class="btn-secondary">Reset Form</button>
                                    <button type="submit" class="btn-primary">
                                        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="8.5" cy="7" r="4"></circle>
                                            <line x1="20" y1="8" x2="20" y2="14"></line>
                                            <line x1="23" y1="11" x2="17" y2="11"></line>
                                        </svg>
                                        <span>Register Faculty Member</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </section>

                    <!-- ============================================== -->
                    <!-- TAB 4: CREATE ADMINISTRATOR -->
                    <!-- ============================================== -->
                    <section class="tab-content <?php echo ($initialTab === 'admin') ? 'active' : ''; ?>" id="tab-admin" role="tabpanel">
                        <div class="card-panel">
                            <div class="card-panel-header">
                                <div class="card-panel-header-left">
                                    <div class="card-panel-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h2>System Administrator Provisioning</h2>
                                        <p>Create administrator accounts with full access to institutional management.</p>
                                    </div>
                                </div>
                            </div>
                            <form class="card-panel-body" id="form-admin" action="../../controllers/admin/creationProcess.php" method="POST"><?php echo csrfField(); ?>
                                <input type="hidden" name="action" value="admin">
                                <div class="form-section-header">
                                    <span class="form-section-title">01. Administrative Principal Identity</span>
                                    <span class="form-section-subtitle">Governance identity</span>
                                </div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="admin_name">
                                            <span class="label-title">First Name <span class="label-required">*</span></span>
                                        </label>
                                        <input class="field" type="text" id="admin_name" name="name" placeholder="e.g. Victor" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="admin_lastname">
                                            <span class="label-title">Last Name <span class="label-required">*</span></span>
                                        </label>
                                        <input class="field" type="text" id="admin_lastname" name="lastname" placeholder="e.g. Vance" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="admin_email">
                                            <span class="label-title">Governance Email Address <span class="label-required">*</span></span>
                                        </label>
                                        <input class="field" type="email" id="admin_email" name="email" placeholder="e.g. vvance@edu-system.com" required>
                                        <span class="field-caption">Security audit events and verification alerts are dispatched to this mailbox.</span>
                                    </div>

                                    <div class="form-group">
                                        <span class="label-title">Administrator Access</span>
                                        <p class="field-caption">Administrator accounts have full access to the catalog, user accounts, enrollments, and reports.</p>
                                    </div>
                                </div>

                                <div class="form-section-header bordered">
                                    <span class="form-section-title">02. Security Policy & Authentication</span>
                                    <span class="form-section-subtitle">Privileged credential management</span>
                                </div>
                                <div class="form-grid">
                                    <div class="form-group full-width">
                                        <label for="admin_password">
                                            <span class="label-title">Master Access Key <span class="label-required">*</span></span>
                                        </label>
                                        <input class="field" type="text" id="admin_password" name="password" value="admin123" required>
                                        <span class="field-caption">Must satisfy entropy guidelines: minimum 8 alphanumeric characters. Administrator privileges are activated immediately.</span>
                                    </div>
                                </div>

                                <div class="form-actions-bar">
                                    <button type="reset" class="btn-secondary">Reset Form</button>
                                    <button type="submit" class="btn-primary">
                                        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                        </svg>
                                        <span>Grant Administrator Privileges</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </body>
</html>
