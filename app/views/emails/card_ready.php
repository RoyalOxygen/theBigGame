<?php
ob_start(); ?>
<p>Dear <?= e($name) ?>,</p>
<p>Your NARFCARD (reference <?= e($reference) ?>) is ready for collection.</p>
<p><strong>Centre:</strong> <?= e($centre) ?><br>
<strong>Address:</strong> <?= e($address) ?><br>
<strong>State / LGA:</strong> <?= e($state) ?> / <?= e($lga) ?></p>
<p>Bring a valid means of identification matching your application.</p>
<?php $body = ob_get_clean(); require __DIR__ . '/_layout.php'; ?>
