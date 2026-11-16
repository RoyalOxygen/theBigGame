<?php
ob_start(); ?>
<p>Dear <?= e($name) ?>,</p>
<p>Collection of NARFCARD request <?= e($reference) ?> has been recorded as completed.</p>
<?php $body = ob_get_clean(); require __DIR__ . '/_layout.php'; ?>
