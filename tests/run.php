<?php
declare(strict_types=1);

foreach ([
    'DB_HOST' => '127.0.0.1',
    'DB_NAME' => 'leave_management_test',
    'DB_USER' => 'test_user',
    'DB_PASS' => '',
] as $name => $value) {
    if (getenv($name) === false) {
        putenv("$name=$value");
    }
}

require_once __DIR__ . '/../includes/auth.php';

$tests = [
    'inclusive same-day leave duration' => calculate_days('2026-10-02', '2026-10-02') === 1.0,
    'inclusive multi-day leave duration' => calculate_days('2026-10-20', '2026-10-22') === 3.0,
    'reversed date range is rejected' => calculate_days('2026-10-22', '2026-10-20') === 0.0,
    'impossible calendar date is rejected' => calculate_days('2026-02-30', '2026-03-01') === 0.0,
    'malformed date is rejected' => calculate_days('not-a-date', '2026-10-22') === 0.0,
    'employee initials use first and last names' => make_avatar_initials('Alex Morgan') === 'AM',
    'single-name initials use two characters' => make_avatar_initials('Alex') === 'AL',
    'HTML output is escaped' => e('<script>') === '&lt;script&gt;',
];

$_SERVER['SCRIPT_NAME'] = '\\employee\\dashboard.php';
$tests['nested Windows script paths produce browser-safe URLs'] =
    base_url('employee/apply-leave.php') === '/employee/apply-leave.php';
$tests['login page does not publish demo credentials'] =
    strpos(file_get_contents(__DIR__ . '/../login.php'), 'northstar.local') === false;
$seedSql = file_get_contents(__DIR__ . '/../database.sql');
$tests['database seed does not create demo accounts'] =
    strpos($seedSql, 'INSERT INTO users') === false &&
    strpos($seedSql, 'alex@northstar.local') === false;

$failed = 0;
foreach ($tests as $name => $passed) {
    printf("%s %s\n", $passed ? 'PASS' : 'FAIL', $name);
    if (!$passed) {
        $failed++;
    }
}

printf("%d tests, %d failures\n", count($tests), $failed);
exit($failed === 0 ? 0 : 1);
