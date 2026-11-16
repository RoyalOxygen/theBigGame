<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';

use App\Services\Paystack;

require_user();
if (!verify_csrf()) {
    json_response(['status' => false, 'message' => 'Invalid token'], 419);
}
$payload = json_decode((string) file_get_contents('php://input'), true) ?: $_POST;
$account = preg_replace('/\D+/', '', (string) ($payload['account_number'] ?? '')) ?? '';
$code = (string) ($payload['bank_code'] ?? '');
if (!preg_match('/^\d{10}$/', $account) || $code === '') {
    json_response(['status' => false, 'message' => 'Provide a 10-digit account number and bank.']);
}
json_response(Paystack::resolveAccount($account, $code));
