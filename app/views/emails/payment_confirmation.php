<?php
ob_start(); ?>
<p>Dear <?= e($name) ?>,</p>
<p>We have confirmed your payment of <strong><?= e($amount) ?></strong> with reference <?= e($reference) ?>.</p>
<p><?= e($description ?? 'Card issuance payment') ?></p>
<?php $body = ob_get_clean(); require __DIR__ . '/_layout.php'; ?>
