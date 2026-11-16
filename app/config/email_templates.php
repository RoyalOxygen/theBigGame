<?php

declare(strict_types=1);

return [
    'verify_email' => [
        'label' => 'Verify email',
        'placeholders' => '{{name}} {{email}} {{verify_url}}',
        'subject' => 'Verify your NARFCARD email address',
        'body' => '<p>Hello {{name}},</p>
<p>Please verify the email address <strong>{{email}}</strong> to continue your NARFCARD application.</p>
<p><a href="{{verify_url}}">Verify email address</a></p>
<p>This link expires in 24 hours. If you did not create an account, you may ignore this message.</p>',
    ],
    'password_reset' => [
        'label' => 'Password reset',
        'placeholders' => '{{name}} {{email}} {{reset_url}}',
        'subject' => 'Reset your NARFCARD password',
        'body' => '<p>Hello {{name}},</p>
<p>A password reset was requested for your NARFCARD account ({{email}}).</p>
<p><a href="{{reset_url}}">Reset password</a></p>
<p>This link expires in 1 hour. If you did not request a reset, you may ignore this message.</p>',
    ],
    'application_received' => [
        'label' => 'Application received',
        'placeholders' => '{{name}} {{email}} {{reference}} {{date}}',
        'subject' => 'Your NARFCARD Application Has Been Received',
        'body' => '<p>Dear {{name}},</p>
<p>Your NARFCARD application has been received.</p>
<p><strong>Applicant:</strong> {{name}}<br>
<strong>Reference:</strong> {{reference}}<br>
<strong>Date:</strong> {{date}}<br>
<strong>Status:</strong> UNDER REVIEW</p>
<p>You can check progress at any time by signing in to your applicant dashboard.</p>',
    ],
    'additional_info' => [
        'label' => 'Additional information',
        'placeholders' => '{{name}} {{email}} {{reference}} {{message}}',
        'subject' => 'Additional information required for your NARFCARD application',
        'body' => '<p>Dear {{name}},</p>
<p>Additional information is required for application <strong>{{reference}}</strong>.</p>
<p>{{message}}</p>
<p>Please sign in and update your application.</p>',
    ],
    'application_approved' => [
        'label' => 'Application approved',
        'placeholders' => '{{name}} {{email}} {{reference}} {{amount}} {{approval_reference}}',
        'subject' => 'Your NARFCARD application has been approved',
        'body' => '<p>Dear {{name}},</p>
<p>Your NARFCARD application <strong>{{reference}}</strong> has been approved.</p>
<p>Approved grant amount: <strong>{{amount}}</strong><br>
Approval reference: {{approval_reference}}</p>
<p>Please sign in to your dashboard to view grant details. Funds appear in your wallet only when they have been allocated and credited.</p>
<p>A physical card is optional. Bank transfer remains available as an alternative disbursement channel after approval.</p>',
    ],
    'application_rejected' => [
        'label' => 'Application rejected',
        'placeholders' => '{{name}} {{email}} {{reference}} {{reason}}',
        'subject' => 'Update on your NARFCARD application',
        'body' => '<p>Dear {{name}},</p>
<p>Your NARFCARD application <strong>{{reference}}</strong> was not approved.</p>
<p><strong>Reason:</strong> {{reason}}</p>
<p>If you believe this decision was made in error, contact the Secretariat through the published contact channels.</p>',
    ],
    'grant_credit' => [
        'label' => 'Grant credited',
        'placeholders' => '{{name}} {{email}} {{reference}} {{amount}}',
        'subject' => 'A grant credit has been posted to your NARFCARD wallet',
        'body' => '<p>Dear {{name}},</p>
<p>A grant credit of <strong>{{amount}}</strong> has been posted to your NARFCARD wallet.</p>
<p>Reference: {{reference}}</p>
<p>Sign in to Wallet to view the credited amount. Physical card issuance is optional.</p>',
    ],
    'card_request' => [
        'label' => 'Card request confirmed',
        'placeholders' => '{{name}} {{email}} {{reference}} {{payment}} {{state}} {{lga}} {{location}}',
        'subject' => 'Your NARFCARD Request Has Been Confirmed',
        'body' => '<p>Dear {{name}},</p>
<p>Your NARFCARD request has been confirmed.</p>
<p><strong>Card request reference:</strong> {{reference}}<br>
<strong>Payment:</strong> {{payment}}<br>
<strong>State / LGA:</strong> {{state}} / {{lga}}<br>
<strong>Collection or delivery:</strong> {{location}}</p>
<p>Your card request has been successfully received. You will receive another notification through your registered email address when your card arrives at your designated collection centre and is ready for collection.</p>',
    ],
    'payment_confirmation' => [
        'label' => 'Payment confirmation',
        'placeholders' => '{{name}} {{email}} {{reference}} {{amount}} {{description}}',
        'subject' => 'Payment confirmation',
        'body' => '<p>Dear {{name}},</p>
<p>We have confirmed your payment of <strong>{{amount}}</strong> with reference {{reference}}.</p>
<p>{{description}}</p>',
    ],
    'card_ready' => [
        'label' => 'Card ready for collection',
        'placeholders' => '{{name}} {{email}} {{reference}} {{centre}} {{address}} {{state}} {{lga}}',
        'subject' => 'Your NARFCARD is ready for collection',
        'body' => '<p>Dear {{name}},</p>
<p>Your NARFCARD (reference {{reference}}) is ready for collection.</p>
<p><strong>Centre:</strong> {{centre}}<br>
<strong>Address:</strong> {{address}}<br>
<strong>State / LGA:</strong> {{state}} / {{lga}}</p>
<p>Bring a valid means of identification matching your application.</p>',
    ],
    'card_delivered' => [
        'label' => 'Card delivered',
        'placeholders' => '{{name}} {{email}} {{reference}}',
        'subject' => 'Your NARFCARD collection has been confirmed',
        'body' => '<p>Dear {{name}},</p>
<p>Collection of NARFCARD request {{reference}} has been recorded as completed.</p>',
    ],
];
