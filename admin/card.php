<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';

$admin = require_admin(['cards.manage']);
$id = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT cr.*, s.name AS state_name, l.name AS lga_name, cp.status AS pay_status FROM card_requests cr LEFT JOIN states s ON s.id=cr.state_id LEFT JOIN local_governments l ON l.id=cr.lga_id LEFT JOIN card_payments cp ON cp.card_request_id=cr.id WHERE cr.id=?');
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) {
    flash('error', 'Card request not found.');
    redirect('admin/cards.php');
}
$centres = db()->query('SELECT id, name FROM collection_centres WHERE is_active=1 ORDER BY name')->fetchAll();
view('admin/card', ['title' => 'Card request', 'admin' => $admin, 'row' => $row, 'centres' => $centres]);
