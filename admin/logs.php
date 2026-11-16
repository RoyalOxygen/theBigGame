<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';

$admin = require_admin(['*']);
$rows = db()->query('SELECT l.*, a.first_name, a.last_name FROM admin_activity_logs l JOIN administrators a ON a.id=l.admin_id ORDER BY l.id DESC LIMIT 200')->fetchAll();
view('admin/logs', ['title' => 'Audit log', 'admin' => $admin, 'rows' => $rows]);
