<?php
require_once __DIR__ . '/../includes/auth.php'; require_login('employee');
$user = current_user(); $error = null;
$types = db()->query('SELECT * FROM leave_types WHERE is_active = 1 ORDER BY name')->fetchAll();
$typeIds = array_map('intval', array_column($types, 'id'));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $start = is_string($_POST['start_date'] ?? null) ? $_POST['start_date'] : '';
    $end = is_string($_POST['end_date'] ?? null) ? $_POST['end_date'] : '';
    $reason = is_string($_POST['reason'] ?? null) ? trim($_POST['reason']) : '';
    $typeInput = $_POST['leave_type_id'] ?? null;
    $typeId = is_string($typeInput) && ctype_digit($typeInput) ? (int) $typeInput : 0;
    $days = calculate_days($start, $end);
    if (
        !$reason ||
        !$days ||
        $start < date('Y-m-d') ||
        !in_array($typeId, $typeIds, true)
    ) { $error = 'Please choose an active leave type, complete each field, and select a valid future date range.'; }
    else {
        $statement = db()->prepare('INSERT INTO leave_applications (user_id, leave_type_id, start_date, end_date, days, reason) VALUES (?, ?, ?, ?, ?, ?)');
        $statement->execute([$user['id'], $typeId, $start, $end, $days, $reason]);
        $adminIds = db()->query("SELECT id FROM users WHERE role = 'admin' AND is_active = 1")->fetchAll(PDO::FETCH_COLUMN);
        $notice = db()->prepare('INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)');
        foreach ($adminIds as $adminId) { $notice->execute([$adminId, 'New leave request', $user['full_name'] . ' submitted a ' . $days . '-day leave request.']); }
        flash('success', 'Your leave request has been sent to People & Culture.'); header('Location: ' . base_url('employee/leave-history.php')); exit;
    }
}
$pageTitle = 'Apply for leave'; require __DIR__ . '/../includes/header.php';
?><div class="page-content"><div class="detail-heading"><div><span class="eyebrow">Time away / New request</span><h2>Take the time you need.</h2><p class="muted mb-0">Give your team enough context to plan around your absence.</p></div></div><section class="panel form-card"><div class="panel-heading"><h3>Leave details</h3><span class="helper">All fields required</span></div><?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?><form method="post" class="stack-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><label>Leave type<select name="leave_type_id" required><option value="">Select a leave type</option><?php foreach ($types as $type): ?><option value="<?= $type['id'] ?>"><?= e($type['name']) ?> · <?= e($type['description']) ?></option><?php endforeach; ?></select></label><div class="row g-3"><div class="col-md-6"><label class="form-label">First day<input class="form-control" type="date" name="start_date" data-date-start min="<?= date('Y-m-d') ?>" required></label></div><div class="col-md-6"><label class="form-label">Last day<input class="form-control" type="date" name="end_date" data-date-end min="<?= date('Y-m-d') ?>" required></label></div></div><div class="helper">Requested duration: <strong data-days-output>Select your dates</strong>. Weekends are included in the request total.</div><label>Reason<textarea name="reason" placeholder="Add a short note about your request..." required></textarea></label><div class="form-section"><h3>Before you send</h3><p class="helper">Your request will be reviewed by People & Culture. You can track its status from your leave history.</p><button class="btn btn-primary" type="submit">Submit request <span>&rarr;</span></button></div></form></section></div><?php require __DIR__ . '/../includes/footer.php'; ?>
