<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';

$secret = paystack_secret();
$raw = (string) file_get_contents('php://input');
$sig = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';
if ($secret === '' || str_contains($secret, 'xxxxxxxx')) {
    http_response_code(200);
    echo 'ignored';
    exit;
}
if (!hash_equals(hash_hmac('sha512', $raw, $secret), (string) $sig)) {
    http_response_code(401);
    echo 'invalid signature';
    exit;
}
$event = json_decode($raw, true);
$reference = $event['data']['reference'] ?? '';
if ($reference !== '' && ($event['event'] ?? '') === 'charge.success') {
    $stmt = db()->prepare("UPDATE card_payments SET status='success', verified_at=NOW(), raw_payload=?, updated_at=NOW() WHERE reference=? AND status='pending'");
    $stmt->execute([$raw, $reference]);
}
http_response_code(200);
echo 'ok';
