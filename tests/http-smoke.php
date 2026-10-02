<?php
declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$socket = stream_socket_server('tcp://127.0.0.1:0', $errorCode, $errorMessage);
if (!$socket) {
    fwrite(STDERR, "Could not reserve a local test port: $errorMessage ($errorCode)\n");
    exit(1);
}

$address = stream_socket_get_name($socket, false);
fclose($socket);
if ($address === false) {
    fwrite(STDERR, "Could not determine the reserved local test port.\n");
    exit(1);
}
$port = (int) substr(strrchr($address, ':'), 1);
$logFile = tempnam(sys_get_temp_dir(), 'leave-management-http-');
if ($logFile === false) {
    fwrite(STDERR, "Could not create a temporary server log.\n");
    exit(1);
}

$serverEnvironment = getenv();
if (!is_array($serverEnvironment)) {
    $serverEnvironment = [];
}
foreach ([
    'DB_HOST' => '127.0.0.1',
    'DB_NAME' => 'leave_management_test',
    'DB_USER' => 'test_user',
    'DB_PASS' => '',
] as $name => $value) {
    if (!array_key_exists($name, $serverEnvironment)) {
        $serverEnvironment[$name] = $value;
    }
}

$process = proc_open(
    [PHP_BINARY, '-S', "127.0.0.1:$port", '-t', $projectRoot],
    [
        0 => ['pipe', 'r'],
        1 => ['file', $logFile, 'a'],
        2 => ['file', $logFile, 'a'],
    ],
    $pipes,
    $projectRoot,
    $serverEnvironment
);

if (!is_resource($process)) {
    unlink($logFile);
    fwrite(STDERR, "Could not start the PHP development server.\n");
    exit(1);
}

fclose($pipes[0]);
$context = stream_context_create([
    'http' => [
        'follow_location' => 1,
        'max_redirects' => 5,
        'timeout' => 2,
        'ignore_errors' => true,
    ],
]);
$url = "http://127.0.0.1:$port/";
$body = false;

for ($attempt = 0; $attempt < 30; $attempt++) {
    $body = @file_get_contents($url, false, $context);
    if (is_string($body) && strpos($body, 'Sign in to your workspace') !== false) {
        break;
    }
    usleep(100000);
}

$statusLines = $http_response_header ?? [];
$statusLine = '';
foreach ($statusLines as $line) {
    if (preg_match('/^HTTP\/\S+\s+(\d+)/', $line, $matches)) {
        $statusLine = $line;
        $statusCode = (int) $matches[1];
    }
}
$serverOutput = file_get_contents($logFile);
proc_terminate($process);
proc_close($process);
unlink($logFile);

if (!is_string($body) || strpos($body, 'Sign in to your workspace') === false || ($statusCode ?? 0) !== 200) {
    fwrite(STDERR, "Homepage smoke test failed for $url.\n");
    fwrite(STDERR, (string) $serverOutput);
    exit(1);
}

printf("PASS homepage redirects to the sign-in page (final response: %s)\n", $statusLine);
