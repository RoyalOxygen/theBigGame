<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';

$user = require_user();
$reference = (string) ($_GET['reference'] ?? '');
if ($reference === '') {
    flash('error', 'Missing payment reference.');
    redirect('user/card.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Development payment</title>
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="auth-shell">
<div class="card-soft auth-card">
    <h1 class="h5">Development payment checkout</h1>
    <p>Paystack keys are not configured. This page simulates a successful checkout for reference <strong><?= e($reference) ?></strong>.</p>
    <p class="small">Never treat simulated payments as genuine in production.</p>
    <a class="btn btn-official" href="<?= url('user/card-callback.php?reference=' . urlencode($reference) . '&demo=1') ?>">Simulate successful payment</a>
    <a class="btn btn-outline-secondary mt-2" href="<?= url('user/card.php') ?>">Cancel</a>
</div>
</body>
</html>
