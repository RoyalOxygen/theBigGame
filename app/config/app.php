<?php

declare(strict_types=1);

return [
    'name' => getenv('APP_NAME') ?: 'National Relief Fund Card',
    'short_name' => 'NARFCARD',
    'env' => getenv('APP_ENV') ?: 'development',
    'debug' => filter_var(getenv('APP_DEBUG') ?: 'true', FILTER_VALIDATE_BOOLEAN),
    'url' => rtrim(getenv('APP_URL') ?: 'http://localhost:8080', '/'),
    'key' => getenv('APP_KEY') ?: 'narfcard-dev-key-32chars-secure!!',
    'timezone' => 'Africa/Lagos',
    'session_lifetime' => (int) (getenv('SESSION_LIFETIME') ?: 7200),
    'https_only' => filter_var(getenv('HTTPS_ONLY') ?: 'false', FILTER_VALIDATE_BOOLEAN),
    'operator' => [
        'name' => 'NARFCARD Programme Secretariat',
        'legal_name' => 'NARFCARD Relief Initiative',
        'address' => '',
        'email' => 'info@narfcard.ng',
        'phone' => '+234 700 NARFCARD',
        'phone_alt' => '+234 1 700 2273',
        'disclosure' => 'NARFCARD Programme is available to all eligible Nigerians, all applications are subject to review.',
    ],
    'mail' => [
        'mailer' => getenv('MAIL_MAILER') ?: 'smtp',
        'host' => getenv('MAIL_HOST') ?: 'mail.oxygendata.com.ng',
        'port' => (int) (getenv('MAIL_PORT') ?: 465),
        'username' => getenv('MAIL_USERNAME') ?: 'nerf@oxygendata.com.ng',
        'password' => getenv('MAIL_PASSWORD') ?: '',
        'encryption' => getenv('MAIL_ENCRYPTION') ?: 'ssl',
        'from_address' => getenv('MAIL_FROM_ADDRESS') ?: 'nerf@oxygendata.com.ng',
        'from_name' => getenv('MAIL_FROM_NAME') ?: 'NARFCARD Programme',
    ],
    'paystack' => [
        'public_key' => getenv('PAYSTACK_PUBLIC_KEY') ?: '',
        'secret_key' => getenv('PAYSTACK_SECRET_KEY') ?: '',
        'callback_url' => getenv('PAYSTACK_CALLBACK_URL') ?: '',
    ],
    'upload' => [
        'max_bytes' => 2 * 1024 * 1024,
        'photo_mimes' => ['image/jpeg', 'image/png', 'image/webp'],
        'doc_mimes' => ['image/jpeg', 'image/png', 'application/pdf'],
    ],
];
