<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$content = '<h2>Privacy Policy</h2>
<p>' . e((string) setting('programme_operator', 'NARFCARD Programme Secretariat')) . ' (' . e((string) setting('operator_legal_name', 'NARFCARD Relief Initiative')) . ') ("we") processes personal data to administer applications, communicate decisions, credit approved grants and, where requested, produce an optional identification card.</p>
<h3>Data we collect</h3>
<ul>
<li>Account data: email address and password hash.</li>
<li>Identity and contact data provided in the application.</li>
<li>Occupation and financial circumstance information you submit.</li>
<li>Bank account details for disbursement (never PINs, CVVs or internet banking passwords).</li>
<li>Payment references if you choose to pay optional card fees.</li>
<li>Technical logs reasonably required for security.</li>
</ul>
<h3>Lawful basis</h3>
<p>We process data with your consent and as necessary to perform the application service, in accordance with the Nigeria Data Protection Act (NDPA).</p>
<h3>Sharing</h3>
<p>We do not sell personal data. Limited sharing occurs with payment processors (Paystack) for optional card-fee payments, email delivery providers, and authorised administrators of this programme.</p>
<h3>Retention</h3>
<p>Application records are retained for the period required to administer the programme, meet audit obligations and handle disputes, then deleted or anonymised.</p>
<h3>Your rights</h3>
<p>You may request access, correction or deletion of personal data, subject to legal retention duties, by contacting ' . e((string) setting('contact_email', config('operator.email'))) . '.</p>
<h3>Security</h3>
<p>We use hashed passwords, prepared statements, CSRF protection, access control and restricted file storage. No method of transmission is completely secure.</p>';

view('public/page', ['title' => 'Privacy Policy', 'content' => $content]);
