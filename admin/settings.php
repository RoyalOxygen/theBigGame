<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';

$admin = require_admin(['*']);
ensure_settings_rows();
$catalog = settings_catalog();
$section = (string) ($_GET['section'] ?? $_POST['section'] ?? 'display');
if (!isset($catalog[$section])) {
    $section = 'display';
}

if (is_post()) {
    if (!verify_csrf()) {
        flash('error', 'Invalid token.');
        redirect('admin/settings.php?section=' . urlencode($section));
    }
    if ((string) ($_POST['action'] ?? '') === 'test_mail') {
        $to = strtolower(trim((string) ($_POST['test_to'] ?? '')));
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $adminUser = auth_admin();
            $to = (string) ($adminUser['email'] ?? '');
        }
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Enter a valid address to send the test email.');
            redirect('admin/settings.php?section=email');
        }
        $status = \App\Services\Mailer::status();
        if (!$status['password_set']) {
            flash('error', 'Save the SMTP password first, then send a test. Username must be nerf@oxygendata.com.ng and port 465 with SSL.');
            redirect('admin/settings.php?section=email');
        }
        $html = \App\Services\Mailer::template('verify_email', [
            'name' => $to,
            'email' => $to,
            'verify_url' => url('verify-email.php?pending=1'),
        ]);
        $ok = \App\Services\Mailer::send($to, 'NARFCARD SMTP test', $html, $to);
        if ($ok) {
            flash('success', 'Test email accepted by ' . $status['host'] . ':' . $status['port'] . '. Check inbox and spam for ' . $to . '.');
        } else {
            flash('error', 'SMTP rejected the test. Open storage/logs/mail.log on the server, or confirm the mailbox password, port 465 and SSL.');
        }
        redirect('admin/settings.php?section=email');
    }
    $postedSection = (string) ($_POST['section'] ?? $section);
    if (!isset($catalog[$postedSection])) {
        flash('error', 'Unknown settings section.');
        redirect('admin/settings.php');
    }
    $group = $catalog[$postedSection];
    $values = $_POST['settings'] ?? [];
    if (!is_array($values)) {
        $values = [];
    }
    foreach ($group['fields'] as $key => $field) {
        $type = $field['type'] ?? 'text';
        $secret = !empty($field['secret']);
        if ($type === 'file') {
            $uploaded = store_brand_upload($_FILES['file_' . $key] ?? []);
            if ($uploaded) {
                save_setting($key, $uploaded, false);
            }
            continue;
        }
        if ($type === 'checkbox') {
            save_setting($key, !empty($values[$key]) ? '1' : '0', $secret);
            continue;
        }
        if ($type === 'password' && $secret) {
            $incoming = (string) ($values[$key] ?? '');
            if (trim($incoming) === '') {
                continue;
            }
            save_setting($key, $incoming, true);
            continue;
        }
        $value = (string) ($values[$key] ?? '');
        if (in_array($type, ['html', 'textarea'], true)) {
            $value = $type === 'html' ? sanitize_email_html($value) : $value;
        } elseif ($type === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Enter a valid email for ' . ($field['label'] ?? $key) . '.');
            redirect('admin/settings.php?section=' . urlencode($postedSection));
        } elseif ($type === 'color' && $value !== '' && !preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) {
            flash('error', 'Use a six-digit hex colour for ' . ($field['label'] ?? $key) . '.');
            redirect('admin/settings.php?section=' . urlencode($postedSection));
        } elseif ($type === 'number' && $value !== '' && !is_numeric($value)) {
            flash('error', 'Enter a number for ' . ($field['label'] ?? $key) . '.');
            redirect('admin/settings.php?section=' . urlencode($postedSection));
        }
        save_setting($key, $value, $secret);
    }
    audit_admin('settings.update', 'system_settings', null, ['section' => $postedSection]);
    flash('success', $group['label'] . ' saved. Changes appear on the public site immediately.');
    redirect('admin/settings.php?section=' . urlencode($postedSection));
}

view('admin/settings', [
    'title' => 'Settings',
    'admin' => $admin,
    'catalog' => $catalog,
    'section' => $section,
    'values' => settings_all(),
    'mailStatus' => \App\Services\Mailer::status(),
]);
