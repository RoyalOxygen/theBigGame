# National Relief Fund Card (NARFCARD)

Production-oriented PHP application for the NARFCARD Programme Secretariat, an independent civic relief initiative.

This platform is not a website of the Federal Government of Nigeria, any Federal Ministry, or any State Government unless a formal authorisation is published on the site.

## Requirements

- PHP 8.2 or later with extensions: pdo_mysql, mbstring, curl, gd, zip, intl
- MySQL 8 or MariaDB 10.11
- Composer
- Optional: SMTP credentials (PHPMailer) and Paystack keys

The application runs on XAMPP and on standard PHP hosting.

## Shared hosting (cPanel / subdomain such as nerf.oxygendata.com.ng)

A blank HTTP 500 after extracting files is usually caused by `.htaccess` or PHP version, not by the test domain name.

1. Upload the `narfcard` folder so `index.php` is the subdomain document root.
2. Set the subdomain PHP version to **8.0, 8.1 or 8.2** (Select PHP Version). PHP 7.x will fail.
3. In cPanel, create an empty MySQL database and user, and add the user to the database.
4. Open `https://your-domain/install.php` in the browser. Until this is done, the public site redirects here.
5. Enter MySQL host, database name, username and password, plus the Super Administrator name, email and password. Click **Install NARFCARD**.
6. The installer writes `.env`, creates every table, loads states/LGAs/banks/settings, and creates that Super Administrator.
7. Sign in at `/admin/login.php` with the Super Administrator email you entered. Finish programme setup (contact, SMTP, Paystack, fees) from Settings.
8. Delete `install.php` from the server.

Applicants never use the installer. They open the public website, create an account, apply for a grant, and wait for review.

If the site still returns 500 after extracting:

1. Open `https://nerf.oxygendata.com.ng/health.php` first. That file is PHP 7-safe and reports the real problem.
2. If `health.php` also 500s, rename `.htaccess` to `.htaccess.bak` and reload. Then switch the subdomain to PHP 8.1 in cPanel MultiPHP Manager.
3. Confirm the subdomain document root is the folder that contains `index.php`, not the parent folder.
4. Confirm `storage` and `uploads` are writable (`0755` or `0775`).
5. Then open `install.php`.

Composer and SSH are not required for this install path. `includes/autoload.php` loads the application if `vendor/` is incomplete.

## XAMPP installation

1. Copy the `narfcard` folder into `C:\xampp\htdocs\` (Windows) or `/opt/lampp/htdocs/` (Linux).
2. Start Apache and MySQL in the XAMPP control panel.
3. Create the database:

```
CREATE DATABASE narfcard CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'narfcard'@'localhost' IDENTIFIED BY 'choose-a-strong-password';
GRANT ALL PRIVILEGES ON narfcard.* TO 'narfcard'@'localhost';
FLUSH PRIVILEGES;
```

4. Import the schema:

```
mysql -u narfcard -p narfcard < database/schema.sql
```

5. Copy `.env.example` to `.env` and set `DB_*` and `APP_URL` (for XAMPP, typically `http://localhost/narfcard`).
6. Install PHP dependencies:

```
composer install --no-dev
```

7. Open `http://localhost/narfcard/install.php`, enter MySQL credentials and the Super Administrator login, then click **Install NARFCARD**.

Alternatively, from the command line after `.env` is set:

```
NARFCARD_ADMIN_EMAIL=you@example.com NARFCARD_ADMIN_PASSWORD='your-password' NARFCARD_ADMIN_FIRST=Your NARFCARD_ADMIN_LAST=Name php database/seed.php
```

8. Open `http://localhost/narfcard` in a browser.

Applicants only register, apply and wait for approval. The homepage **Click to install** button is a browser home-screen shortcut (PWA). It does not run the operator installer.

On Apache, `AllowOverride All` must be enabled so `.htaccess` rules apply.

## Super Administrator

There is no default admin password. The first Super Administrator is created on `install.php` from the email and password you enter.

Applicant registration cannot create administrator accounts.

## Super Administrator settings

After install, sign in at `/admin/login.php` and open **Settings**. From the browser you can update:

- Site display: programme name, homepage copy, logo, favicon, colours
- Operator and contact details
- Programme rules: registration open/closed, grant min/max, site banner
- Payments: Paystack keys, card processing and delivery fees
- Email delivery: SMTP host, credentials, from address
- Email templates: subjects and bodies (leave blank to keep the built-in templates)

Applicants never see this page. You do not need to edit `.env` or PHP files for these changes.

## SMTP configuration

You can set SMTP from **Settings > Email delivery**, or in `.env`:

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@narfcard.ng
MAIL_FROM_NAME=NARFCARD Programme
```

If `MAIL_MAILER=log` or SMTP credentials are empty, messages are written to `storage/emails/mailbox.log`. Use this during local development. Open that file to copy email verification links.

## Paystack configuration

Set in `.env`:

```
PAYSTACK_PUBLIC_KEY=pk_live_or_test
PAYSTACK_SECRET_KEY=sk_live_or_test
```

Initialisation happens on the server. Every checkout is verified server-side (`user/card-callback.php`). The webhook endpoint is `api/paystack-webhook.php` and checks `X-Paystack-Signature`.

If keys are left as placeholders, the application uses a clearly labelled development checkout. Simulated payments must never be treated as genuine in production.

Card issuance and delivery charges are optional and disclosed before payment. They are not a condition for viewing or receiving an approved grant. Bank transfer remains available as an alternative disbursement channel.

## Production deployment

1. Upload the project to your PHP host.
2. Point the document root at the `narfcard` folder (or keep it in a subdirectory and set `APP_URL` accordingly).
3. Set `APP_ENV=production`, `APP_DEBUG=false`, `HTTPS_ONLY=true`.
4. Generate a unique `APP_KEY`.
5. Use a strong database password and restrict remote MySQL access.
6. Configure real SMTP and Paystack live keys.
7. Ensure `storage/` and `uploads/` are writable by the web server and not publicly executable.
8. Enable HTTPS at the web server or load balancer.

## Security notes

- Passwords are hashed with `password_hash()`.
- Database access uses PDO prepared statements.
- CSRF tokens are required on state-changing forms.
- Sessions regenerate after login and expire on idle timeout.
- Login attempts are rate limited.
- Uploaded files are stored outside direct execution and served only to the owner or an administrator.

## Programme operator

NARFCARD Programme Secretariat  
Plot 14, Adeola Odeku Street, Victoria Island, Lagos, Nigeria  
info@narfcard.ng
