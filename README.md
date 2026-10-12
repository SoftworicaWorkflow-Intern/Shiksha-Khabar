# Shiksha Khabar

Shiksha Khabar is a Laravel education-news portal for Nepal. It supports published articles, searchable category/tag listings, notices, scholarships, vacancies, and a role-protected editorial area. The application does not seed sample stories or create a default administrator account.

## Requirements

- PHP 8.3 or newer with the extensions required by Laravel (including `pdo_sqlite` for the default local database, or `pdo_mysql` for MySQL)
- Composer
- Node.js and npm
- MySQL 8+ for a production installation, or SQLite for local development

## Local installation

```powershell
composer install
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
php artisan key:generate
```

The example environment uses SQLite. Create its database file if it does not exist:

```powershell
New-Item -ItemType File -Path database\database.sqlite -Force
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
php artisan serve
```

Open the URL printed by `artisan serve`. Set `APP_URL` to the real URL before deploying. For MySQL, configure `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in the private `.env` file before migrating. Never commit `.env` or production credentials.

The database seeder creates the initial education categories only. To bootstrap the first administrator, use a trusted local terminal after migrations and choose a unique email and a strong password:

```powershell
php artisan tinker
```

At the Tinker prompt:

```php
\App\Models\User::create([
    'name' => 'Portal Administrator',
    'email' => 'admin@example.com',
    'password' => \Illuminate\Support\Facades\Hash::make('replace-with-a-unique-strong-password'),
    'role' => 'super_admin',
]);
```

Replace both example values before entering the command. Do not use this example password in a deployed environment. Administrators can create staff accounts and grant roles from **Admin → Users & roles**.

## Editorial workflow and roles

- **Super Admin / Administrator:** site settings, user administration (Super Admin only), and editorial management.
- **Editor:** manage categories, notices, media, and publish or schedule articles.
- **Author / Reporter:** create and manage their own articles; non-editor submissions are restricted to draft or editorial review.

Sign in at `/login`. Drafts, review items, archived content, and future-scheduled articles are not publicly visible. Scheduling is checked when public content is requested, so no queue worker is needed for the scheduled visibility check.

Article bodies are plain text, not trusted HTML; line breaks are preserved and output is escaped. Images are validated and stored with Laravel's generated storage names. Public uploaded files require the `storage:link` symlink.

## Production deployment

1. Set production-only environment values in the hosting environment or a protected `.env` file. Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, secure session/cookie settings, and production database/mail settings.
2. Install dependencies and compile frontend assets:

   ```sh
   composer install --no-dev --optimize-autoloader
   npm ci
   npm run build
   ```

3. Run database migrations during a controlled deployment:

   ```sh
   php artisan migrate --force
   php artisan storage:link
   php artisan optimize
   ```

4. Configure the web server document root to Laravel's `public` directory, enable HTTPS, and ensure `storage` and `bootstrap/cache` are writable by the application process.
5. Monitor Laravel logs and infrastructure health. Automated third-party news collection, comments, and newsletter delivery are not enabled; do not publish unverified official announcements.
6. Configure `APP_URL` to the production HTTPS origin; the generated `robots.txt` points search engines to that origin's sitemap.

## Backup and recovery

- Back up the production database before each schema change and on a regular schedule. For MySQL, use an encrypted, access-controlled `mysqldump`/managed database backup and test restores in a separate environment.
- Back up `storage/app/public` and any private storage separately from the database; article records alone do not contain the uploaded files.
- Keep backups encrypted, restrict access, define retention periods, and periodically restore both database and files to verify the recovery procedure.
- For SQLite deployments, stop writes or use SQLite's online backup mechanism before copying the database file.

## Quality checks

```powershell
php artisan test
npm run build
```

Public pages, search, article workflows, notice management, role checks, and uploads are covered by feature tests. Add verified editorial sources and configure a real contact address before public launch.
