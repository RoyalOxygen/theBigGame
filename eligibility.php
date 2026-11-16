<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$content = '<h2>Eligibility Requirements</h2>
<p>You may apply if you meet all of the following:</p>
<ul>
<li>You are a Nigerian resident aged ' . e((string) setting('min_age', '18')) . ' years or above.</li>
<li>You have a valid National Identity Number (NIN).</li>
<li>You can provide a Nigerian bank account in your name for disbursement.</li>
<li>Your household income falls within the published need bands used during review.</li>
<li>You have not already submitted an application with the same email or identification number.</li>
</ul>
<p><strong>Eligibility does not guarantee approval.</strong> Decisions depend on review and available programme funding. False information may lead to rejection and exclusion from future rounds.</p>
<h3>Who should not apply</h3>
<ul>
<li>Persons under ' . e((string) setting('min_age', '18')) . ' years of age.</li>
<li>Applicants unable to verify identity or bank account ownership.</li>
<li>Duplicate applications.</li>
</ul>';

view('public/page', ['title' => 'Eligibility Requirements', 'content' => $content]);
