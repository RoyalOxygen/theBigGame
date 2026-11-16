<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$content = '<h2>Terms and Conditions</h2>
<p>By creating an account or submitting an application you agree to these terms.</p>
<ol>
<li>' . e(site_short()) . ' is operated by ' . e((string) setting('programme_operator', 'NARFCARD Programme Secretariat')) . ' (' . e((string) setting('operator_legal_name', 'NARFCARD Relief Initiative')) . '). It is not a Federal or State Government service unless a formal authorisation is published on this website.</li>
<li>Submitting an application does not create an entitlement to a grant. Approval is discretionary and limited by available funding.</li>
<li>You must provide true information. False statements may lead to rejection.</li>
<li>There is no fee to apply. Optional card issuance and delivery charges are disclosed before payment and are not required to receive an approved grant.</li>
<li>Wallet balances reflect actual ledger credits. You cannot edit wallet records.</li>
<li>Physical cards, if issued, are identification cards for the programme. This portal does not generate bank card numbers or CVVs.</li>
<li>The Secretariat may update these terms. Continued use after publication constitutes acceptance.</li>
</ol>';

view('public/page', ['title' => 'Terms and Conditions', 'content' => $content]);
