<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';

$admin = require_admin(['cards.manage']);
$rows = db()->query('SELECT cr.*, s.name AS state_name FROM card_requests cr LEFT JOIN states s ON s.id=cr.state_id ORDER BY cr.id DESC LIMIT 200')->fetchAll();
view('admin/cards', ['title' => 'Card requests', 'admin' => $admin, 'rows' => $rows]);
