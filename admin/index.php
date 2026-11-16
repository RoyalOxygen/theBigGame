<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';

$admin = require_admin();
$pdo = db();
$stats = [
    'users' => (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'verified' => (int) $pdo->query('SELECT COUNT(*) FROM users WHERE email_verified_at IS NOT NULL')->fetchColumn(),
    'applications' => (int) $pdo->query('SELECT COUNT(*) FROM applications')->fetchColumn(),
    'pending' => (int) $pdo->query("SELECT COUNT(*) FROM applications WHERE status='submitted'")->fetchColumn(),
    'under_review' => (int) $pdo->query("SELECT COUNT(*) FROM applications WHERE status='under_review'")->fetchColumn(),
    'approved' => (int) $pdo->query("SELECT COUNT(*) FROM applications WHERE status='approved'")->fetchColumn(),
    'rejected' => (int) $pdo->query("SELECT COUNT(*) FROM applications WHERE status='rejected'")->fetchColumn(),
    'grant_value' => (float) $pdo->query('SELECT COALESCE(SUM(amount),0) FROM grant_approvals')->fetchColumn(),
    'cards' => (int) $pdo->query('SELECT COUNT(*) FROM card_requests')->fetchColumn(),
    'card_pay' => (int) $pdo->query("SELECT COUNT(*) FROM card_requests WHERE status='awaiting_payment'")->fetchColumn(),
    'card_proc' => (int) $pdo->query("SELECT COUNT(*) FROM card_requests WHERE status IN ('processing','produced','dispatched')")->fetchColumn(),
    'card_ready' => (int) $pdo->query("SELECT COUNT(*) FROM card_requests WHERE status='ready_for_collection'")->fetchColumn(),
];
$activity = $pdo->query('SELECT l.*, a.first_name, a.last_name FROM admin_activity_logs l JOIN administrators a ON a.id=l.admin_id ORDER BY l.id DESC LIMIT 12')->fetchAll();

view('admin/dashboard', [
    'title' => 'Dashboard',
    'admin' => $admin,
    'stats' => $stats,
    'activity' => $activity,
]);
