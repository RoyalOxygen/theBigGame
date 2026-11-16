<?php

declare(strict_types=1);

use App\Services\Database;

function config(?string $key = null, mixed $default = null): mixed
{
    static $config;
    if ($config === null) {
        $config = require BASE_PATH . '/app/config/app.php';
    }
    if ($key === null) {
        return $config;
    }
    $segments = explode('.', $key);
    $value = $config;
    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }
    return $value;
}

function db(): PDO
{
    return Database::connection();
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    $base = defined('BASE_URL') ? BASE_URL : '';
    $path = ltrim($path, '/');
    return $path === '' ? $base . '/' : $base . '/' . $path;
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function upload_url(string $path): string
{
    return url('file.php?f=' . rawurlencode(ltrim($path, '/')));
}

function redirect(string $path, int $code = 302)
{
    if (!str_starts_with($path, 'http')) {
        $path = url($path);
    }
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    if (class_exists(\App\Services\Mailer::class)) {
        \App\Services\Mailer::flushQueue();
    }
    header('Location: ' . $path, true, $code);
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(?string $token = null): bool
{
    $token = $token ?? ($_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
    return is_string($token) && isset($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $token);
}

function old(string $key, mixed $default = ''): mixed
{
    $flash = $_SESSION['_old'] ?? [];
    return $flash[$key] ?? $default;
}

function flash(string $key, mixed $value = null): mixed
{
    if ($value === null) {
        $val = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $val;
    }
    $_SESSION['_flash'][$key] = $value;
    return $value;
}

function flash_errors(array $errors): void
{
    $_SESSION['_flash']['errors'] = $errors;
}

function errors(): array
{
    $errors = $_SESSION['_flash']['errors'] ?? [];
    unset($_SESSION['_flash']['errors']);
    return is_array($errors) ? $errors : [];
}

function auth_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    static $user;
    if ($user === null) {
        $stmt = db()->prepare('SELECT u.*, p.first_name, p.middle_name, p.last_name, p.phone, p.photo_path, p.state_id, p.lga_id, p.address FROM users u LEFT JOIN user_profiles p ON p.user_id = u.id WHERE u.id = ? LIMIT 1');
        $stmt->execute([(int) $_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;
    }
    return $user ?: null;
}

function auth_admin(): ?array
{
    if (empty($_SESSION['admin_id'])) {
        return null;
    }
    static $admin;
    if ($admin === null) {
        $stmt = db()->prepare('SELECT a.*, r.slug AS role_slug, r.name AS role_name, r.permissions FROM administrators a JOIN admin_roles r ON r.id = a.role_id WHERE a.id = ? AND a.is_active = 1 LIMIT 1');
        $stmt->execute([(int) $_SESSION['admin_id']]);
        $admin = $stmt->fetch() ?: null;
        if ($admin && is_string($admin['permissions'] ?? null)) {
            $admin['permissions'] = json_decode($admin['permissions'], true) ?: [];
        }
    }
    return $admin ?: null;
}

function admin_can(string $permission): bool
{
    $admin = auth_admin();
    if (!$admin) {
        return false;
    }
    if (($admin['role_slug'] ?? '') === 'super-admin') {
        return true;
    }
    $perms = $admin['permissions'] ?? [];
    return in_array($permission, $perms, true) || in_array('*', $perms, true);
}

function require_user(): array
{
    $user = auth_user();
    if (!$user) {
        flash('intended', $_SERVER['REQUEST_URI'] ?? '/user/dashboard');
        redirect('login.php');
    }
    if (setting_bool('require_email_verification', true) && empty($user['email_verified_at'])) {
        redirect('verify-email.php?pending=1');
    }
    return $user;
}

function require_admin(array $permissions = []): array
{
    $admin = auth_admin();
    if (!$admin) {
        redirect('admin/login.php');
    }
    foreach ($permissions as $permission) {
        if (!admin_can($permission)) {
            http_response_code(403);
            echo 'Access denied.';
            exit;
        }
    }
    return $admin;
}

function email_templates(): array
{
    static $templates;
    if ($templates === null) {
        $templates = require BASE_PATH . '/app/config/email_templates.php';
    }
    return $templates;
}

function settings_catalog(): array
{
    static $catalog;
    if ($catalog === null) {
        $catalog = require BASE_PATH . '/app/config/settings.php';
    }
    return $catalog;
}

function setting_defaults(): array
{
    static $defaults;
    if ($defaults === null) {
        $defaults = [];
        foreach (settings_catalog() as $group) {
            foreach ($group['fields'] as $key => $field) {
                $defaults[$key] = (string) ($field['default'] ?? '');
            }
        }
    }
    return $defaults;
}

function settings_all(): array
{
    if (!isset($GLOBALS['_narf_settings_cache']) || !is_array($GLOBALS['_narf_settings_cache'])) {
        $all = setting_defaults();
        try {
            foreach (db()->query('SELECT setting_key, setting_value FROM system_settings') as $row) {
                $all[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Throwable $e) {
            // Database may not be ready during install.
        }
        $GLOBALS['_narf_settings_cache'] = $all;
    }
    return $GLOBALS['_narf_settings_cache'];
}

function setting(string $key, mixed $default = null): mixed
{
    $all = settings_all();
    if (array_key_exists($key, $all) && $all[$key] !== null && $all[$key] !== '') {
        return $all[$key];
    }
    if ($default !== null) {
        return $default;
    }
    return $all[$key] ?? null;
}

function setting_bool(string $key, bool $default = true): bool
{
    $value = setting($key, $default ? '1' : '0');
    return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
}

function save_setting(string $key, ?string $value, bool $secret = false): void
{
    $stmt = db()->prepare('INSERT INTO system_settings (setting_key, setting_value, is_secret, updated_at) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), is_secret = VALUES(is_secret), updated_at = NOW()');
    $stmt->execute([$key, $value, $secret ? 1 : 0]);
    unset($GLOBALS['_narf_settings_cache']);
}

function ensure_settings_rows(): void
{
    $stmt = db()->prepare('INSERT IGNORE INTO system_settings (setting_key, setting_value, is_secret, updated_at) VALUES (?, ?, ?, NOW())');
    foreach (settings_catalog() as $group) {
        foreach ($group['fields'] as $key => $field) {
            $stmt->execute([$key, (string) ($field['default'] ?? ''), !empty($field['secret']) ? 1 : 0]);
        }
    }
    $fill = db()->prepare('UPDATE system_settings SET setting_value = ? WHERE setting_key = ? AND (setting_value IS NULL OR setting_value = \'\')');
    foreach (email_templates() as $name => $tpl) {
        $fill->execute([(string) $tpl['subject'], 'email_subject_' . $name]);
        $fill->execute([(string) $tpl['body'], 'email_body_' . $name]);
    }
    foreach ([
        'mail_mailer' => 'smtp',
        'mail_host' => 'mail.oxygendata.com.ng',
        'mail_port' => '465',
        'mail_username' => 'nerf@oxygendata.com.ng',
        'mail_encryption' => 'ssl',
        'mail_from_address' => 'nerf@oxygendata.com.ng',
    ] as $key => $value) {
        $fill->execute([$value, $key]);
    }
    $migrate = db()->prepare('UPDATE system_settings SET setting_value = ? WHERE setting_key = ? AND setting_value = ?');
    $migrate->execute(['smtp', 'mail_mailer', 'log']);
    $migrate->execute(['465', 'mail_port', '587']);
    $migrate->execute(['ssl', 'mail_encryption', 'tls']);
    $migrate->execute(['nerf@oxygendata.com.ng', 'mail_from_address', 'noreply@narfcard.ng']);
    unset($GLOBALS['_narf_settings_cache']);
}

function brand_url(string $key, string $fallbackAsset): string
{
    $path = trim((string) setting($key, ''));
    $full = BASE_PATH . '/uploads/' . ltrim($path, '/');
    if ($path !== '' && str_starts_with($path, 'branding/') && is_file($full)) {
        return url('branding.php?f=' . rawurlencode($path));
    }
    return asset($fallbackAsset);
}

function site_name(): string
{
    return (string) setting('programme_name', config('name'));
}

function site_short(): string
{
    return (string) setting('programme_short_name', config('short_name'));
}

function theme_color(): string
{
    $color = (string) setting('theme_color', '#073b22');
    return preg_match('/^#[0-9A-Fa-f]{6}$/', $color) ? $color : '#073b22';
}

function accent_color(): string
{
    $color = (string) setting('accent_color', '#c9a227');
    return preg_match('/^#[0-9A-Fa-f]{6}$/', $color) ? $color : '#c9a227';
}

function theme_style_tag(): string
{
    $theme = theme_color();
    $accent = accent_color();
    return '<style>:root{--green-900:' . e($theme) . ';--green-800:' . e($theme) . ';--gold-500:' . e($accent) . ';}</style>';
}

function render_placeholders(string $text, array $data): string
{
    return (string) preg_replace_callback('/\{\{\s*([a-z0-9_]+)\s*\}\}/i', static function (array $m) use ($data): string {
        $key = $m[1];
        return isset($data[$key]) ? (string) $data[$key] : '';
    }, $text);
}

function sanitize_email_html(string $html): string
{
    return strip_tags($html, '<p><br><br/><strong><b><em><i><a><ul><ol><li><h2><h3><h4><span><div>');
}

function paystack_secret(): string
{
    $fromSettings = trim((string) setting('paystack_secret_key', ''));
    if ($fromSettings !== '') {
        return $fromSettings;
    }
    return (string) config('paystack.secret_key');
}

function paystack_public(): string
{
    $fromSettings = trim((string) setting('paystack_public_key', ''));
    if ($fromSettings !== '') {
        return $fromSettings;
    }
    return (string) config('paystack.public_key');
}

function store_brand_upload(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return null;
    }
    if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
        return null;
    }
    $tmp = (string) ($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_file($tmp)) {
        return null;
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string) $finfo->file($tmp);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/svg+xml' => 'svg',
        'image/x-icon' => 'ico',
        'image/vnd.microsoft.icon' => 'ico',
        'text/plain' => 'svg',
    ];
    $orig = strtolower((string) ($file['name'] ?? ''));
    $ext = $allowed[$mime] ?? null;
    if ($ext === 'svg' && $mime === 'text/plain' && !str_ends_with($orig, '.svg')) {
        return null;
    }
    if ($ext === null) {
        return null;
    }
    $dir = BASE_PATH . '/uploads/branding';
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        return null;
    }
    $name = 'brand_' . bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($tmp, $dir . '/' . $name)) {
        return null;
    }
    return 'branding/' . $name;
}

function money_ngn(float|int|string|null $amount): string
{
    return 'NGN ' . number_format((float) $amount, 2);
}

function full_name(?array $person): string
{
    if (!$person) {
        return '';
    }
    $parts = array_filter([
        $person['first_name'] ?? '',
        $person['middle_name'] ?? '',
        $person['last_name'] ?? '',
    ], static fn ($v) => $v !== '' && $v !== null);
    return trim(implode(' ', $parts));
}

function initials(?array $person): string
{
    $first = strtoupper(substr((string) ($person['first_name'] ?? $person['email'] ?? 'N'), 0, 1));
    $last = strtoupper(substr((string) ($person['last_name'] ?? ''), 0, 1));
    return $first . $last;
}

function status_badge(string $status): string
{
    $map = [
        'draft' => 'secondary',
        'submitted' => 'info',
        'under_review' => 'warning',
        'additional_info' => 'warning',
        'approved' => 'success',
        'rejected' => 'danger',
        'pending' => 'warning',
        'processing' => 'info',
        'produced' => 'primary',
        'dispatched' => 'primary',
        'ready_for_collection' => 'success',
        'delivered' => 'success',
        'cancelled' => 'danger',
        'not_requested' => 'secondary',
        'awaiting_payment' => 'warning',
        'payment_confirmed' => 'info',
        'success' => 'success',
        'failed' => 'danger',
        'abandoned' => 'secondary',
        'credited' => 'success',
        'debited' => 'danger',
        'verified' => 'success',
        'unverified' => 'warning',
        'not_started' => 'secondary',
    ];
    $class = $map[$status] ?? 'secondary';
    $label = ucwords(str_replace('_', ' ', $status));
    return '<span class="badge text-bg-' . $class . '">' . e($label) . '</span>';
}

function view(string $name, array $data = []): void
{
    extract($data, EXTR_SKIP);
    $viewFile = BASE_PATH . '/app/views/' . $name . '.php';
    if (!is_file($viewFile)) {
        throw new RuntimeException('View not found: ' . $name);
    }
    require $viewFile;
}

function layout_start(string $layout, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require BASE_PATH . '/app/views/layouts/' . $layout . '_start.php';
}

function layout_end(string $layout, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require BASE_PATH . '/app/views/layouts/' . $layout . '_end.php';
}

function json_response(array $payload, int $code = 200)
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

function method(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

function is_post(): bool
{
    return method() === 'POST';
}

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function generate_reference(string $prefix): string
{
    return $prefix . strtoupper(bin2hex(random_bytes(5))) . date('ymd');
}

function mask_account(?string $number): string
{
    $number = (string) $number;
    if (strlen($number) < 4) {
        return '****';
    }
    return str_repeat('*', max(0, strlen($number) - 4)) . substr($number, -4);
}

function nigeria_phone_valid(string $phone): bool
{
    $phone = preg_replace('/\s+/', '', $phone) ?? '';
    return (bool) preg_match('/^(\+234|0)[789][01]\d{8}$/', $phone);
}

function paginate(int $total, int $page, int $perPage = 20): array
{
    $pages = max(1, (int) ceil($total / $perPage));
    $page = max(1, min($page, $pages));
    return [
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
        'pages' => $pages,
        'offset' => ($page - 1) * $perPage,
    ];
}

function audit_admin(string $action, ?string $entity = null, ?int $entityId = null, array $meta = []): void
{
    $admin = auth_admin();
    if (!$admin) {
        return;
    }
    $stmt = db()->prepare('INSERT INTO admin_activity_logs (admin_id, action, entity_type, entity_id, metadata, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
    $stmt->execute([
        $admin['id'],
        $action,
        $entity,
        $entityId,
        json_encode($meta),
        client_ip(),
        substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
    ]);
}

function notify_user(int $userId, string $title, string $body, string $type = 'info', ?string $link = null): void
{
    $stmt = db()->prepare('INSERT INTO notifications (user_id, title, body, type, link, is_read, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())');
    $stmt->execute([$userId, $title, $body, $type, $link]);
}

function current_path(): string
{
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    return $uri;
}

function active_nav(string $needle): string
{
    return str_contains(current_path(), $needle) ? 'active' : '';
}
