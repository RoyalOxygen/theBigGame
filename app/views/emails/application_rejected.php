<?php
ob_start(); ?>
<p>Dear <?= e($name) ?>,</p>
<p>Your NARFCARD application <strong><?= e($reference) ?></strong> was not approved.</p>
<p><strong>Reason:</strong> <?= e($reason) ?></p>
<p>If you believe this decision was made in error, contact the Secretariat through the published contact channels.</p>
<?php $body = ob_get_clean(); require __DIR__ . '/_layout.php'; ?>
