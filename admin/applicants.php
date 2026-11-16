<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';

$admin = require_admin(['applicants.view']);
$q = trim((string) ($_GET['q'] ?? ''));
$status = (string) ($_GET['status'] ?? '');
$verified = (string) ($_GET['verified'] ?? '');
$stateId = (int) ($_GET['state_id'] ?? 0);
$lgaId = (int) ($_GET['lga_id'] ?? 0);

$sql = 'SELECT u.id AS user_id, u.email, u.email_verified_at, u.created_at,
               a.id AS application_id, a.reference, a.status, a.updated_at AS application_updated_at,
               p.first_name, p.last_name, s.name AS state_name
        FROM users u
        LEFT JOIN applications a ON a.user_id = u.id
        LEFT JOIN user_profiles p ON p.user_id = u.id
        LEFT JOIN states s ON s.id = p.state_id
        WHERE 1=1';
$params = [];
if ($q !== '') {
    $sql .= ' AND (a.reference LIKE ? OR u.email LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like);
}
if ($status === 'not_started') {
    $sql .= ' AND a.id IS NULL';
} elseif ($status !== '') {
    $sql .= ' AND a.status = ?';
    $params[] = $status;
}
if ($verified === 'yes') {
    $sql .= ' AND u.email_verified_at IS NOT NULL';
} elseif ($verified === 'no') {
    $sql .= ' AND u.email_verified_at IS NULL';
}
if ($stateId) {
    $sql .= ' AND p.state_id = ?';
    $params[] = $stateId;
}
if ($lgaId) {
    $sql .= ' AND p.lga_id = ?';
    $params[] = $lgaId;
}
$sql .= ' ORDER BY u.created_at DESC LIMIT 500';
$stmt = db()->prepare($sql);
$stmt->execute($params);

$states = db()->query('SELECT id, name FROM states ORDER BY name')->fetchAll();
$lgas = [];
if ($stateId) {
    $ls = db()->prepare('SELECT id, name FROM local_governments WHERE state_id = ? ORDER BY name');
    $ls->execute([$stateId]);
    $lgas = $ls->fetchAll();
}

$totalUsers = (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
$unverifiedUsers = (int) db()->query('SELECT COUNT(*) FROM users WHERE email_verified_at IS NULL')->fetchColumn();

view('admin/applicants', [
    'title' => 'Applicants',
    'admin' => $admin,
    'rows' => $stmt->fetchAll(),
    'q' => $q,
    'status' => $status,
    'verified' => $verified,
    'stateId' => $stateId,
    'lgaId' => $lgaId,
    'states' => $states,
    'lgas' => $lgas,
    'totalUsers' => $totalUsers,
    'unverifiedUsers' => $unverifiedUsers,
]);
