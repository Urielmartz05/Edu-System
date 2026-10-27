<?php
    require_once __DIR__ . "/../../controllers/auth/check_auth.php";
    requireRole([4]);

    require_once __DIR__ . "/../../models/studentModels/studentCoursesModel.php";

    $studentCoursesModel = new StudentCourses();
    $totalBlocks = $studentCoursesModel->getBlocksCount();
    $overallProgress = $studentCoursesModel->getCourseProgress();
    $credits = $studentCoursesModel->getCourseCredits();
    $course = $studentCoursesModel->getCourse();
    $blocks = $studentCoursesModel->getBlocks();

    // Pure PHP search filter for modules and files
    $search = trim($_GET['search'] ?? '');
    if (!empty($search)) {
        $blocks = array_filter($blocks, function($b) use ($search) {
            $hasInFile = false;
            foreach ($b['files'] ?? [] as $f) {
                if (stripos($f['file_name'] ?? '', $search) !== false) {
                    $hasInFile = true;
                    break;
                }
            }
            return stripos($b['block_name'] ?? '', $search) !== false
                || stripos($b['teacher_name'] ?? '', $search) !== false
                || $hasInFile;
        });
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Modules · Edu-System</title>
    <link rel="stylesheet" href="alumnoCourses.css">
</head>
<body>
    <?php include __DIR__ . '/../../../ui/header/header.php'; ?>
    <?php include __DIR__ . '/../../../components/studentSidebar/studentSidebar.php'; ?>

    <div class="workspace">
        <main>
            <div id="page-content">

                <!-- Heading -->
                <section class="page-heading">
                    <div>
                        <span class="eyebrow">ACADEMIC PROGRAM · <?php echo htmlspecialchars($course['course_name'] ?? 'Academic Program'); ?></span>
                        <h1>Syllabus & Course Modules</h1>
                        <p class="subtitle">Explore instructional blocks, curriculum topics, assigned faculty, and completion progress for your active course.</p>
                    </div>
                </section>

                <!-- Quick KPI Metrics -->
                <section class="kpi-grid">
                    <article class="kpi">
                        <div class="kpi-header">
                            <h2 class="kpi-label">Total Modules</h2>
                            <div class="kpi-icon-wrap icon-maroon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value"><?php echo $totalBlocks; ?></strong>
                        <span class="kpi-hint">Modules in syllabus</span>
                    </article>
                    <article class="kpi">
                        <div class="kpi-header">
                            <h2 class="kpi-label">Cumulative Progress</h2>
                            <div class="kpi-icon-wrap icon-emerald">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value"><?php echo $overallProgress; ?>%</strong>
                        <div class="kpi-progress-bar">
                            <div class="kpi-progress-fill" style="width: <?php echo min(100, max(0, (float)$overallProgress)); ?>%;"></div>
                        </div>
                    </article>
                    <article class="kpi">
                        <div class="kpi-header">
                            <h2 class="kpi-label">Credits</h2>
                            <div class="kpi-icon-wrap icon-blue">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value"><?php echo $credits; ?></strong>
                        <span class="kpi-hint">Academic weight</span>
                    </article>
                    <article class="kpi">
                        <div class="kpi-header">
                            <h2 class="kpi-label">Course Status</h2>
                            <div class="kpi-icon-wrap icon-emerald">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                        </div>
                        <strong class="kpi-value"><?php echo ((int)($course['status'] ?? 0) === 1) ? 'Active' : 'Inactive'; ?></strong>
                        <span class="kpi-hint">Enrolled status</span>
                    </article>
                </section>

                <!-- Main Modules Panel -->
                <section class="panel">
                    <div class="panel-head">
                        <div>
                            <h2>Curriculum Modules</h2>
                            <p>Structured list of learning blocks scheduled for this course program.</p>
                        </div>
                        <span class="badge-tag">Online Modality</span>
                    </div>

                    <div class="filters-bar">
                        <form method="GET" action="alumnoCourses.php" class="search-wrap" style="width:100%; max-width:420px; display:flex; align-items:center;">
                            <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <input type="search" name="search" class="search-input" id="moduleSearch" placeholder="Search module by title or instructor..." value="<?php echo htmlspecialchars($search); ?>">
                        </form>
                    </div>

                    <div class="blocks-classified-list">
                        <?php if (!empty($blocks)): ?>
                            <?php foreach ($blocks as $block):
                                $blockDone = !empty($block['is_completed']);
                                $blockFiles = $block['files'] ?? [];
                                $filesCount = count($blockFiles);
                            ?>
                                <article class="module-classified-card">
                                    <!-- Module Header -->
                                    <div class="module-card-header">
                                        <div class="module-title-area">
                                            <div class="module-badges-row">
                                                <span class="block-badge">Block #<?php echo sprintf('%02d', (int)$block['id']); ?></span>
                                                <span class="teacher-tag">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                                    <span>Prof. <?php echo htmlspecialchars($block['teacher_name'] ?? 'Instructor'); ?></span>
                                                </span>
                                            </div>
                                            <h3 class="module-name"><?php echo htmlspecialchars($block['block_name']); ?></h3>
                                        </div>

                                        <div class="module-header-actions">
                                            <?php if ($blockDone): ?>
                                                <span class="status-badge-completed">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                    <span>Completed</span>
                                                </span>
                                            <?php else: ?>
                                                <span class="status-badge-pending">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                                                    <span>In Progress</span>
                                                </span>
                                            <?php endif; ?>

                                            <a href="blockContent.php?block_id=<?php echo urlencode($block['id']); ?>" class="btn-action" style="text-decoration:none;" title="View comprehensive block syllabus and deliverables">
                                                <span>View details</span>
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Classified Files Section for this Block -->
                                    <div class="module-files-box">
                                        <div class="module-files-title">
                                            <div style="display:flex; align-items:center; gap:6px;">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                                <span>Attached Learning Materials & PDFs</span>
                                            </div>
                                            <span class="files-counter-tag"><?php echo $filesCount; ?> Document<?php echo $filesCount === 1 ? '' : 's'; ?></span>
                                        </div>

                                        <?php if (!empty($blockFiles)): ?>
                                            <div class="classified-files-grid">
                                                <?php foreach ($blockFiles as $file): ?>
                                                    <div class="classified-file-card">
                                                        <div class="file-card-info">
                                                            <div class="file-type-badge"><?php echo htmlspecialchars(strtoupper(pathinfo($file['file_path'], PATHINFO_EXTENSION)) ?: 'FILE'); ?></div>
                                                            <div class="file-names-group">
                                                                <span class="file-primary-name" title="<?php echo htmlspecialchars($file['file_name']); ?>">
                                                                    <?php echo htmlspecialchars($file['file_name']); ?>
                                                                </span>
                                                                <span class="file-secondary-meta">Curricular Resource · Block #<?php echo (int)$block['id']; ?></span>
                                                            </div>
                                                        </div>

                                                        <div class="file-card-actions">
                                                            <a href="../../controllers/files/downloadFile.php?file_id=<?php echo (int)$file['id']; ?>&mode=download" class="btn-file-download" title="Direct download to disk">
                                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                                                <span>Download</span>
                                                            </a>
                                                            <a href="../../controllers/files/downloadFile.php?file_id=<?php echo (int)$file['id']; ?>&mode=view" target="_blank" class="btn-file-open" title="Open in new browser tab">
                                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                                                <span>Open</span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="empty-block-files">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                                <span>No study materials uploaded for this block yet.</span>
                                            </div>
                                        <?php endif; ?>

                                        <?php
                                            $blockLinks = $block['links'] ?? [];
                                            if (!empty($blockLinks)):
                                        ?>
                                            <div style="margin-top: 18px; border-top: 1px dashed #e2e8f0; padding-top: 14px;">
                                                <div class="module-files-title">
                                                    <div style="display:flex; align-items:center; gap:6px;">
                                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                                        <span>Web Resources & Video Lectures</span>
                                                    </div>
                                                    <span class="files-counter-tag"><?php echo count($blockLinks); ?> Link<?php echo count($blockLinks) === 1 ? '' : 's'; ?></span>
                                                </div>
                                                <div class="classified-files-grid">
                                                    <?php foreach ($blockLinks as $link): ?>
                                                        <div class="classified-file-card">
                                                            <div class="file-card-info">
                                                                <div class="file-type-badge" style="background:#eff6ff; color:#2563eb; border-color:#bfdbfe;">
                                                                    <?php echo htmlspecialchars(strtoupper($link['link_type'] ?: 'LINK')); ?>
                                                                </div>
                                                                <div class="file-names-group">
                                                                    <span class="file-primary-name" title="<?php echo htmlspecialchars($link['title']); ?>">
                                                                        <?php echo htmlspecialchars($link['title']); ?>
                                                                    </span>
                                                                    <span class="file-secondary-meta"><?php echo htmlspecialchars(parse_url($link['url'], PHP_URL_HOST) ?: 'External Resource'); ?></span>
                                                                </div>
                                                            </div>
                                                            <div class="file-card-actions">
                                                                <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" rel="noopener noreferrer" class="btn-file-download" style="background:#0284c7; text-decoration:none;" title="Open external web resource">
                                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                                                    <span>Open Link</span>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-modules-box">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                <p>No curriculum modules matched your search criteria.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
