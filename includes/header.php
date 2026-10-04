<?php
require_once __DIR__ . '/auth.php';
$user = current_user();
$pageTitle = $pageTitle ?? 'Workspace';
$flash = consume_flash();
$unreadCount = 0;
if ($user) {
    $countStatement = db()->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
    $countStatement->execute([$user['id']]);
    $unreadCount = (int) $countStatement->fetchColumn();
}
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="google-site-verification" content="ocm2ul6JAdRgIVbwlETa1t1-yOjRZ9xNTbcgxpxlm3A" />
    <title><?= e($pageTitle) ?> · Northstar Leave</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(base_url('assets/css/style.css')) ?>">
    <?php
    $gaMeasurementId = getenv('GA_MEASUREMENT_ID') ?: ($_ENV['GA_MEASUREMENT_ID'] ?? '');
    if ($gaMeasurementId !== ''):
        ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($gaMeasurementId) ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?= e($gaMeasurementId) ?>');
    </script>
    <?php endif; ?>
</head>
<body>
<div class="app-shell">
    <?php if ($user): ?>
        <?php require __DIR__ . ($user['role'] === 'admin' ? '/admin-sidebar.php' : '/employee-sidebar.php'); ?>
    <?php endif; ?>
    <main class="main-content<?= $user ? '' : ' auth-main' ?>">
        <?php if ($user): ?>
        <header class="topbar">
            <button class="menu-toggle" type="button" data-sidebar-toggle aria-label="Open navigation">&#9776;</button>
            <div class="topbar-context"><span class="eyebrow">Northstar / <?= e(ucfirst($user['role'])) ?></span><h1><?= e($pageTitle) ?></h1></div>
            <div class="topbar-actions">
                <a class="notification-link" href="<?= e(base_url($user['role'] === 'admin' ? 'admin/notifications.php' : 'employee/notifications.php')) ?>" aria-label="Notifications">&#128276;<?php if ($unreadCount): ?><span><?= $unreadCount ?></span><?php endif; ?></a>
                <div class="user-chip"><span class="avatar avatar-small"><?= e($user['avatar_initials']) ?></span><div><strong><?= e($user['full_name']) ?></strong><small><?= e($user['job_title']) ?></small></div></div>
            </div>
        </header>
        <?php endif; ?>
        <?php if ($flash): ?><div class="alert alert-<?= e($flash['type']) ?> page-alert"><?= e($flash['message']) ?></div><?php endif; ?>
