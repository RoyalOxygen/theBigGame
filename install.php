<?php
/**
 * Operator web installer.
 * Enter MySQL credentials and the first Super Administrator account.
 * Applicants never use this page. They only register, apply, and wait for review.
 * Delete this file after a successful install.
 */

header('Content-Type: text/html; charset=utf-8');

$base = dirname(__FILE__);
$lockFile = $base . '/storage/installed.lock';
$errors = array();
$ok = array();
$installed = is_file($lockFile);
$phpId = defined('PHP_VERSION_ID') ? PHP_VERSION_ID : 0;
$phpOk = $phpId >= 80000;
$createdAdminEmail = '';

function install_h($v)
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

function install_write_env($base, $data)
{
    $env = "APP_NAME=\"National Relief Fund Card\"\n";
    $env .= "APP_ENV=production\n";
    $env .= "APP_DEBUG=false\n";
    $env .= "APP_URL=" . $data['url'] . "\n";
    $env .= "APP_KEY=" . $data['key'] . "\n\n";
    $env .= "DB_HOST=" . $data['host'] . "\n";
    $env .= "DB_PORT=" . $data['port'] . "\n";
    $env .= "DB_NAME=" . $data['name'] . "\n";
    $env .= "DB_USER=" . $data['user'] . "\n";
    $env .= "DB_PASS=" . $data['pass'] . "\n\n";
    $env .= "MAIL_MAILER=log\nMAIL_HOST=\nMAIL_PORT=587\nMAIL_USERNAME=\nMAIL_PASSWORD=\nMAIL_ENCRYPTION=tls\n";
    $env .= "MAIL_FROM_ADDRESS=noreply@narfcard.ng\nMAIL_FROM_NAME=\"NARFCARD Programme\"\n\n";
    $env .= "PAYSTACK_PUBLIC_KEY=pk_test_xxxxxxxx\nPAYSTACK_SECRET_KEY=sk_test_xxxxxxxx\nPAYSTACK_CALLBACK_URL=\n\n";
    $env .= "SESSION_LIFETIME=7200\nHTTPS_ONLY=false\n";
    return file_put_contents($base . '/.env', $env) !== false;
}

