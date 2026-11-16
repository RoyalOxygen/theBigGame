<?php

declare(strict_types=1);

return [
    'display' => [
        'label' => 'Site display',
        'help' => 'Names, logo, colours and homepage text shown to applicants.',
        'fields' => [
            'programme_name' => ['label' => 'Programme name', 'type' => 'text', 'default' => 'National Relief Fund Card'],
            'programme_short_name' => ['label' => 'Short name', 'type' => 'text', 'default' => 'NARFCARD'],
            'programme_tagline' => ['label' => 'Header tagline', 'type' => 'text', 'default' => 'NARFCARD Programme'],
            'site_meta_description' => ['label' => 'Search / share description', 'type' => 'textarea', 'default' => 'National Relief Fund Card programme application portal operated by the NARFCARD Programme Secretariat.'],
            'hero_kicker' => ['label' => 'Homepage kicker', 'type' => 'text', 'default' => 'Relief Programme Portal'],
            'hero_title' => ['label' => 'Homepage headline', 'type' => 'text', 'default' => 'National Relief Fund Card'],
            'hero_lead' => ['label' => 'Homepage introduction', 'type' => 'textarea', 'default' => 'A transparent application portal for eligible residents seeking one-off household relief support administered by the NARFCARD Programme Secretariat.'],
            'objective_text' => ['label' => 'Objective card text', 'type' => 'textarea', 'default' => 'The programme reviews applications from eligible households and, subject to available funding, may approve a one-off relief amount credited to an applicant wallet.'],
            'transparency_text' => ['label' => 'Transparency card text', 'type' => 'textarea', 'default' => 'Card issuance is optional. Any processing or delivery charge is disclosed before payment and is not a condition for viewing an approved grant.'],
            'footer_text' => ['label' => 'Footer copyright line', 'type' => 'textarea', 'default' => 'NARFCARD Programme Secretariat. All rights reserved. This website does not display government seals, coats of arms or official endorsements.'],
            'theme_color' => ['label' => 'Primary colour (hex)', 'type' => 'color', 'default' => '#073b22'],
            'accent_color' => ['label' => 'Accent colour (hex)', 'type' => 'color', 'default' => '#c9a227'],
            'logo_path' => ['label' => 'Logo', 'type' => 'file', 'accept' => 'image/png,image/jpeg,image/webp,image/svg+xml', 'default' => ''],
            'favicon_path' => ['label' => 'Favicon', 'type' => 'file', 'accept' => 'image/png,image/jpeg,image/webp,image/svg+xml,image/x-icon', 'default' => ''],
            'apple_icon_path' => ['label' => 'Apple / PWA icon', 'type' => 'file', 'accept' => 'image/png,image/jpeg,image/webp', 'default' => ''],
        ],
    ],
    'operator' => [
        'label' => 'Operator & contact',
        'help' => 'Shown in the header bar, footer, contact page and legal notices.',
        'fields' => [
            'programme_operator' => ['label' => 'Operator name', 'type' => 'text', 'default' => 'NARFCARD Programme Secretariat'],
            'operator_legal_name' => ['label' => 'Legal name', 'type' => 'text', 'default' => 'NARFCARD Relief Initiative'],
            'operator_disclosure' => ['label' => 'Public disclosure', 'type' => 'textarea', 'default' => 'NARFCARD is operated by the NARFCARD Programme Secretariat, an independent civic relief initiative. This platform is not a website of the Federal Government of Nigeria, any Federal Ministry, or any State Government unless a formal authorisation is published here.'],
            'contact_email' => ['label' => 'Public email', 'type' => 'email', 'default' => 'info@narfcard.ng'],
            'contact_phone' => ['label' => 'Public phone', 'type' => 'text', 'default' => '+234 700 NARFCARD'],
            'contact_phone_alt' => ['label' => 'Alternative phone', 'type' => 'text', 'default' => '+234 1 700 2273'],
            'contact_address' => ['label' => 'Office address', 'type' => 'textarea', 'default' => 'Plot 14, Adeola Odeku Street, Victoria Island, Lagos, Nigeria'],
            'support_hours' => ['label' => 'Support hours', 'type' => 'text', 'default' => 'Monday to Friday, 9:00 to 16:00 WAT'],
        ],
    ],
    'programme' => [
        'label' => 'Programme rules',
        'help' => 'Controls registration, grant limits and applicant-facing notices.',
        'fields' => [
            'allow_registration' => ['label' => 'Allow new applicant registration', 'type' => 'checkbox', 'default' => '1'],
            'applications_open' => ['label' => 'Accept new grant applications', 'type' => 'checkbox', 'default' => '1'],
            'require_email_verification' => ['label' => 'Require email verification before applying', 'type' => 'checkbox', 'default' => '1'],
            'min_age' => ['label' => 'Minimum applicant age', 'type' => 'number', 'default' => '18'],
            'min_grant_amount' => ['label' => 'Minimum grant (NGN)', 'type' => 'number', 'default' => '25000'],
            'max_grant_amount' => ['label' => 'Maximum grant (NGN)', 'type' => 'number', 'default' => '250000'],
            'grant_disbursement_note' => ['label' => 'Grant disbursement note', 'type' => 'textarea', 'default' => 'Approved grants are credited to the applicant wallet only when programme funding has been allocated. Physical card issuance is optional. Bank transfer remains available as an alternative disbursement channel after approval.'],
            'maintenance_message' => ['label' => 'Site banner (leave blank to hide)', 'type' => 'textarea', 'default' => ''],
        ],
    ],
    'payments' => [
        'label' => 'Payments',
        'help' => 'Paystack keys and optional card charges. Leave secret key blank to keep the current value.',
        'fields' => [
            'payments_enabled' => ['label' => 'Enable card-fee payments', 'type' => 'checkbox', 'default' => '1'],
            'paystack_public_key' => ['label' => 'Paystack public key', 'type' => 'text', 'default' => ''],
            'paystack_secret_key' => ['label' => 'Paystack secret key', 'type' => 'password', 'secret' => true, 'default' => ''],
            'paystack_callback_url' => ['label' => 'Paystack callback URL (optional)', 'type' => 'text', 'default' => ''],
            'card_processing_fee' => ['label' => 'Card processing fee (NGN)', 'type' => 'number', 'default' => '2500'],
            'card_delivery_fee' => ['label' => 'Card delivery fee (NGN)', 'type' => 'number', 'default' => '1500'],
            'card_fees_note' => ['label' => 'Fee disclosure shown before payment', 'type' => 'textarea', 'default' => 'Card issuance is optional. These charges are for production and delivery only. They are not required to view or receive an approved grant. Bank transfer remains available as an alternative disbursement channel.'],
        ],
    ],
    'email' => [
        'label' => 'Email delivery',
        'help' => 'cPanel Secure SSL: host mail.oxygendata.com.ng, port 465, encryption SSL, username the full mailbox (nerf@oxygendata.com.ng). From email must be that same mailbox. Leave password blank to keep the current value.',
        'fields' => [
            'mail_mailer' => ['label' => 'Mail driver', 'type' => 'select', 'options' => ['log' => 'Log only (no SMTP)', 'smtp' => 'SMTP'], 'default' => 'smtp'],
            'mail_host' => ['label' => 'SMTP host', 'type' => 'text', 'default' => 'mail.oxygendata.com.ng'],
            'mail_port' => ['label' => 'SMTP port', 'type' => 'number', 'default' => '465'],
            'mail_username' => ['label' => 'SMTP username', 'type' => 'text', 'default' => 'nerf@oxygendata.com.ng'],
            'mail_password' => ['label' => 'SMTP password', 'type' => 'password', 'secret' => true, 'default' => ''],
            'mail_encryption' => ['label' => 'Encryption', 'type' => 'select', 'options' => ['ssl' => 'SSL (port 465)', 'tls' => 'TLS (port 587)', 'none' => 'None'], 'default' => 'ssl'],
            'mail_from_address' => ['label' => 'From email', 'type' => 'email', 'default' => 'nerf@oxygendata.com.ng'],
            'mail_from_name' => ['label' => 'From name', 'type' => 'text', 'default' => 'NARFCARD Programme'],
            'email_footer' => ['label' => 'Email footer', 'type' => 'textarea', 'default' => 'This message was sent by the NARFCARD Programme Secretariat, an independent civic relief initiative. It is not a communication from the Federal Government of Nigeria unless expressly stated.'],
        ],
    ],
    'templates' => [
        'label' => 'Email templates',
        'help' => 'Each message is pre-filled. Edit the subject or body, or replace the text entirely. Placeholders such as {{name}} are filled when the email is sent. Header, colours and footer still come from Site display and Email delivery.',
        'fields' => (static function (): array {
            $fields = [];
            foreach (require __DIR__ . '/email_templates.php' as $name => $tpl) {
                $fields['email_subject_' . $name] = [
                    'label' => $tpl['label'] . ' — subject',
                    'type' => 'text',
                    'default' => $tpl['subject'],
                    'template' => $name,
                ];
                $fields['email_body_' . $name] = [
                    'label' => $tpl['label'] . ' — body',
                    'type' => 'html',
                    'default' => $tpl['body'],
                    'template' => $name,
                    'placeholders' => $tpl['placeholders'],
                ];
            }
            return $fields;
        })(),
    ],
];
