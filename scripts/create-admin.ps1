param(
    [string]$PhpExecutable = 'C:\xampp\php\php.exe'
)

$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot

if (-not (Test-Path -LiteralPath $PhpExecutable)) {
    throw "PHP executable not found: $PhpExecutable"
}

foreach ($setting in @('DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS')) {
    if (-not (Test-Path "Env:$setting")) {
        throw "Set the $setting environment variable before running this script."
    }
}

$name = (Read-Host 'Administrator full name').Trim()
$email = (Read-Host 'Administrator email').Trim()
$employeeCode = (Read-Host 'Administrator employee code').Trim()
$password = Read-Host 'Administrator password (at least 12 characters)' -AsSecureString
$confirmation = Read-Host 'Confirm administrator password' -AsSecureString
$passwordPointer = [IntPtr]::Zero
$confirmationPointer = [IntPtr]::Zero
$exitCode = 1

try {
    $passwordPointer = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($password)
    $confirmationPointer = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($confirmation)
    $plainPassword = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($passwordPointer)
    $confirmedPassword = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($confirmationPointer)

    if ($plainPassword -cne $confirmedPassword) {
        throw 'The passwords do not match.'
    }
    if ($plainPassword.Length -lt 12) {
        throw 'The password must contain at least 12 characters.'
    }

    $env:INITIAL_ADMIN_PASSWORD = $plainPassword
    & $PhpExecutable (Join-Path $projectRoot 'scripts\create-admin.php') $name $email $employeeCode
    $exitCode = $LASTEXITCODE
}
finally {
    Remove-Item Env:INITIAL_ADMIN_PASSWORD -ErrorAction SilentlyContinue
    if ($passwordPointer -ne [IntPtr]::Zero) {
        [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($passwordPointer)
    }
    if ($confirmationPointer -ne [IntPtr]::Zero) {
        [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($confirmationPointer)
    }
    $plainPassword = $null
    $confirmedPassword = $null
    if ($password) {
        $password.Dispose()
    }
    if ($confirmation) {
        $confirmation.Dispose()
    }
}

exit $exitCode
