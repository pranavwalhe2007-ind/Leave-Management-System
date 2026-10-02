<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This administrator setup script can only run from the command line.\n");
    exit(1);
}

require_once __DIR__ . '/../includes/auth.php';

$name = trim($argv[1] ?? '');
$email = trim($argv[2] ?? '');
$employeeCode = trim($argv[3] ?? '');
$password = getenv('INITIAL_ADMIN_PASSWORD');

if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$employeeCode) {
    fwrite(STDERR, 'Usage: php scripts/create-admin.php "Full Name" admin@example.com ADM-0001' . PHP_EOL);
    exit(2);
}

if (!is_string($password) || strlen($password) < 12) {
    putenv('INITIAL_ADMIN_PASSWORD');
    fwrite(STDERR, "Set INITIAL_ADMIN_PASSWORD to a password of at least 12 characters before running this script.\n");
    exit(2);
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);
unset($password);
putenv('INITIAL_ADMIN_PASSWORD');

try {
    if ((int) db()->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn() > 0) {
        throw new RuntimeException('An administrator already exists. Refusing to create another initial admin.');
    }

    $statement = db()->prepare(
        "INSERT INTO users (employee_code, full_name, email, password_hash, department, job_title, role, avatar_initials)
         VALUES (?, ?, ?, ?, 'People & Culture', 'HR Manager', 'admin', ?)"
    );
    $statement->execute([$employeeCode, $name, $email, $passwordHash, make_avatar_initials($name)]);
    fwrite(STDOUT, "Initial administrator created successfully.\n");
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
