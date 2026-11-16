<?php
ob_start(); ?>
<p>Dear <?= e($name) ?>,</p>
<p>Additional information is required for application <strong><?= e($reference) ?></strong>.</p>
<p><?= nl2br(e($message)) ?></p>
<p>Please sign in and update your application.</p>
<?php $body = ob_get_clean(); require __DIR__ . '/_layout.php'; ?>
