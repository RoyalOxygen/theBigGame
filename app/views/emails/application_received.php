<?php
ob_start(); ?>
<p>Dear <?= e($name) ?>,</p>
<p>Your NARFCARD application has been received.</p>
<p><strong>Applicant:</strong> <?= e($name) ?><br>
<strong>Reference:</strong> <?= e($reference) ?><br>
<strong>Date:</strong> <?= e($date) ?><br>
<strong>Status:</strong> UNDER REVIEW</p>
<p>You can check progress at any time by signing in to your applicant dashboard.</p>
<?php $body = ob_get_clean(); require __DIR__ . '/_layout.php'; ?>
