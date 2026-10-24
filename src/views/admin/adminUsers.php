<?php
    require_once __DIR__ . '/../../controllers/auth/check_auth.php';
    require_once __DIR__ . '/../../models/adminModels/adminUsersModel.php';
    require_once __DIR__ . '/../../models/adminModels/adminCoursesModel.php';
    $user = getLoggedUser();

    $adminUsers = new AdminUsers();
    $adminCourses = new AdminCourses();
    $totalUsers = $adminUsers->getTotalUsersCount();
    $studentsCount = $adminUsers->getStudentsCount();
    $academicStaffCount = $adminUsers->getAcademicStaffCount();
    $pendingUsersCount = $adminUsers->getPendingUsersCount();
    $usersList = $adminUsers->getAllUsers();
    $departmentsList = $adminCourses->getDepartmentsList();
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
        <link rel="stylesheet" href="users.css">
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
                        <div class="actions">
                            <a href="adminCreate.php?tab=student" class="btn btn-primary">
                                <svg style="width:15px; height:15px; stroke:currentColor; stroke-width:2.2;" viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                <span>Register Member</span>
                            </a>
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
                        <article class="kpi">
                            <h2 class="kpi-label">Pending Verification</h2>
                            <strong class="kpi-value"><?php echo $pendingUsersCount; ?></strong>
                        </article>
                    </section>
                   
                    <!-- Main Panel -->
                    <section class="panel">
                        <div class="panel-head">
                            <div>
                                <h2>User Directory</h2>
                                <p>View active accounts, institutional emails, assigned roles, and email verification states.</p>
                            </div>
                        </div>
                        <div class="filters">
                            <label class="search">
                                <span class="filter-label">Search by name, email or ID</span>
                                <input class="field" id="search" type="search" placeholder="Search by name, email or ID…" onkeyup="filterUsers()">
                            </label>
                            <label>
                                <span class="filter-label">Role</span>
                                <select id="roleFilter" onchange="filterUsers()">
                                    <option value="">All roles</option>
                                    <option value="Admin">Admin</option>
                                    <option value="Director">Director</option>
                                    <option value="Teacher">Teacher</option>
                                    <option value="Student">Student</option>
                                </select>
                            </label>
                            <label>
                                <span class="filter-label">Status</span>
                                <select id="statusFilter" onchange="filterUsers()">
                                    <option value="">All statuses</option>
                                    <option value="Verified">Verified</option>
                                    <option value="Pending">Pending</option>
                                </select>
                            </label>
                        </div>
                        <div class="table-wrap">
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
                                            <tr data-role="<?php echo $roleName; ?>" data-status="<?php echo $statusText; ?>">
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
                                                        <button type="button" class="btn-table-action" onclick="openEditUserModal(<?php echo htmlspecialchars(json_encode($u)); ?>)" title="Edit User Account">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                            <span>Edit</span>
                                                        </button>
                                                        <?php if (!$isVerified): ?>
                                                            <a href="../../controllers/admin/manageUser.php?action=verify&user_id=<?php echo urlencode($u['id']); ?>" class="btn-table-action btn-verify" title="Verify and activate account">
                                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                                                <span>Verify</span>
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="../../controllers/admin/manageUser.php?action=reset_password&user_id=<?php echo urlencode($u['id']); ?>" class="btn-table-action" onclick="return confirm('Reset password for <?php echo htmlspecialchars(addslashes($u['name'])); ?> to \'password123\'?')" title="Reset Temporary Password">
                                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="7.5" cy="15.5" r="5.5"/><path d="m21 2-9.6 9.6"/><path d="m15.5 7.5 3 3L22 7l-3-3"/></svg>
                                                                <span>Reset</span>
                                                            </a>
                                                        <?php endif; ?>
                                                        <?php if ((int)$u['id'] !== (int)$user['id']): ?>
                                                            <button type="button" class="btn-table-action" onclick="confirmDeleteUser(<?php echo (int)$u['id']; ?>, '<?php echo htmlspecialchars(addslashes($u['name'] . ' ' . ($u['lastname'] ?? ''))); ?>')" style="color:#dc2626;" title="Delete User Account">
                                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                                <span>Delete</span>
                                                            </button>
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

        <!-- Edit User Modal -->
        <div id="editUserModal" class="modal-overlay" style="display:none;">
            <div class="modal-card">
                <div class="modal-header">
                    <div>
                        <h3 style="font-size:16px; font-weight:700; color:#0f172a;">Edit User Profile</h3>
                        <p style="font-size:12px; color:#64748b; margin-top:2px;">Modify member details, academic role, and verification status.</p>
                    </div>
                    <button type="button" class="btn-modal-close" onclick="closeEditUserModal()">&times;</button>
                </div>
                <form action="../../controllers/admin/manageUser.php" method="POST" style="margin-top:18px;">
                    <input type="hidden" name="action" value="update_user">
                    <input type="hidden" name="user_id" id="edit_user_id">

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:14px;">
                        <div>
                            <label for="edit_name" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:6px;">First Name *</label>
                            <input type="text" id="edit_name" name="name" class="modal-field" required>
                        </div>
                        <div>
                            <label for="edit_lastname" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:6px;">Last Name</label>
                            <input type="text" id="edit_lastname" name="lastname" class="modal-field">
                        </div>
                    </div>

                    <div style="margin-bottom:14px;">
                        <label for="edit_email" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:6px;">Institutional Email *</label>
                        <input type="email" id="edit_email" name="email" class="modal-field" required>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:14px;">
                        <div>
                            <label for="edit_role" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:6px;">Academic Role *</label>
                            <select id="edit_role" name="role" class="modal-field" onchange="toggleEditDeptSelect()" required>
                                <option value="4">Student</option>
                                <option value="3">Teacher / Faculty</option>
                                <option value="2">Director</option>
                                <option value="1">Administrator</option>
                            </select>
                        </div>
                        <div>
                            <label for="edit_verified" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:6px;">Account Status</label>
                            <select id="edit_verified" name="verified" class="modal-field">
                                <option value="1">Verified (Active)</option>
                                <option value="0">Pending Verification</option>
                            </select>
                        </div>
                    </div>

                    <div id="editDeptGroup" style="display:none; margin-bottom:20px;">
                        <label for="edit_department_id" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:6px;">Assigned Department</label>
                        <select id="edit_department_id" name="department_id" class="modal-field">
                            <?php foreach ($departmentsList as $d): ?>
                                <option value="<?php echo (int)$d['id']; ?>">
                                    <?php echo htmlspecialchars($d['department']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="display:flex; justify-content:flex-end; gap:10px;">
                        <button type="button" class="btn btn-secondary" onclick="closeEditUserModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function filterUsers() {
                const query = document.getElementById('search').value.toLowerCase().trim();
                const role = document.getElementById('roleFilter').value;
                const status = document.getElementById('statusFilter').value;
                const rows = document.querySelectorAll('#usersTable tbody tr');
                let visibleCount = 0;

                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    const rowRole = row.getAttribute('data-role') || '';
                    const rowStatus = row.getAttribute('data-status') || '';
                    const matchesQuery = text.includes(query);
                    const matchesRole = !role || rowRole === role;
                    const matchesStatus = !status || rowStatus === status;

                    if (matchesQuery && matchesRole && matchesStatus) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                document.getElementById('recordCount').textContent = `Showing ${visibleCount} user(s)`;
            }

            function toggleEditDeptSelect() {
                const role = document.getElementById('edit_role').value;
                const deptGroup = document.getElementById('editDeptGroup');
                if (deptGroup) {
                    deptGroup.style.display = (role === '2') ? 'block' : 'none';
                }
            }

            function openEditUserModal(user) {
                document.getElementById('edit_user_id').value = user.id;
                document.getElementById('edit_name').value = user.name;
                document.getElementById('edit_lastname').value = user.lastname || '';
                document.getElementById('edit_email').value = user.email;
                document.getElementById('edit_role').value = user.role;
                document.getElementById('edit_verified').value = user.verified;
                
                if (user.department_id) {
                    document.getElementById('edit_department_id').value = user.department_id;
                }
                toggleEditDeptSelect();

                document.getElementById('editUserModal').style.display = 'flex';
            }

            function closeEditUserModal() {
                document.getElementById('editUserModal').style.display = 'none';
            }

            function confirmDeleteUser(userId, userName) {
                if (confirm(`Are you sure you want to permanently delete user "${userName}" (#${userId})?\n\nThis will remove their account and related student/portal records.`)) {
                    window.location.href = `../../controllers/admin/manageUser.php?action=delete_user&user_id=${encodeURIComponent(userId)}`;
                }
            }

            window.addEventListener('click', function(e) {
                const modal = document.getElementById('editUserModal');
                if (e.target === modal) {
                    closeEditUserModal();
                }
            });

            window.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeEditUserModal();
                }
            });
        </script>
    </body>
</html>
