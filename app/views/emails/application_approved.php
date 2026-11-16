<?php
ob_start(); ?>
<p>Dear <?= e($name) ?>,</p>
<p>Your NARFCARD application <strong><?= e($reference) ?></strong> has been approved.</p>
<p>Approved grant amount: <strong><?= e($amount) ?></strong><br>
Approval reference: <?= e($approval_reference) ?></p>
<p>Please sign in to your dashboard to view grant details. Funds appear in your wallet only when they have been allocated and credited.</p>
<p>A physical card is optional. Bank transfer remains available as an alternative disbursement channel after approval.</p>
<?php $body = ob_get_clean(); require __DIR__ . '/_layout.php'; ?>
