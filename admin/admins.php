<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';

$admin = require_admin(['*']);
if (is_post()) {
    if (!verify_csrf()) {
        flash('error', 'Invalid token.');
        redirect('admin/admins.php');
    }
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $first = trim((string) ($_POST['first_name'] ?? ''));
    $last = trim((string) ($_POST['last_name'] ?? ''));
    $roleId = (int) ($_POST['role_id'] ?? 0);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8 || $first === '' || $last === '' || $roleId < 1) {
        flash('error', 'Complete all administrator fields with a valid email and password.');
    } else {
        db()->prepare('INSERT INTO administrators (role_id, first_name, last_name, email, password_hash, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 1, NOW(), NOW())')
            ->execute([$roleId, $first, $last, $email, password_hash($password, PASSWORD_DEFAULT)]);
        audit_admin('admin.create', 'administrator', (int) db()->lastInsertId(), ['email' => $email]);
        flash('success', 'Administrator created.');
    }
    redirect('admin/admins.php');
}
$roles = db()->query('SELECT * FROM admin_roles ORDER BY id')->fetchAll();
$admins = db()->query('SELECT a.*, r.name AS role_name FROM administrators a JOIN admin_roles r ON r.id=a.role_id ORDER BY a.id')->fetchAll();
view('admin/admins', ['title' => 'Administrators', 'admin' => $admin, 'roles' => $roles, 'admins' => $admins]);
