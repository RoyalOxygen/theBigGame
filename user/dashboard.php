<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';

$user = require_user();
$appStmt = db()->prepare('SELECT * FROM applications WHERE user_id = ?');
$appStmt->execute([$user['id']]);
$app = $appStmt->fetch() ?: ['status' => 'draft', 'reference' => null];

$walletStmt = db()->prepare('SELECT * FROM wallets WHERE user_id = ?');
$walletStmt->execute([$user['id']]);
$wallet = $walletStmt->fetch() ?: ['available_balance' => 0];

$tx = db()->prepare('SELECT * FROM wallet_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 5');
$tx->execute([$user['id']]);

$grant = db()->prepare('SELECT * FROM grant_approvals WHERE user_id = ? ORDER BY id DESC LIMIT 1');
$grant->execute([$user['id']]);

$card = db()->prepare('SELECT * FROM card_requests WHERE user_id = ? ORDER BY id DESC LIMIT 1');
$card->execute([$user['id']]);

$notes = db()->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5');
$notes->execute([$user['id']]);

view('user/dashboard', [
    'title' => 'Dashboard',
    'user' => $user,
    'app' => $app,
    'wallet' => $wallet,
    'transactions' => $tx->fetchAll(),
    'grant' => $grant->fetch() ?: null,
    'cardRequest' => $card->fetch() ?: null,
    'notifications' => $notes->fetchAll(),
]);
