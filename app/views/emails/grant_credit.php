<?php
ob_start(); ?>
<p>Dear <?= e($name) ?>,</p>
<p>A grant credit of <strong><?= e($amount) ?></strong> has been posted to your NARFCARD wallet.</p>
<p>Reference: <?= e($reference) ?></p>
<?php $body = ob_get_clean(); require __DIR__ . '/_layout.php'; ?>
