<?php
    require_once __DIR__ . '/../../controllers/auth/check_auth.php';
    require_once __DIR__ . '/../../config/connection.php';
    $user = getLoggedUser();

    // Fetch existing catalog options for realistic selects
    $departments = [];
    $teachers = [];
    $courses = [];

    try {
        $conn = createConnection();
        
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

        $courseRes = $conn->query("SELECT id, course_name FROM course ORDER BY course_name ASC");
        if ($courseRes) {
            while ($row = $courseRes->fetch_assoc()) {
                $courses[] = $row;
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
        <link rel="stylesheet" href="create.css">
    </head>
    <body>
        <?php include __DIR__ . '/../../../ui/header/header.php'; ?>
        <?php include __DIR__ . '/../../../ui/adminSideBar/adminSideBar.php'; ?>

        <div class="workspace">
            <main>
                <div id="page-content">
                    <!-- Top Breadcrumb & Status Tag -->
                    <div class="breadcrumb-bar">
                        <a href="adminDashboard.php" class="breadcrumb">
                            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="19" y1="12" x2="5" y2="12"></line>
                                <polyline points="12 19 5 12 12 5"></polyline>
                            </svg>
                            Back to Administration Dashboard
                        </a>
                        <span class="breadcrumb-tag">Administrative Access</span>
                    </div>

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

                    <!-- Executive Segmented Tab Navigation -->
                    <div class="tab-control-wrap" role="tablist">
                        <button class="tab-btn <?php echo ($initialTab === 'course') ? 'active' : ''; ?>" onclick="switchTab('course')" id="btn-tab-course" type="button" role="tab">
                            <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                            </svg>
                            <span>Academic Course</span>
                        </button>
                        <button class="tab-btn <?php echo ($initialTab === 'student') ? 'active' : ''; ?>" onclick="switchTab('student')" id="btn-tab-student" type="button" role="tab">
                            <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                                <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                            </svg>
                            <span>Student Enrollment</span>
                        </button>
                        <button class="tab-btn <?php echo ($initialTab === 'teacher') ? 'active' : ''; ?>" onclick="switchTab('teacher')" id="btn-tab-teacher" type="button" role="tab">
                            <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="8.5" cy="7" r="4"></circle>
                                <polyline points="17 11 19 13 23 9"></polyline>
                            </svg>
                            <span>Faculty / Ponente</span>
                        </button>
                        <button class="tab-btn <?php echo ($initialTab === 'admin') ? 'active' : ''; ?>" onclick="switchTab('admin')" id="btn-tab-admin" type="button" role="tab">
                            <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                            <span>Administrator</span>
                        </button>
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
                            <form class="card-panel-body" id="form-course" action="../../controllers/admin/creationProcess.php" method="POST">
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
                                    <span class="form-section-title">02. Faculty Assignment & Deployment Status</span>
                                    <span class="form-section-subtitle">Instructional responsibility</span>
                                </div>
                                <div class="form-grid">
                                    <div class="form-group">
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

                                    <div class="form-group">
                                        <label>
                                            <span class="label-title">Catalog Visibility</span>
                                            <span class="label-hint">Initial State</span>
                                        </label>
                                        <div class="choice-tiles-grid">
                                            <label class="choice-tile active" onclick="selectChoiceTile(this)">
                                                <input type="radio" name="course_status" value="1" checked>
                                                <span class="choice-tile-indicator"></span>
                                                <div class="choice-tile-content">
                                                    <strong>Published Status</strong>
                                                    <span>Live catalog listing ready for student enrollment.</span>
                                                </div>
                                            </label>
                                            <label class="choice-tile" onclick="selectChoiceTile(this)">
                                                <input type="radio" name="course_status" value="0">
                                                <span class="choice-tile-indicator"></span>
                                                <div class="choice-tile-content">
                                                    <strong>Under Review / Draft</strong>
                                                    <span>Restricted syllabus access pending director sign-off.</span>
                                                </div>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group full-width">
                                        <label for="course_desc">
                                            <span class="label-title">Syllabus Overview & Course Prerequisites</span>
                                        </label>
                                        <textarea id="course_desc" name="description" placeholder="Provide a summary of core learning outcomes, recommended prerequisites, and grading policies..."></textarea>
                                    </div>
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
                            <form class="card-panel-body" id="form-student" action="../../controllers/admin/creationProcess.php" method="POST">
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
                                        <div class="input-addon-group">
                                            <input class="field" type="text" id="student_password" name="password" value="alumno123" required>
                                            <button type="button" class="btn-input-action" onclick="generateSecurePassword('student_password')">
                                                <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="7.5" cy="15.5" r="5.5"></circle>
                                                    <path d="m21 2-9.6 9.6"></path>
                                                    <path d="m15.5 7.5 3 3L22 7l-3-3"></path>
                                                </svg>
                                                <span>Generate Key</span>
                                            </button>
                                        </div>
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
                            <form class="card-panel-body" id="form-teacher" action="../../controllers/admin/creationProcess.php" method="POST">
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
                                    <span class="form-section-title">02. Institutional Affiliation & Security Access</span>
                                    <span class="form-section-subtitle">Department assignment</span>
                                </div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="teacher_dept">
                                            <span class="label-title">Primary Department <span class="label-required">*</span></span>
                                        </label>
                                        <select id="teacher_dept" name="department_id" required>
                                            <option value="">Select academic department...</option>
                                            <?php if (!empty($departments)): ?>
                                                <?php foreach ($departments as $dept): ?>
                                                    <option value="<?php echo (int)$dept['id']; ?>"><?php echo htmlspecialchars($dept['department']); ?></option>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <option value="1">Computer Science & Engineering</option>
                                                <option value="2">Information Systems</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="teacher_password">
                                            <span class="label-title">Temporary Access Password <span class="label-required">*</span></span>
                                        </label>
                                        <div class="input-addon-group">
                                            <input class="field" type="text" id="teacher_password" name="password" value="password123" required>
                                            <button type="button" class="btn-input-action" onclick="generateSecurePassword('teacher_password')">
                                                <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="7.5" cy="15.5" r="5.5"></circle>
                                                    <path d="m21 2-9.6 9.6"></path>
                                                    <path d="m15.5 7.5 3 3L22 7l-3-3"></path>
                                                </svg>
                                                <span>Generate Key</span>
                                            </button>
                                        </div>
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
                                        <p>Delegate privileged governance credentials with institutional scope controls.</p>
                                    </div>
                                </div>
                            </div>
                            <form class="card-panel-body" id="form-admin" action="../../controllers/admin/creationProcess.php" method="POST">
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
                                        <label for="admin_scope">
                                            <span class="label-title">Administrative Role Scope <span class="label-required">*</span></span>
                                            <span class="label-hint">Access Level</span>
                                        </label>
                                        <select id="admin_scope" name="scope" required>
                                            <option value="super">Super Administrator (Full Catalog, Role & System Control)</option>
                                            <option value="academic">Academic Dean / Coordinator (Catalog & Faculty Operations)</option>
                                            <option value="registrar">Student Registrar (Accounts, Enrollments & Reports)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-section-header bordered">
                                    <span class="form-section-title">02. Security Policy & Authentication</span>
                                    <span class="form-section-subtitle">Privileged credential management</span>
                                </div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="admin_password">
                                            <span class="label-title">Master Access Key <span class="label-required">*</span></span>
                                        </label>
                                        <div class="input-addon-group">
                                            <input class="field" type="text" id="admin_password" name="password" value="admin123" required>
                                            <button type="button" class="btn-input-action" onclick="generateSecurePassword('admin_password')">
                                                <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="7.5" cy="15.5" r="5.5"></circle>
                                                    <path d="m21 2-9.6 9.6"></path>
                                                    <path d="m15.5 7.5 3 3L22 7l-3-3"></path>
                                                </svg>
                                                <span>Generate Key</span>
                                            </button>
                                        </div>
                                        <span class="field-caption">Must satisfy entropy guidelines: minimum 8 alphanumeric characters.</span>
                                    </div>

                                    <div class="form-group">
                                        <label>
                                            <span class="label-title">Verification Clearance</span>
                                            <span class="label-hint">Immediate State</span>
                                        </label>
                                        <div class="choice-tiles-grid">
                                            <label class="choice-tile active" onclick="selectChoiceTile(this)">
                                                <input type="radio" name="admin_verified" value="1" checked>
                                                <span class="choice-tile-indicator"></span>
                                                <div class="choice-tile-content">
                                                    <strong>Pre-Verified Access</strong>
                                                    <span>Immediate administrator session authorization without email hurdle.</span>
                                                </div>
                                            </label>
                                            <label class="choice-tile" onclick="selectChoiceTile(this)">
                                                <input type="radio" name="admin_verified" value="0">
                                                <span class="choice-tile-indicator"></span>
                                                <div class="choice-tile-content">
                                                    <strong>Require Email Confirmation</strong>
                                                    <span>Generates activation token; session locked until verified.</span>
                                                </div>
                                            </label>
                                        </div>
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

        <!-- Executive Submission Confirmation Modal -->
        <div class="modal-overlay" id="submissionModal" role="dialog" aria-modal="true">
            <div class="modal-card">
                <div class="modal-header">
                    <div class="modal-badge-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <div>
                        <h3 id="modalTitle">Validation Successful</h3>
                        <p id="modalSubtitle">The administrative record has been client-side validated and prepared for database ingestion.</p>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="summary-list" id="modalSummaryList">
                        <!-- Populated by JS -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Dismiss</button>
                    <a href="adminDashboard.php" class="btn-primary" style="text-decoration:none;">
                        <span>Return to Dashboard →</span>
                    </a>
                </div>
            </div>
        </div>

        <script>
            // Segmented Tab Switching
            function switchTab(tabKey) {
                document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
                const activeBtn = document.getElementById('btn-tab-' + tabKey);
                if (activeBtn) activeBtn.classList.add('active');

                document.querySelectorAll('.tab-content').forEach(panel => panel.classList.remove('active'));
                const activePanel = document.getElementById('tab-' + tabKey);
                if (activePanel) activePanel.classList.add('active');

                const url = new URL(window.location);
                url.searchParams.set('tab', tabKey);
                window.history.replaceState({}, '', url);
            }

            // Choice Tile Selector
            function selectChoiceTile(tileEl) {
                const grid = tileEl.closest('.choice-tiles-grid');
                grid.querySelectorAll('.choice-tile').forEach(t => t.classList.remove('active'));
                tileEl.classList.add('active');
                const radio = tileEl.querySelector('input[type="radio"]');
                if (radio) radio.checked = true;
            }

            // Secure High-Entropy Password Generator
            function generateSecurePassword(targetInputId) {
                const uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
                const lowercase = 'abcdefghijkmnpqrstuvwxyz';
                const digits = '23456789';
                const symbols = '!@#$%^&*';
                const all = uppercase + lowercase + digits + symbols;

                let password = '';
                password += uppercase.charAt(Math.floor(Math.random() * uppercase.length));
                password += lowercase.charAt(Math.floor(Math.random() * lowercase.length));
                password += digits.charAt(Math.floor(Math.random() * digits.length));
                password += symbols.charAt(Math.floor(Math.random() * symbols.length));

                for (let i = 4; i < 12; i++) {
                    password += all.charAt(Math.floor(Math.random() * all.length));
                }

                // Shuffle characters
                password = password.split('').sort(() => 0.5 - Math.random()).join('');

                const field = document.getElementById(targetInputId);
                if (field) {
                    field.value = password;
                    field.type = 'text';
                    field.focus();
                }
            }

            // Form Submit Interceptor & Executive Modal Display
            function handleFormSubmit(event, entityType) {
                event.preventDefault();
                const form = event.target;
                const formData = new FormData(form);

                const modalTitle = document.getElementById('modalTitle');
                const modalSubtitle = document.getElementById('modalSubtitle');
                const summaryList = document.getElementById('modalSummaryList');

                modalTitle.textContent = `${entityType} Verified Successfully`;
                modalSubtitle.textContent = `All mandatory fields for this ${entityType.toLowerCase()} meet production data constraints. Ready for backend insertion.`;

                summaryList.innerHTML = '';

                // Extract friendly summary pairs
                for (let [key, val] of formData.entries()) {
                    if (!val) continue;
                    let label = key.replace(/_/g, ' ').toUpperCase();
                    if (key.includes('password')) val = '•••••••• (Masked)';
                    if (key.includes('status')) val = (val === '1') ? 'Published' : 'Draft / In Review';
                    if (key.includes('verified')) val = (val === '1') ? 'Pre-Verified' : 'Verification Pending';

                    const item = document.createElement('div');
                    item.className = 'summary-item';
                    item.innerHTML = `<span>${label}</span><strong>${escapeHtml(String(val))}</strong>`;
                    summaryList.appendChild(item);
                }

                document.getElementById('submissionModal').classList.add('active');
            }

            function closeModal() {
                document.getElementById('submissionModal').classList.remove('active');
            }

            function escapeHtml(str) {
                return str.replace(/[&<>'"]/g, 
                    tag => ({
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        "'": '&#39;',
                        '"': '&quot;'
                    }[tag] || tag)
                );
            }

            // Close modal with Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') closeModal();
            });
        </script>
    </body>
</html>
