<?php
    require_once __DIR__ . "/../../controllers/auth/check_auth.php";
    header("Location: alumnoDashboard.php");
    exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Statistics · Edu-System</title>
    <link rel="stylesheet" href="stats.css">
</head>
<body>
    <?php include __DIR__ . '/../../../ui/header/header.php'; ?>
    <?php include __DIR__ . '/../../../components/studentSidebar/studentSidebar.php'; ?>

    <div class="workspace">
        <main>
            <div id="page-content">
                <!-- Page Heading -->
                <section class="page-heading">
                    <div>
                        <p class="eyebrow">ACADEMIC TRACKING</p>
                        <h1>My Academic Performance</h1>
                        <p class="subtitle">Monitor your course progress, syllabus block completion, and academic milestones.</p>
                    </div>
                    <span class="period-badge">Cycle 2026-B</span>
                </section>

                <!-- Key Metrics KPI Grid -->
                <section class="kpi-grid">
                    <article class="kpi">
                        <h2 class="kpi-label">Course Progress</h2>
                        <strong class="kpi-value"><?php echo $avgProgress; ?>%</strong>
                    </article>
                    <article class="kpi">
                        <h2 class="kpi-label">Active Modules</h2>
                        <strong class="kpi-value"><?php echo $totalBlocks; ?></strong>
                    </article>
                    <article class="kpi">
                        <h2 class="kpi-label">Assigned Instructors</h2>
                        <strong class="kpi-value"><?php echo $totalInstructors; ?></strong>
                    </article>
                    <article class="kpi">
                        <h2 class="kpi-label">Academic Status</h2>
                        <strong class="kpi-value" style="font-size: 22px; color: #10b981;">Active</strong>
                    </article>
                </section>

                <!-- Course Progress Summary Panel -->
                <section class="panel">
                    <div class="panel-head">
                        <div>
                            <h2>Enrolled Course Performance</h2>
                            <p><?php echo htmlspecialchars($progressData['course_name'] ?? 'Active Academic Program'); ?></p>
                        </div>
                    </div>
                    <div style="padding: 24px 28px;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-weight:600;">
                            <span>Cumulative Completion</span>
                            <span><?php echo $avgProgress; ?>%</span>
                        </div>
                        <div style="background:#e2e8f0; border-radius:8px; height:12px; overflow:hidden;">
                            <div style="width:<?php echo min(100, max(0, $avgProgress)); ?>%; background:#10b981; height:100%;"></div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
