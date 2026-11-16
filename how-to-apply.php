<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$content = '<h2>How to Apply</h2>
<ol>
<li><strong>Create an account</strong> with your email address and a strong password.</li>
<li><strong>Verify your email</strong> using the link sent to your inbox. Unverified accounts cannot apply.</li>
<li><strong>Complete the four-step form:</strong> personal information, occupation, bank account, then review and submit.</li>
<li><strong>Note your application reference</strong> and wait for review. You will receive email updates.</li>
<li>If approved, sign in to view your grant. Funds appear in your wallet only when they have been allocated and credited.</li>
<li>Request a physical credit card for withdrawal. If you request one, processing charges are shown before payment. Bank transfer remains available as an alternative disbursement channel.</li>
</ol>
<p>There is no application fee.</p>
<p><a class="btn btn-official" href="' . e(url('register.php')) . '">Start application</a></p>';

view('public/page', ['title' => 'How to Apply', 'content' => $content]);
