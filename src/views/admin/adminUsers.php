<?php
    require_once __DIR__ . '/../../controllers/auth/check_auth.php';
    requireRole([1]);
    $user = getLoggedUser();
    require_once __DIR__ . '/../../models/adminModels/adminUsersModel.php';
    require_once __DIR__ . '/../../models/adminModels/adminCoursesModel.php';

    $adminUsers = new AdminUsers();
    $adminCourses = new AdminCourses();
    $totalUsers = $adminUsers->getTotalUsersCount();
    $studentsCount = $adminUsers->getStudentsCount();
    $academicStaffCount = $adminUsers->getAcademicStaffCount();
    $pendingUsersCount = $adminUsers->getPendingUsersCount();
    $usersList = $adminUsers->getAllUsers();
    $departmentsList = $adminCourses->getDepartmentsList();
    $allCoursesList = $adminCourses->getAllCourses();

    // Pure PHP filtering
    $search = trim($_GET['search'] ?? '');
    $roleFilter = trim($_GET['role'] ?? '');
    $statusFilter = trim($_GET['status'] ?? '');

    if (!empty($search)) {
        $usersList = array_filter($usersList, function($u) use ($search) {
            $fullName = ($u['name'] ?? '') . ' ' . ($u['lastname'] ?? '');
            return stripos($fullName, $search) !== false
                || stripos($u['email'] ?? '', $search) !== false
                || stripos((string)$u['id'], $search) !== false;
        });
    }

    if (!empty($roleFilter)) {
        $usersList = array_filter($usersList, function($u) use ($roleFilter) {
            return strtolower($u['role_name'] ?? '') === strtolower($roleFilter);
        });
    }

    if (!empty($statusFilter)) {
        $usersList = array_filter($usersList, function($u) use ($statusFilter) {
            $isVer = ((int)$u['verified'] === 1);
            $stText = $isVer ? 'Verified' : 'Pending';
            return strtolower($stText) === strtolower($statusFilter);
        });
    }

    // Pure PHP Edit User Resolution
    $editUserId = (int)($_GET['edit_user_id'] ?? 0);
    $userToEdit = null;
    if ($editUserId > 0) {
        $userToEdit = $adminUsers->getUserById($editUserId);
    }

    // Pure PHP Delete User Confirmation
    $confirmDeleteId = (int)($_GET['confirm_delete_id'] ?? 0);
    $userToDelete = null;
    if ($confirmDeleteId > 0) {
        $userToDelete = $adminUsers->getUserById($confirmDeleteId);
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
        <title>Users · Edu-System</title>
        <link rel="stylesheet" href="adminUsers.css">
    </head>
    <body>
        <?php include __DIR__ . '/../../../ui/header/header.php'; ?>
        <?php include __DIR__ . '/../../../ui/adminSideBar/adminSideBar.php'; ?>

        <div class="workspace">
            <main>
                <div id="page-content">
                    <!-- Heading -->
                    <section class="page-heading">
                        <div>
                            <p class="eyebrow">ACADEMIC MANAGEMENT</p>
                            <h1>School Community</h1>
                            <p class="subtitle">Manage member profiles, roles, and platform permissions across your institution.</p>
                        </div>
                    </section>

                    <?php if ($flashSuccess): ?>
                        <div style="background-color: #f0fdf4; border: 1px solid #86efac; color: #15803d; padding: 14px 20px; border-radius: 10px; margin-bottom: 24px; font-size: 13px; font-weight: 500;">
                            <strong>Success:</strong> <?php echo htmlspecialchars($flashSuccess); ?>
                        </div>
                    <?php elseif ($flashError): ?>
                        <div style="background-color: #fef2f2; border: 1px solid #fecdd3; color: #b91c1c; padding: 14px 20px; border-radius: 10px; margin-bottom: 24px; font-size: 13px; font-weight: 500;">
                            <strong>Error:</strong> <?php echo htmlspecialchars($flashError); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($userToDelete): ?>
                        <div style="background-color: #fff1f2; border: 1px solid #fecdd3; color: #9f1239; padding: 18px 24px; border-radius: 10px; margin-bottom: 24px; font-size: 13px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                            <div>
                                <strong>Delete Confirmation:</strong> Are you sure you want to permanently delete account for "<strong><?php echo htmlspecialchars($userToDelete['name'] . ' ' . ($userToDelete['lastname'] ?? '')); ?></strong>" (#<?php echo (int)$userToDelete['id']; ?>)?
                            </div>
                            <div style="display:flex; gap:10px;">
                                <a href="adminUsers.php" class="btn btn-secondary" style="padding:6px 14px; text-decoration:none;">Cancel</a>
                                <form method="POST" action="../../controllers/admin/manageUser.php" style="display:inline;">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="user_id" value="<?php echo (int)$userToDelete['id']; ?>">
                                    <button type="submit" class="btn btn-primary" style="background:#dc2626; border-color:#dc2626; padding:6px 14px; text-decoration:none;">Confirm Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- KPI Grid -->
                    <section class="kpi-grid">
                        <article class="kpi">
                            <h2 class="kpi-label">Registered Users</h2>
                            <strong class="kpi-value"><?php echo $totalUsers; ?></strong>
                        </article>
                        <article class="kpi">
                            <h2 class="kpi-label">Students</h2>
                            <strong class="kpi-value"><?php echo $studentsCount; ?></strong>
                        </article>
                        <article class="kpi">
                            <h2 class="kpi-label">Academic Staff</h2>
                            <strong class="kpi-value"><?php echo $academicStaffCount; ?></strong>
                        </article>
                        <a href="adminUsers.php?status=Pending" style="text-decoration:none; color:inherit; display:block;" title="Review accounts pending verification">
                            <article class="kpi" style="cursor:pointer; border-color:<?php echo $pendingUsersCount > 0 ? '#fde68a' : '#e2e8f0'; ?>; background:<?php echo $pendingUsersCount > 0 ? '#fffdf7' : '#ffffff'; ?>;">
                                <h2 class="kpi-label" style="display:flex; justify-content:space-between; align-items:center;">
                                    <span>Pending Verification</span>
                                    <?php if ($pendingUsersCount > 0): ?>
                                        <span style="font-size:11px; font-weight:700; color:#b45309; background:#fef3c7; padding:2px 8px; border-radius:12px;">Needs Approval</span>
                                    <?php endif; ?>
                                </h2>
                                <strong class="kpi-value" style="color:<?php echo $pendingUsersCount > 0 ? '#d97706' : '#0f172a'; ?>;"><?php echo $pendingUsersCount; ?></strong>
                            </article>
                        </a>
                    </section>

                    <!-- Main Panel -->
                    <section class="panel">
                        <div class="panel-head">
                            <div>
                                <h2>User Directory</h2>
                                <p>View active accounts, institutional emails, assigned roles, and email verification states.</p>
                            </div>
                            <a href="adminCreate.php?tab=student" class="btn btn-primary" style="text-decoration:none;">
                                <svg style="width:15px; height:15px; stroke:currentColor; stroke-width:2.2;" viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                <span>Register Member</span>
                            </a>
                        </div>

                        <!-- Pure PHP Filter Form -->
                        <form method="GET" action="adminUsers.php" class="filters">
                            <label class="search">
                                <span class="filter-label">Search by name, email or ID</span>
                                <input class="field" name="search" id="search" type="search" placeholder="Search by name, email or ID…" value="<?php echo htmlspecialchars($search); ?>">
                            </label>
                            <label>
                                <span class="filter-label">Role</span>
                                <select name="role" id="roleFilter">
                                    <option value="">All roles</option>
                                    <option value="Admin" <?php echo ($roleFilter === 'Admin') ? 'selected' : ''; ?>>Admin</option>
                                    <option value="Director" <?php echo ($roleFilter === 'Director') ? 'selected' : ''; ?>>Director</option>
                                    <option value="Teacher" <?php echo ($roleFilter === 'Teacher') ? 'selected' : ''; ?>>Teacher</option>
                                    <option value="Student" <?php echo ($roleFilter === 'Student') ? 'selected' : ''; ?>>Student</option>
                                </select>
                            </label>
                            <label>
                                <span class="filter-label">Status</span>
                                <select name="status" id="statusFilter">
                                    <option value="">All statuses</option>
                                    <option value="Verified" <?php echo ($statusFilter === 'Verified') ? 'selected' : ''; ?>>Verified</option>
                                    <option value="Pending" <?php echo ($statusFilter === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                </select>
                            </label>
                            <button type="submit" class="btn btn-primary" style="padding:10px 18px; align-self:flex-end;">Filter</button>
                            <?php if (!empty($search) || !empty($roleFilter) || !empty($statusFilter)): ?>
                                <a href="adminUsers.php" class="btn btn-secondary" style="padding:10px 16px; align-self:flex-end; text-decoration:none;">Reset</a>
                            <?php endif; ?>
                        </form>

                        <div class="table-wrap" role="region" aria-label="User directory" tabindex="0">
                            <table id="usersTable">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 35%;" class="col-user">USER</th>
                                        <th scope="col" style="width: 15%;" class="col-id">CONTROL ID</th>
                                        <th scope="col" style="width: 18%;" class="col-role">ROLE</th>
                                        <th scope="col" style="width: 17%;" class="col-status">STATUS</th>
                                        <th scope="col" style="width: 15%; text-align: right;" class="col-actions">ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($usersList)): ?>
                                        <?php foreach ($usersList as $u):
                                            $isVerified = ((int)$u['verified'] === 1);
                                            $statusText = $isVerified ? 'Verified' : 'Pending';
                                            $statusClass = $isVerified ? 'active' : 'pending';
                                            $roleName = htmlspecialchars($u['role_name']);
                                        ?>
                                            <tr>
                                                <td class="col-user">
                                                    <strong><?php echo htmlspecialchars($u['name'] . ' ' . ($u['lastname'] ?? '')); ?></strong>
                                                    <span style="display:block; font-size:12px; color:#64748b;"><?php echo htmlspecialchars($u['email']); ?></span>
                                                </td>
                                                <td class="col-id"><code>#<?php echo htmlspecialchars($u['id']); ?></code></td>
                                                <td class="col-role">
                                                    <span class="badge role-<?php echo strtolower($roleName); ?>">
                                                        <?php echo $roleName; ?>
                                                    </span>
                                                </td>
                                                <td class="col-status">
                                                    <span class="badge <?php echo $statusClass; ?>">
                                                        <?php echo $statusText; ?>
                                                    </span>
                                                </td>
                                                <td class="col-actions" style="text-align: right;">
                                                    <div style="display:inline-flex; align-items:center; gap:6px;">
                                                        <a href="adminUsers.php?edit_user_id=<?php echo (int)$u['id']; ?>" class="btn-table-action" title="Edit User Account">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                            <span>Edit</span>
                                                        </a>
                                                        <?php if ((int)$u['role'] === 4): ?>
                                                            <a href="adminGrades.php?tab=students&student_id=<?php echo (int)$u['id']; ?>" class="btn-table-action" title="View Student Academic Grades">
                                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                                                                <span>Grades</span>
                                                            </a>
                                                        <?php endif; ?>
                                                        <?php if (!$isVerified): ?>
                                                            <form method="POST" action="../../controllers/admin/manageUser.php" style="display:inline;" onsubmit="return confirm('Accept and activate verification for <?php echo htmlspecialchars($u['name'] . ' ' . ($u['lastname'] ?? '')); ?>?');">
                                                                <?php echo csrfField(); ?>
                                                                <input type="hidden" name="action" value="verify">
                                                                <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                                                                <button type="submit" class="btn-table-action btn-verify" title="Accept and activate verification" style="background:#15803d; color:#ffffff; border-color:#15803d; font-weight:600;">
                                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                                                    <span>Approve</span>
                                                                </button>
                                                            </form>
                                                        <?php else: ?>
                                                            <form method="POST" action="../../controllers/admin/manageUser.php" style="display:inline;" onsubmit="return confirm('Reset temporary password for <?php echo htmlspecialchars($u['name'] . ' ' . ($u['lastname'] ?? '')); ?> to password123?');">
                                                                <?php echo csrfField(); ?>
                                                                <input type="hidden" name="action" value="reset_password">
                                                                <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                                                                <button type="submit" class="btn-table-action" title="Reset Temporary Password">
                                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="7.5" cy="15.5" r="5.5"/><path d="m21 2-9.6 9.6"/><path d="m15.5 7.5 3 3L22 7l-3-3"/></svg>
                                                                    <span>Reset</span>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        <?php if ((int)$u['id'] !== (int)$user['id']): ?>
                                                            <a href="adminUsers.php?confirm_delete_id=<?php echo (int)$u['id']; ?>" class="btn-table-action" style="color:#dc2626;" title="Delete User Account">
                                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                                <span>Delete</span>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" style="text-align:center; padding:24px; color:#64748b;">No users registered yet.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="table-footer">
                            <span id="recordCount">Showing <?php echo count($usersList); ?> user(s)</span>
                        </div>
                    </section>
                </div>
            </main>
        </div>

        <!-- Edit User Modal (Pure PHP) -->
        <?php if ($userToEdit): ?>
            <div id="editUserModal" class="modal-overlay" style="display:flex;">
                <div class="modal-card">
                    <div class="modal-header">
                        <div>
                            <h3 style="font-size:16px; font-weight:700; color:#0f172a;">Edit User Profile</h3>
                            <p style="font-size:12px; color:#64748b; margin-top:2px;">Modify member details, academic role, and verification status.</p>
                        </div>
                        <a href="adminUsers.php" class="btn-modal-close" style="text-decoration:none;">&times;</a>
                    </div>
                    <form action="../../controllers/admin/manageUser.php" method="POST" style="margin-top:18px;"><?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="update_user">
                        <input type="hidden" name="user_id" value="<?php echo (int)$userToEdit['id']; ?>">

                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:14px;">
                            <div>
                                <label for="edit_name" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:6px;">First Name *</label>
                                <input type="text" id="edit_name" name="name" class="modal-field" value="<?php echo htmlspecialchars($userToEdit['name']); ?>" required>
                            </div>
                            <div>
                                <label for="edit_lastname" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:6px;">Last Name</label>
                                <input type="text" id="edit_lastname" name="lastname" class="modal-field" value="<?php echo htmlspecialchars($userToEdit['lastname'] ?? ''); ?>">
                            </div>
                        </div>

                        <div style="margin-bottom:14px;">
                            <label for="edit_email" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:6px;">Institutional Email *</label>
                            <input type="email" id="edit_email" name="email" class="modal-field" value="<?php echo htmlspecialchars($userToEdit['email']); ?>" required>
                        </div>

                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:14px;">
                            <div>
                                <label for="edit_role" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:6px;">Academic Role *</label>
                                <select id="edit_role" name="role" class="modal-field" required>
                                    <option value="4" <?php echo ((int)$userToEdit['role'] === 4) ? 'selected' : ''; ?>>Student</option>
                                    <option value="3" <?php echo ((int)$userToEdit['role'] === 3) ? 'selected' : ''; ?>>Teacher / Faculty</option>
                                    <option value="2" <?php echo ((int)$userToEdit['role'] === 2) ? 'selected' : ''; ?>>Director</option>
                                    <option value="1" <?php echo ((int)$userToEdit['role'] === 1) ? 'selected' : ''; ?>>Administrator</option>
                                </select>
                            </div>
                            <div>
                                <label for="edit_verified" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:6px;">Account Status</label>
                                <select id="edit_verified" name="verified" class="modal-field">
                                    <option value="1" <?php echo ((int)$userToEdit['verified'] === 1) ? 'selected' : ''; ?>>Verified (Active)</option>
                                    <option value="0" <?php echo ((int)$userToEdit['verified'] === 0) ? 'selected' : ''; ?>>Pending Verification</option>
                                </select>
                            </div>
                        </div>

                        <div id="editDeptGroup" style="margin-bottom:20px; <?php echo ((int)$userToEdit['role'] === 2) ? '' : 'display:none;'; ?>">
                            <label for="edit_department_id" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:6px;">Assigned Department (for Directors)</label>
                            <select id="edit_department_id" name="department_id" class="modal-field">
                                <option value="">None / Not Applicable</option>
                                <?php foreach ($departmentsList as $d): ?>
                                    <option value="<?php echo (int)$d['id']; ?>" <?php echo ((int)($userToEdit['department_id'] ?? 0) === (int)$d['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($d['department']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div id="editCourseGroup" style="margin-bottom:20px; <?php echo ((int)$userToEdit['role'] === 3 || (int)$userToEdit['role'] === 4) ? '' : 'display:none;'; ?>">
                            <label for="edit_course_id" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:6px;">Assigned Course (for Teachers & Students)</label>
                            <select id="edit_course_id" name="course_id" class="modal-field">
                                <option value="">None / Unassigned</option>
                                <?php foreach ($allCoursesList as $c): ?>
                                    <option value="<?php echo (int)$c['id']; ?>" <?php echo ((int)($userToEdit['course_id'] ?? 0) === (int)$c['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c['course_name'] . (!empty($c['department_name']) ? ' — ' . $c['department_name'] : '')); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div style="display:flex; justify-content:flex-end; gap:10px;">
                            <a href="adminUsers.php" class="btn btn-secondary" style="text-decoration:none;">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const roleSelect = document.getElementById('edit_role');
                    const deptGroup = document.getElementById('editDeptGroup');
                    const courseGroup = document.getElementById('editCourseGroup');
                    if (roleSelect && deptGroup && courseGroup) {
                        roleSelect.addEventListener('change', function() {
                            const role = parseInt(this.value, 10);
                            deptGroup.style.display = (role === 2) ? 'block' : 'none';
                            courseGroup.style.display = (role === 3 || role === 4) ? 'block' : 'none';
                        });
                    }
                });
            </script>
        <?php endif; ?>

        <!-- Delete User Confirmation Modal (Pure PHP) -->
        <?php if ($userToDelete): ?>
            <div id="deleteUserModal" class="modal-overlay" style="display:flex;">
                <div class="modal-card" style="max-width:440px;">
                    <div class="modal-header">
                        <div>
                            <h3 style="font-size:16px; font-weight:700; color:#dc2626;">Confirm Deletion</h3>
                            <p style="font-size:12px; color:#64748b; margin-top:2px;">Are you sure you want to permanently delete this user account?</p>
                        </div>
                        <a href="adminUsers.php" class="btn-modal-close" style="text-decoration:none;">&times;</a>
                    </div>
                    <div style="background:#fef2f2; border:1px solid #fecdd3; border-radius:8px; padding:12px 14px; margin:16px 0; font-size:13px; color:#991b1b;">
                        <strong>User:</strong> <?php echo htmlspecialchars($userToDelete['name'] . ' ' . ($userToDelete['lastname'] ?? '')); ?> (<code>#<?php echo (int)$userToDelete['id']; ?></code>)<br>
                        <strong>Email:</strong> <?php echo htmlspecialchars($userToDelete['email']); ?><br>
                        <strong>Role:</strong> <?php echo htmlspecialchars($userToDelete['role_name']); ?>
                    </div>
                    <form action="../../controllers/admin/manageUser.php" method="POST" style="display:flex; justify-content:flex-end; gap:10px;">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="delete_user">
                        <input type="hidden" name="user_id" value="<?php echo (int)$userToDelete['id']; ?>">
                        <a href="adminUsers.php" class="btn btn-secondary" style="text-decoration:none;">Cancel</a>
                        <button type="submit" class="btn btn-primary" style="background:#dc2626; border-color:#dc2626;">Permanently Delete</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </body>
</html>
