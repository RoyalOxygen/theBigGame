<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';

use App\Services\Mailer;
use App\Services\Paystack;

$user = require_user();
$reference = (string) ($_GET['reference'] ?? $_GET['trxref'] ?? '');
if ($reference === '') {
    flash('error', 'Missing payment reference.');
    redirect('user/card.php');
}

$payStmt = db()->prepare('SELECT * FROM card_payments WHERE reference = ? AND user_id = ? LIMIT 1');
$payStmt->execute([$reference, $user['id']]);
$payment = $payStmt->fetch();
if (!$payment) {
    flash('error', 'Payment record not found.');
    redirect('user/card.php');
}
if ($payment['status'] === 'success') {
    flash('success', 'Payment already confirmed.');
    redirect('user/card.php');
}

$verify = Paystack::verify($reference);
$status = $verify['data']['status'] ?? '';
$ok = !empty($verify['status']) && $status === 'success';
if (!empty($verify['demo'])) {
    $ok = true;
}

if (!$ok) {
    db()->prepare("UPDATE card_payments SET status='failed', gateway_response=?, updated_at=NOW() WHERE id=?")
        ->execute([$verify['data']['gateway_response'] ?? 'failed', $payment['id']]);
    flash('error', 'Payment could not be verified.');
    redirect('user/card.php');
}

db()->beginTransaction();
try {
    db()->prepare("UPDATE card_payments SET status='success', channel=?, gateway_response=?, paid_at=NOW(), verified_at=NOW(), raw_payload=?, updated_at=NOW() WHERE id=? AND status='pending'")
        ->execute([
            $verify['data']['channel'] ?? 'paystack',
            $verify['data']['gateway_response'] ?? 'success',
            json_encode($verify),
            $payment['id'],
        ]);
    db()->prepare("UPDATE card_requests SET status='processing', updated_at=NOW() WHERE id=?")
        ->execute([$payment['card_request_id']]);
    db()->commit();
} catch (Throwable $e) {
    db()->rollBack();
    flash('error', 'Unable to record payment.');
    redirect('user/card.php');
}

$cr = db()->prepare('SELECT cr.*, s.name AS state_name, l.name AS lga_name, cc.name AS centre_name FROM card_requests cr LEFT JOIN states s ON s.id=cr.state_id LEFT JOIN local_governments l ON l.id=cr.lga_id LEFT JOIN collection_centres cc ON cc.id=cr.collection_centre_id WHERE cr.id=?');
$cr->execute([$payment['card_request_id']]);
$row = $cr->fetch() ?: [];
$html = Mailer::template('card_request', [
    'name' => full_name($user) ?: $user['email'],
    'email' => $user['email'],
    'reference' => $row['reference'] ?? '',
    'payment' => money_ngn($payment['amount']) . ' confirmed',
    'state' => $row['state_name'] ?? '',
    'lga' => $row['lga_name'] ?? '',
    'location' => $row['centre_name'] ?: ($row['delivery_address'] ?? ''),
]);
Mailer::queue($user['email'], Mailer::subject('card_request', [
    'name' => full_name($user) ?: $user['email'],
    'email' => $user['email'],
    'reference' => $row['reference'] ?? '',
], 'Your NARFCARD Request Has Been Confirmed'), $html, full_name($user));
$payHtml = Mailer::template('payment_confirmation', [
    'name' => full_name($user) ?: $user['email'],
    'email' => $user['email'],
    'amount' => money_ngn($payment['amount']),
    'reference' => $reference,
    'description' => 'Optional card processing and delivery charges',
]);
Mailer::queue($user['email'], Mailer::subject('payment_confirmation', [
    'name' => full_name($user) ?: $user['email'],
    'email' => $user['email'],
    'amount' => money_ngn($payment['amount']),
    'reference' => $reference,
], 'Payment confirmation'), $payHtml, full_name($user));
notify_user((int) $user['id'], 'Card request confirmed', 'Your card request has been received and is being processed.', 'success', url('user/card.php'));
flash('success', 'Payment verified. Your card request is now being processed.');
redirect('user/card.php');
