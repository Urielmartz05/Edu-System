<?php
    // ==========================================
    // VIEW: USER PROFILE & PASSWORD SECURITY
    // ==========================================

    require_once __DIR__ . "/../../controllers/auth/check_auth.php";
    requireRole([1, 2, 3, 4]);
    $user = getLoggedUser();

    include_once __DIR__ . "/../../controllers/role/getRoleType.php";
    $roleType = getRoleType($user['role']);

    $flashSuccess = $_SESSION['flash_profile_success'] ?? null;
    $flashError = $_SESSION['flash_profile_error'] ?? null;
    unset($_SESSION['flash_profile_success'], $_SESSION['flash_profile_error']);

    $initial = strtoupper(substr($user['name'] ?? 'U', 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile & Security · EduX</title>
    <link rel="stylesheet" href="profile.css">
</head>
<body>
    <?php include_once __DIR__ . '/../../../ui/header/header.php'; ?>

    <?php 
        $roleId = (int)$user['role'];
        if ($roleId === 1) {
            include_once __DIR__ . '/../../../ui/adminSideBar/adminSideBar.php';
        } elseif ($roleId === 2) {
            include_once __DIR__ . '/../../../components/directorSidebar/directorSidebar.php';
        } elseif ($roleId === 3) {
            include_once __DIR__ . '/../../../components/teacherSidebar/teacherSidebar.php';
        } else {
            include_once __DIR__ . '/../../../components/studentSidebar/studentSidebar.php';
        }
    ?>

    <div class="workspace">
        <main>
            <div id="page-content">
                <!-- Page Heading Hero (100% Free of Buttons) -->
                <section class="page-heading">
                    <div>
                        <span class="eyebrow">ACCOUNT & SECURITY SETTINGS</span>
                        <h1>Personal Profile & Security</h1>
                        <p class="subtitle">Manage your personal account credentials, review institutional role details, and change your password.</p>
                    </div>
                </section>

                <!-- Feedback Notifications -->
                <?php if ($flashSuccess): ?>
                    <div class="alert alert-success">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        <span><?php echo htmlspecialchars($flashSuccess); ?></span>
                    </div>
                <?php elseif ($flashError): ?>
                    <div class="alert alert-error">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span><?php echo htmlspecialchars($flashError); ?></span>
                    </div>
                <?php endif; ?>

                <div class="profile-layout-grid">
                    <!-- Left: Identity & Institutional Overview Card -->
                    <section class="panel profile-card">
                        <div class="panel-head">
                            <div>
                                <h2>Account Identity</h2>
                                <p>Verified institutional profile information</p>
                            </div>
                            <span class="badge-role badge-role-<?php echo strtolower($roleType ?: 'user'); ?>">
                                <?php echo htmlspecialchars(ucfirst(strtolower($roleType ?: 'User'))); ?>
                            </span>
                        </div>

                        <div class="panel-body profile-identity-body">
                            <div class="profile-avatar-circle">
                                <span><?php echo $initial; ?></span>
                            </div>

                            <div class="profile-user-names">
                                <h3><?php echo htmlspecialchars($user['name'] . ' ' . ($user['lastname'] ?? '')); ?></h3>
                                <p class="profile-user-email"><?php echo htmlspecialchars($user['email']); ?></p>
                            </div>

                            <div class="profile-meta-list">
                                <div class="meta-row">
                                    <span class="meta-label">User Identifier</span>
                                    <span class="meta-val">#<?php echo (int)$user['id']; ?></span>
                                </div>
                                <div class="meta-row">
                                    <span class="meta-label">System Role</span>
                                    <span class="meta-val"><?php echo htmlspecialchars(ucfirst(strtolower($roleType ?: 'User'))); ?></span>
                                </div>
                                <div class="meta-row">
                                    <span class="meta-label">Verification Status</span>
                                    <span class="status-verified-badge">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                        <span>Verified</span>
                                    </span>
                                </div>
                                <div class="meta-row">
                                    <span class="meta-label">Portal Access</span>
                                    <span class="meta-val" style="color:#15803d; font-weight:600;">Active Session</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Right: Change Password Form Card -->
                    <section class="panel security-card">
                        <div class="panel-head">
                            <div>
                                <h2>Password & Security</h2>
                                <p>Update your authentication password to keep your account secure</p>
                            </div>
                            <div class="security-shield-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            </div>
                        </div>

                        <div class="panel-body">
                            <form action="../../controllers/profile/updatePassword.php" method="POST" class="profile-password-form">
                                <?php echo csrfField(); ?>

                                <div class="form-group">
                                    <label for="current_password">Current Password *</label>
                                    <input type="password" name="current_password" id="current_password" class="form-control" placeholder="Enter your current password" required autocomplete="current-password">
                                    <span class="form-hint">Required to verify account ownership before updating.</span>
                                </div>

                                <div class="form-group">
                                    <label for="new_password">New Password *</label>
                                    <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Enter new password (min. 6 characters)" required minlength="6" autocomplete="new-password">
                                    <span class="form-hint">Use a combination of letters, numbers, and symbols for high security.</span>
                                </div>

                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password *</label>
                                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Repeat your new password" required minlength="6" autocomplete="new-password">
                                </div>

                                <div class="form-actions-row">
                                    <button type="submit" class="btn-save-password">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                        <span>Update Password</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
