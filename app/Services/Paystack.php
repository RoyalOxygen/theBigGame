<?php

declare(strict_types=1);

namespace App\Services;

class Paystack
{
    public static function initialize(string $email, int $amountKobo, string $reference, string $callbackUrl, array $metadata = []): array
    {
        $secret = paystack_secret();
        if ($secret === '' || str_contains($secret, 'xxxxxxxx')) {
            return [
                'status' => true,
                'demo' => true,
                'data' => [
                    'authorization_url' => url('user/card-pay.php?demo=1&reference=' . urlencode($reference)),
                    'access_code' => 'demo',
                    'reference' => $reference,
                ],
            ];
        }

        return self::request('https://api.paystack.co/transaction/initialize', [
            'email' => $email,
            'amount' => $amountKobo,
            'reference' => $reference,
            'callback_url' => $callbackUrl,
            'metadata' => $metadata,
            'currency' => 'NGN',
        ]);
    }

    public static function verify(string $reference): array
    {
        $secret = paystack_secret();
        if ($secret === '' || str_contains($secret, 'xxxxxxxx')) {
            return [
                'status' => true,
                'demo' => true,
                'data' => [
                    'status' => 'success',
                    'reference' => $reference,
                    'amount' => 0,
                    'gateway_response' => 'Demo verification',
                    'paid_at' => date('c'),
                    'channel' => 'demo',
                ],
            ];
        }

        return self::request('https://api.paystack.co/transaction/verify/' . rawurlencode($reference), null, 'GET');
    }

    public static function resolveAccount(string $accountNumber, string $bankCode): array
    {
        $secret = paystack_secret();
        if ($secret === '' || str_contains($secret, 'xxxxxxxx')) {
            return [
                'status' => true,
                'demo' => true,
                'data' => [
                    'account_number' => $accountNumber,
                    'account_name' => 'VERIFIED ACCOUNT HOLDER',
                    'bank_id' => 0,
                ],
            ];
        }

        $url = 'https://api.paystack.co/bank/resolve?account_number=' . urlencode($accountNumber) . '&bank_code=' . urlencode($bankCode);
        return self::request($url, null, 'GET');
    }

    private static function request(string $url, ?array $payload, string $method = 'POST'): array
    {
        $secret = paystack_secret();
        $ch = curl_init($url);
        $headers = [
            'Authorization: Bearer ' . $secret,
            'Content-Type: application/json',
            'Cache-Control: no-cache',
        ];
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
        ]);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload ?? []));
        }
        $raw = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($raw === false) {
            return ['status' => false, 'message' => $err ?: 'Network error'];
        }
        $decoded = json_decode((string) $raw, true);
        return is_array($decoded) ? $decoded : ['status' => false, 'message' => 'Invalid response'];
    }
}
