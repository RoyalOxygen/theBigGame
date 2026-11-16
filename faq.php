<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$content = '<h2>Frequently Asked Questions</h2>
<p><strong>What is NARFCARD?</strong><br>NARFCARD Programme is a civic relief initiative, sponsored by multi-business cooperation in Nigeria.</p>
<p><strong>Do I pay to apply?</strong><br>No. There is no application fee. Mandatory card processing charges are disclosed before payment and do not unlock or lock an approved grant.</p>
<p><strong>How will I know my application status?</strong><br>Status is shown on your dashboard and sent by email whenever it changes.</p>
<p><strong>When is money available in my wallet?</strong><br>Only after a review and grant is approved. your application and programme funds have actually been allocated and credited.</p>
<p><strong>Must I request a physical card?</strong><br>Yes. Card issuance is mandatory for disbursement. Approved grants can be alternatively disbursed by bank transfer.</p>
<p><strong>What identification is accepted?</strong><br>Only National Identity Number (NIN) is required.</p>
<p><strong>How is my data protected?</strong><br>See the Privacy Policy. Processing is carried out in line with the Nigeria Data Protection Act (NDPA).</p>
<p><strong>Can I install NARFCARD on my phone?</strong><br>Yes. Use <strong>Click to install</strong> on the homepage. The browser adds the portal to your home screen.</p>';

view('public/page', ['title' => 'Frequently Asked Questions', 'content' => $content]);
