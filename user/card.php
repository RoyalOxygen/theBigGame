<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';

use App\Services\Paystack;

$user = require_user();
$appStmt = db()->prepare('SELECT * FROM applications WHERE user_id = ?');
$appStmt->execute([$user['id']]);
$app = $appStmt->fetch();
$approved = $app && $app['status'] === 'approved';

$req = db()->prepare('SELECT * FROM card_requests WHERE user_id = ? ORDER BY id DESC LIMIT 1');
$req->execute([$user['id']]);
$request = $req->fetch() ?: null;

$card = null;
if ($request) {
    $cs = db()->prepare('SELECT * FROM cards WHERE card_request_id = ?');
    $cs->execute([$request['id']]);
    $card = $cs->fetch() ?: null;
}

$processingFee = (float) setting('card_processing_fee', 2500);
$deliveryFee = (float) setting('card_delivery_fee', 1500);

if (is_post() && $approved) {
    if (!setting_bool('payments_enabled', true)) {
        flash('error', 'Card-fee payments are temporarily unavailable. You can still receive an approved grant by bank transfer.');
        redirect('user/card.php');
    }
    if (!verify_csrf()) {
        flash('error', 'Invalid security token.');
        redirect('user/card.php');
    }
    if (empty($_POST['confirm_fees'])) {
        flash('error', 'Please confirm the disclosed charges.');
        redirect('user/card.php');
    }
    if ($request && !in_array($request['status'], ['cancelled', 'awaiting_payment'], true) && $request['status'] !== 'not_requested') {
        flash('info', 'You already have an active card request.');
        redirect('user/card.php');
    }
    $fullName = trim((string) ($_POST['full_name'] ?? full_name($user)));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $stateId = (int) ($_POST['state_id'] ?? 0);
    $lgaId = (int) ($_POST['lga_id'] ?? 0);
    $address = trim((string) ($_POST['delivery_address'] ?? ''));
    $centre = (int) ($_POST['collection_centre_id'] ?? 0);
    if ($fullName === '' || $phone === '' || $stateId < 1 || $lgaId < 1 || $address === '') {
        flash('error', 'Complete all required delivery fields.');
        redirect('user/card.php');
    }
    $total = $processingFee + $deliveryFee;
    $reference = generate_reference('CRD');
    db()->prepare('INSERT INTO card_requests (user_id, reference, full_name, phone, state_id, lga_id, delivery_address, collection_centre_id, processing_fee, delivery_fee, total_fee, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())')
        ->execute([$user['id'], $reference, $fullName, $phone, $stateId, $lgaId, $address, $centre ?: null, $processingFee, $deliveryFee, $total, 'awaiting_payment']);
    $requestId = (int) db()->lastInsertId();
    $payRef = generate_reference('PAY');
    db()->prepare('INSERT INTO card_payments (card_request_id, user_id, reference, amount, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())')
        ->execute([$requestId, $user['id'], $payRef, $total, 'pending']);
    $callback = trim((string) setting('paystack_callback_url', ''));
    if ($callback === '') {
        $callback = url('user/card-callback.php');
    }
    $init = Paystack::initialize($user['email'], (int) round($total * 100), $payRef, $callback, ['card_request_id' => $requestId]);
    if (!empty($init['data']['authorization_url'])) {
        redirect($init['data']['authorization_url']);
    }
    flash('error', $init['message'] ?? 'Unable to start payment.');
    redirect('user/card.php');
}

$states = db()->query('SELECT id, name FROM states ORDER BY name')->fetchAll();
$lgas = [];
if (!empty($user['state_id'])) {
    $ls = db()->prepare('SELECT id, name FROM local_governments WHERE state_id = ? ORDER BY name');
    $ls->execute([$user['state_id']]);
    $lgas = $ls->fetchAll();
}
$centres = db()->query('SELECT * FROM collection_centres WHERE is_active = 1 ORDER BY name')->fetchAll();

view('user/card', [
    'title' => 'Card',
    'user' => $user,
    'approved' => $approved,
    'request' => $request,
    'issuedCard' => $card,
    'processingFee' => $processingFee,
    'deliveryFee' => $deliveryFee,
    'states' => $states,
    'lgas' => $lgas,
    'centres' => $centres,
]);
