<?php
ob_start(); ?>
<p>Dear <?= e($name) ?>,</p>
<p>Your NARFCARD request has been confirmed.</p>
<p><strong>Card request reference:</strong> <?= e($reference) ?><br>
<strong>Payment:</strong> <?= e($payment) ?><br>
<strong>State / LGA:</strong> <?= e($state) ?> / <?= e($lga) ?><br>
<strong>Collection or delivery:</strong> <?= e($location) ?></p>
<p style="background:#fff8e6;padding:12px;border-left:4px solid #c9a227;">Your card request has been successfully received. You will receive another notification through your registered email address when your card arrives at your designated collection centre and is ready for collection.</p>
<?php $body = ob_get_clean(); require __DIR__ . '/_layout.php'; ?>
