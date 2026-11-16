<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';

$admin = require_admin(['applicants.view']);
$id = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT a.*, u.email, p.first_name, p.middle_name, p.last_name, p.date_of_birth, p.gender, p.phone, p.address, p.id_type, p.id_number, p.photo_path, s.name AS state_name, l.name AS lga_name, b.name AS bank_name
    FROM applications a
    JOIN users u ON u.id = a.user_id
    LEFT JOIN user_profiles p ON p.user_id = u.id
    LEFT JOIN states s ON s.id = p.state_id
    LEFT JOIN local_governments l ON l.id = p.lga_id
    LEFT JOIN banks b ON b.id = a.bank_id
    WHERE a.id = ?');
$stmt->execute([$id]);
$app = $stmt->fetch();
if (!$app) {
    flash('error', 'Application not found.');
    redirect('admin/applicants.php');
}
if ($app['status'] === 'submitted') {
    db()->prepare("UPDATE applications SET status='under_review', updated_at=NOW() WHERE id=? AND status='submitted'")->execute([$id]);
    $app['status'] = 'under_review';
}
$notes = db()->prepare('SELECT n.*, ad.first_name, ad.last_name FROM application_notes n JOIN administrators ad ON ad.id=n.admin_id WHERE n.application_id=? ORDER BY n.id DESC');
$notes->execute([$id]);
$funding = db()->query('SELECT * FROM grant_funding ORDER BY id')->fetchAll();

view('admin/application', [
    'title' => 'Application ' . $app['reference'],
    'admin' => $admin,
    'app' => $app,
    'notes' => $notes->fetchAll(),
    'funding' => $funding,
]);
