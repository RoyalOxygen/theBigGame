<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';

$admin = require_admin(['grants.approve', 'funding.manage']);
$funding = db()->query('SELECT * FROM grant_funding ORDER BY id')->fetchAll();
$approvals = db()->query('SELECT g.*, u.email FROM grant_approvals g JOIN users u ON u.id=g.user_id ORDER BY g.id DESC LIMIT 100')->fetchAll();
view('admin/grants', ['title' => 'Grants', 'admin' => $admin, 'funding' => $funding, 'approvals' => $approvals]);