$checks = array(
    'PHP 8.0+' => $phpOk ? ('PHP ' . PHP_VERSION) : false,
    'PDO' => function_exists('extension_loaded') && extension_loaded('pdo'),
    'PDO MySQL' => function_exists('extension_loaded') && extension_loaded('pdo_mysql'),
    'mbstring' => function_exists('extension_loaded') && extension_loaded('mbstring'),
    'json' => function_exists('extension_loaded') && extension_loaded('json'),
    'storage writable' => is_dir($base . '/storage') && is_writable($base . '/storage'),
    'uploads writable' => is_dir($base . '/uploads') && is_writable($base . '/uploads'),
    'schema.sql' => is_file($base . '/database/schema.sql'),
    'seed.php' => is_file($base . '/database/seed.php'),
);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$installed && $phpOk) {
    $host = trim((string) (isset($_POST['db_host']) ? $_POST['db_host'] : 'localhost'));
    $port = trim((string) (isset($_POST['db_port']) ? $_POST['db_port'] : '3306'));
    $name = trim((string) (isset($_POST['db_name']) ? $_POST['db_name'] : ''));
    $user = trim((string) (isset($_POST['db_user']) ? $_POST['db_user'] : ''));
    $pass = (string) (isset($_POST['db_pass']) ? $_POST['db_pass'] : '');
    $adminFirst = trim((string) (isset($_POST['admin_first']) ? $_POST['admin_first'] : ''));
    $adminLast = trim((string) (isset($_POST['admin_last']) ? $_POST['admin_last'] : ''));
    $adminEmail = strtolower(trim((string) (isset($_POST['admin_email']) ? $_POST['admin_email'] : '')));
    $adminPassword = (string) (isset($_POST['admin_password']) ? $_POST['admin_password'] : '');
    $adminConfirm = (string) (isset($_POST['admin_password_confirm']) ? $_POST['admin_password_confirm'] : '');

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ((isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : '') === '443');
    $scheme = $https ? 'https' : 'http';
    $dir = rtrim(str_replace('\\', '/', dirname(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '')), '/.');
    if ($dir === '/' || $dir === '\\' || $dir === '.') {
        $dir = '';
    }
    $url = $scheme . '://' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost') . $dir;

    if ($name === '' || $user === '') {
        $errors[] = 'Database name and username are required.';
    }
    if (!extension_loaded('pdo_mysql')) {
        $errors[] = 'The pdo_mysql PHP extension is not enabled.';
    }
    if ($adminFirst === '' || $adminLast === '') {
        $errors[] = 'Super Administrator first name and last name are required.';
    }
    if ($adminEmail === '' || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid Super Administrator email address. This is the login you will use after install.';
    }
    if (strlen($adminPassword) < 10) {
        $errors[] = 'Super Administrator password must be at least 10 characters.';
    }
    if (!preg_match('/[A-Za-z]/', $adminPassword) || !preg_match('/[0-9]/', $adminPassword)) {
        $errors[] = 'Super Administrator password must contain at least one letter and one number.';
    }
    if ($adminPassword !== $adminConfirm) {
        $errors[] = 'Super Administrator password confirmation does not match.';
    }

    if (!$errors) {
        try {
            $dsn = 'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $name . ';charset=utf8mb4';
            $pdo = new PDO($dsn, $user, $pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            $ok[] = 'Connected to MySQL.';
        } catch (Exception $e) {
            $errors[] = 'Could not connect to MySQL. Create an empty database in cPanel first, then paste those credentials. Host message: ' . $e->getMessage();
        }
    }

    if (!$errors && isset($pdo)) {
        if (!install_write_env($base, array(
            'url' => $url,
            'key' => bin2hex(random_bytes(16)),
            'host' => $host,
            'port' => $port,
            'name' => $name,
            'user' => $user,
            'pass' => $pass,
        ))) {
            $errors[] = 'Could not write .env. Make the site folder writable, then try again.';
        } else {
            $ok[] = 'Wrote site configuration.';
        }
    }

    if (!$errors && isset($pdo)) {
        $sql = file_get_contents($base . '/database/schema.sql');
        if ($sql === false) {
            $errors[] = 'Could not read database/schema.sql.';
        } else {
            try {
                $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
                $parts = explode(';', $sql);
                foreach ($parts as $stmt) {
                    $stmt = trim($stmt);
                    if ($stmt === '' || strpos($stmt, '--') === 0) {
                        continue;
                    }
                    $pdo->exec($stmt);
                }
                $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
                $ok[] = 'Created all application tables.';
            } catch (Exception $e) {
                $errors[] = 'Schema import failed: ' . $e->getMessage();
            }
        }
    }

    if (!$errors) {
        putenv('DB_HOST=' . $host);
        putenv('DB_PORT=' . $port);
        putenv('DB_NAME=' . $name);
        putenv('DB_USER=' . $user);
        putenv('DB_PASS=' . $pass);
        putenv('NARFCARD_ADMIN_EMAIL=' . $adminEmail);
        putenv('NARFCARD_ADMIN_PASSWORD=' . $adminPassword);
        putenv('NARFCARD_ADMIN_FIRST=' . $adminFirst);
        putenv('NARFCARD_ADMIN_LAST=' . $adminLast);
        $_ENV['DB_HOST'] = $host;
        $_ENV['DB_PORT'] = $port;
        $_ENV['DB_NAME'] = $name;
        $_ENV['DB_USER'] = $user;
        $_ENV['DB_PASS'] = $pass;
        $_ENV['NARFCARD_ADMIN_EMAIL'] = $adminEmail;
        $_ENV['NARFCARD_ADMIN_PASSWORD'] = $adminPassword;
        $_ENV['NARFCARD_ADMIN_FIRST'] = $adminFirst;
        $_ENV['NARFCARD_ADMIN_LAST'] = $adminLast;
        try {
            require $base . '/includes/bootstrap.php';
            require $base . '/database/seed.php';
            $createdAdminEmail = $adminEmail;
            $ok[] = 'Loaded Nigerian states, LGAs, banks, occupations, collection centres and programme settings.';
            $ok[] = 'Created Super Administrator account for ' . $adminEmail . '.';
        } catch (Exception $e) {
            $errors[] = 'First-data setup failed: ' . $e->getMessage();
        }
    }

    if (!$errors) {
        if (!is_dir($base . '/storage')) {
            @mkdir($base . '/storage', 0755, true);
        }
        file_put_contents($lockFile, date('c'));
        $installed = true;
        $ok[] = 'Installation complete. Sign in as Super Administrator to finish programme setup. Applicants never use this installer.';
    }
}

$postedHost = isset($_POST['db_host']) ? $_POST['db_host'] : 'localhost';
$postedPort = isset($_POST['db_port']) ? $_POST['db_port'] : '3306';
$postedName = isset($_POST['db_name']) ? $_POST['db_name'] : '';
$postedUser = isset($_POST['db_user']) ? $_POST['db_user'] : '';
$postedFirst = isset($_POST['admin_first']) ? $_POST['admin_first'] : '';
$postedLast = isset($_POST['admin_last']) ? $_POST['admin_last'] : '';
$postedEmail = isset($_POST['admin_email']) ? $_POST['admin_email'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NARFCARD installer</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f6f7f4; color: #1c2430; margin: 0; }
        .bar { background: #073b22; color: #fff; padding: 14px 20px; border-bottom: 3px solid #c9a227; }
        .wrap { max-width: 680px; margin: 32px auto; background: #fff; padding: 28px; border: 1px solid #dfe6dc; border-radius: 12px; }
        h2 { font-size: 16px; margin: 22px 0 8px; color: #073b22; }
        label { display: block; font-weight: 700; margin: 12px 0 4px; }
        input { width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #c5d0c4; border-radius: 6px; }
        button { background: #0b5c35; color: #fff; border: 0; padding: 12px 18px; border-radius: 6px; font-weight: 700; margin-top: 16px; cursor: pointer; }
        .err { background: #fdecea; color: #8a1f11; padding: 10px 12px; border-radius: 8px; margin: 8px 0; }
        .ok { background: #e7f6ec; color: #0b5c35; padding: 10px 12px; border-radius: 8px; margin: 8px 0; }
        table { width: 100%; border-collapse: collapse; margin: 12px 0 20px; font-size: 14px; }
        td { padding: 6px 0; border-bottom: 1px solid #eef2ee; }
        .pass { color: #0b5c35; }
        .fail { color: #a33; }
        .note { font-size: 13px; color: #5b6775; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 16px; }
        @media (max-width: 640px) { .grid { grid-template-columns: 1fr; } }
        a { color: #0b5c35; }
    </style>
</head>
<body>
<div class="bar">NARFCARD operator installer</div>
<div class="wrap">
    <p class="note">This page is for the programme operator only. It writes configuration, creates MySQL tables, loads first data, and creates the Super Administrator. Applicants do not install anything. They create an account, apply for a grant, and wait for review.</p>
    <table>
        <?php foreach ($checks as $label => $result): ?>
            <tr>
                <td><?php echo install_h($label); ?></td>
                <td class="<?php echo $result ? 'pass' : 'fail'; ?>"><?php echo $result ? 'OK' . (is_string($result) ? ' — ' . install_h($result) : '') : 'Missing'; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php foreach ($errors as $err): ?><div class="err"><?php echo install_h($err); ?></div><?php endforeach; ?>
    <?php foreach ($ok as $msg): ?><div class="ok"><?php echo install_h($msg); ?></div><?php endforeach; ?>

    <?php if ($installed): ?>
        <h2>Setup is complete</h2>
        <p>Sign in with the Super Administrator account you created<?php echo $createdAdminEmail !== '' ? ' (<strong>' . install_h($createdAdminEmail) . '</strong>)' : ''; ?>.</p>
        <p>From the admin portal you can finish programme setup: contact details, SMTP, Paystack keys, fees, funding and collection centres.</p>
        <p>Applicants use the public website to register and apply. They never use this installer.</p>
        <p><a href="admin/login.php">Open Super Administrator login</a> &nbsp;|&nbsp; <a href="index.php">Open public website</a></p>
        <p class="note">Delete <code>install.php</code> from the server after you confirm you can sign in.</p>
    <?php elseif (!$phpOk): ?>
        <div class="err">This subdomain is running PHP <?php echo install_h(PHP_VERSION); ?>. NARFCARD needs PHP 8.0, 8.1 or 8.2.</div>
        <p>In cPanel open <strong>Select PHP Version</strong> (or MultiPHP Manager), choose this subdomain, switch to PHP 8.1, enable <code>pdo_mysql</code>, then reload this page.</p>
        <p><a href="health.php">Open server check</a></p>
    <?php else: ?>
        <form method="post" autocomplete="off">
            <h2>1. MySQL database</h2>
            <p class="note">Create an empty MySQL database in cPanel first, then paste its details here. The installer will create every table and the first reference data.</p>
            <label>MySQL host</label>
            <input name="db_host" value="<?php echo install_h($postedHost); ?>" required>
            <label>MySQL port</label>
            <input name="db_port" value="<?php echo install_h($postedPort); ?>" required>
            <label>Database name</label>
            <input name="db_name" value="<?php echo install_h($postedName); ?>" required>
            <label>Database username</label>
            <input name="db_user" value="<?php echo install_h($postedUser); ?>" required>
            <label>Database password</label>
            <input name="db_pass" type="password" value="">

            <h2>2. Super Administrator login</h2>
            <p class="note">This account is the only way into the admin portal after install. Applicant registration cannot create administrators.</p>
            <div class="grid">
                <div>
                    <label>First name</label>
                    <input name="admin_first" value="<?php echo install_h($postedFirst); ?>" required>
                </div>
                <div>
                    <label>Last name</label>
                    <input name="admin_last" value="<?php echo install_h($postedLast); ?>" required>
                </div>
            </div>
            <label>Email (admin login)</label>
            <input name="admin_email" type="email" value="<?php echo install_h($postedEmail); ?>" required>
            <label>Password (min 10 characters, letter and number)</label>
            <input name="admin_password" type="password" required minlength="10">
            <label>Confirm password</label>
            <input name="admin_password_confirm" type="password" required minlength="10">

            <button type="submit">Install NARFCARD</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
