<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';

$user = require_user();
$ps = db()->prepare('SELECT * FROM user_profiles WHERE user_id = ?');
$ps->execute([$user['id']]);
$profile = $ps->fetch() ?: [];
$as = db()->prepare('SELECT * FROM applications WHERE user_id = ?');
$as->execute([$user['id']]);
$app = $as->fetch() ?: [];

if (is_post()) {
    if (!verify_csrf()) {
        flash('error', 'Invalid security token.');
        redirect('user/profile.php');
    }
    $action = (string) ($_POST['action'] ?? 'profile');
    if ($action === 'password') {
        $current = (string) ($_POST['current_password'] ?? '');
        $new = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');
        $full = db()->prepare('SELECT password_hash FROM users WHERE id = ?');
        $full->execute([$user['id']]);
        $hash = (string) $full->fetchColumn();
        if (!password_verify($current, $hash)) {
            flash('error', 'Current password is incorrect.');
        } elseif (strlen($new) < 8 || $new !== $confirm) {
            flash('error', 'New password must be at least 8 characters and match confirmation.');
        } else {
            db()->prepare('UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?')
                ->execute([password_hash($new, PASSWORD_DEFAULT), $user['id']]);
            session_regenerate_id(true);
            flash('success', 'Password updated.');
        }
        redirect('user/profile.php');
    }

    $phone = trim((string) ($_POST['phone'] ?? ''));
    $alt = trim((string) ($_POST['alt_phone'] ?? ''));
    $address = trim((string) ($_POST['address'] ?? ''));
    $photo = $profile['photo_path'] ?? null;
    if (!empty($_FILES['photo']['tmp_name'])) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($_FILES['photo']['tmp_name']);
        if (in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true) && $_FILES['photo']['size'] <= 2 * 1024 * 1024) {
            $ext = $mime === 'image/png' ? 'png' : ($mime === 'image/webp' ? 'webp' : 'jpg');
            $name = bin2hex(random_bytes(12)) . '.' . $ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], BASE_PATH . '/uploads/photos/' . $name);
            $photo = 'photos/' . $name;
        }
    }
    db()->prepare('UPDATE user_profiles SET phone=?, alt_phone=?, address=?, photo_path=?, updated_at=NOW() WHERE user_id=?')
        ->execute([$phone ?: null, $alt ?: null, $address ?: null, $photo, $user['id']]);
    flash('success', 'Profile updated.');
    redirect('user/profile.php');
}

view('user/profile', [
    'title' => 'Profile',
    'user' => $user,
    'profile' => $profile,
    'app' => $app,
]);
