<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';

use App\Services\WalletService;

$user = require_user();
$wallet = WalletService::ensure((int) $user['id']);
$tx = db()->prepare('SELECT * FROM wallet_transactions WHERE user_id = ? ORDER BY created_at DESC');
$tx->execute([$user['id']]);
$transactions = $tx->fetchAll();
$credited = 0.0;
$debited = 0.0;
foreach ($transactions as $row) {
    if ($row['direction'] === 'credit') {
        $credited += (float) $row['amount'];
    } else {
        $debited += (float) $row['amount'];
    }
}
$g = db()->prepare('SELECT COALESCE(SUM(amount),0) FROM grant_approvals WHERE user_id = ?');
$g->execute([$user['id']]);

view('user/wallet', [
    'title' => 'Wallet',
    'user' => $user,
    'wallet' => $wallet,
    'transactions' => $transactions,
    'credited' => $credited,
    'debited' => $debited,
    'approved' => (float) $g->fetchColumn(),
]);
