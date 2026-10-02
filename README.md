# Leave Management System

A PHP and MySQL leave-management website with employee and administrator workspaces.

## Technology stack

- **Frontend:** Server-rendered HTML, CSS, vanilla JavaScript, and Bootstrap 5.3.3 (loaded from a CDN)
- **Backend:** PHP 8.0 or later
- **Database:** MySQL with PDO and the `pdo_mysql` PHP extension
- **Authentication:** PHP sessions, password hashes, role checks, and CSRF tokens
- **API:** No separate API or frontend build framework is present

## Features

- Separate employee and administrator workspaces
- Leave request submission, review, and history
- Employee directory and leave policy management
- Profile and notification management
- Reports by leave type and department

## Project structure

```text
admin/                   Administrator pages
assets/                  Stylesheets and browser JavaScript
config/                  Database configuration
employee/                Employee pages
includes/                Authentication, shared layout, and navigation
scripts/                 Administrator bootstrap tooling
tests/                   PHP helper and HTTP smoke tests
.github/workflows/       CI and CodeQL workflows
database.sql             Schema and leave-type reference data
```

## Local setup

The app expects a MySQL database named `leave_management`. It reads `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASS` from the process environment. It does not parse `.env` files automatically.

1. Install/start PHP and MySQL (for example, using XAMPP).
2. For a **new, empty local database**, import `database.sql` once. It creates the schema and leave types. It intentionally does not create users or sample leave requests. Do not re-import it into an existing database.
3. In PowerShell, configure your local database connection and securely create the first administrator:

   ```powershell
   $env:DB_HOST = '127.0.0.1'
   $env:DB_NAME = 'leave_management'
   $env:DB_USER = 'your_local_database_user'
   $env:DB_PASS = 'your_local_database_password'
   .\scripts\create-admin.ps1
   ```

   The setup script prompts for the admin name, email, employee code, and a password entered without echoing it. It creates an admin only when the database has none. The PHP CLI utility is `scripts/create-admin.php`; it accepts the password through `INITIAL_ADMIN_PASSWORD` and never accepts it as a command-line argument.

4. Start the application in the same PowerShell session, so it inherits the database settings:

   ```powershell
   C:\xampp\php\php.exe -S 127.0.0.1:8000 -t .
   ```

5. Open <http://localhost:8000/>.

Create employee accounts after signing in as the administrator. No demo user credentials or password hashes are included in the public SQL seed.

## Environment variables and secrets

`.env.example` lists placeholders for reference; PHP does not load that file. Set the values in your shell or hosting provider's secret manager. Never put real credentials in `.env.example`, source control, or the SQL seed. Local `.env` files are excluded by `.gitignore`. Missing `DB_HOST`, `DB_NAME`, or `DB_USER` values cause an explicit configuration error. `DB_PASS` may be omitted only for a local MySQL account that actually has no password; set a strong non-empty password in production.

Production requires a managed MySQL service, a unique database user with limited privileges, and secret database credentials configured in the host's secret manager.

## Validation, tests, and build

PHP is interpreted, and this project has no package manager, frontend compiler, or production build step. `php -l` is the available PHP syntax lint; no style linter was present, and none was added so existing application code does not need a broad style rewrite.

Run the checks from the project root:

```powershell
C:\xampp\php\php.exe tests\run.php
C:\xampp\php\php.exe tests\http-smoke.php
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { C:\xampp\php\php.exe -l $_.FullName }
```

The helper tests cover inclusive leave-day calculations, invalid dates, URL generation on Windows, output escaping, avatar initials, and the absence of published demo credentials. The HTTP smoke test starts a temporary local PHP server and checks that the homepage reaches the sign-in screen. The smoke test does not require a running MySQL service; full authenticated workflows do.

## GitHub Actions

`.github/workflows/ci.yml` runs PHP syntax checks, helper tests, and the HTTP startup smoke test for pushes and pull requests. `.github/workflows/codeql.yml` schedules and runs GitHub CodeQL analysis for the JavaScript assets. `.github/dependabot.yml` checks GitHub Actions for updates; the PHP app currently has no Composer dependency manifest.

CI runs after this folder is uploaded to GitHub; workflow execution cannot be verified locally without a GitHub repository and Actions runner. Keep pull requests small and use descriptive commit prefixes such as `feat:`, `fix:`, `test:`, `ci:`, `build:`, `docs:`, and `chore:`.

## Deployment readiness

There is no frontend production build output to configure. **Deployment to Vercel is not yet verified:** choose and test a Vercel-compatible PHP runtime and managed MySQL service before deployment. Do not commit a Vercel configuration until the target runtime and database connection have been tested.

Otherwise, deploy to a host that supports PHP and MySQL directly. Configure the provider's generated HTTPS domain after deployment; no custom domain is needed.

## Docker

Docker is not included. This small PHP/MySQL app can run directly on a standard PHP host; Docker is not required for local development, and there is no Docker runtime available in the checked environment.

## Architecture

```text
Developer
   |
   v
Git -> GitHub
          |
          +--> GitHub Actions: PHP syntax -> helper tests -> HTTP smoke test
          +--> CodeQL: PHP source analysis
          |
          v
Compatible PHP + managed MySQL hosting
          |
          v
       Website
```

Vercel remains a possible target only after its PHP runtime and the remote database configuration are selected and verified.

## Security notes

- Keep local passwords, tokens, and `.env` files out of source control.
- The SQL setup intentionally creates no users. Provision the first administrator with the setup script and a unique, strong password.
- Use a least-privilege database account for the application. Do not use a database root account in production.
- Bootstrap and font assets load from third-party CDNs; production deployments should review their availability and integrity/security requirements.
- Enable GitHub secret scanning and branch protections after creating the repository. CodeQL findings appear in GitHub Security once its workflow has run.

## Troubleshooting

- **Database connection error:** Start MySQL, create/import `leave_management`, and confirm `config/database.php` matches the local MySQL credentials.
- **Login rejected:** The SQL file does not create accounts. Verify the first administrator was provisioned and is active.
- **Database configuration error:** Set `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASS` in the same shell/session that starts PHP.
- **PHP database driver missing:** Enable/install `pdo_mysql` for the PHP executable that runs the server.
- **Port already in use:** Choose another port in the PHP server command and open that matching localhost URL.
- **CI has not run:** Push the project to GitHub and enable Actions for the repository.
- **Vercel deployment requested:** Configure and verify a supported PHP runtime and a reachable managed MySQL database first.
