<?php
require_once __DIR__ . '/includes/auth.php';

$user = current_user();
if ($user) {
    header('Location: ' . base_url($user['role'] === 'admin' ? 'admin/dashboard.php' : 'employee/dashboard.php'));
} else {
    header('Location: ' . base_url('login.php'));
}
exit;
