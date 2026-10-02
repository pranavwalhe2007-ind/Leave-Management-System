<?php
require_once __DIR__ . '/includes/auth.php';
if (current_user()) {
    header('Location: ' . base_url());
    exit;
}
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (login_user(trim($_POST['email'] ?? ''), $_POST['password'] ?? '')) {
        header('Location: ' . base_url());
        exit;
    }
    $error = 'We could not match those credentials. Please check your email and password.';
}
$pageTitle = 'sign in';
require __DIR__ . '/includes/header.php';
?>
<section class="login-page"><div class="login-visual"><div class="visual-copy"><span class="eyebrow">Northstar / People operations</span><h1>Make room for the work that matters.</h1><p>A calmer, clearer way to plan time away and keep your team moving.</p></div><div class="visual-stamp">EST.<br><strong>2026</strong></div></div><div class="login-panel"><div class="login-form-wrap"><span class="brand-mark">N</span><span class="eyebrow">Welcome back</span><h2>Sign in to your workspace</h2><p class="muted">Use your Northstar account to continue.</p><?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?><form method="post" class="stack-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><label>Email address<input type="email" name="email" placeholder="you@company.com" required autofocus></label><label>Password<input type="password" name="password" placeholder="Enter your password" required></label><button class="btn btn-primary btn-lg w-100" type="submit">Continue <span>&rarr;</span></button></form></div><footer class="login-footer">Northstar Leave Management <span>·</span> Secure workspace</footer></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
