<?php
ob_start(); ?>
<p>Hello,</p>
<p>A password reset was requested for your NARFCARD account.</p>
<p><a href="<?= e($reset_url) ?>" style="display:inline-block;background:#0b5c35;color:#fff;padding:12px 18px;border-radius:6px;text-decoration:none;">Reset password</a></p>
<p>This link expires in 1 hour. If you did not request a reset, you may ignore this message.</p>
<?php $body = ob_get_clean(); require __DIR__ . '/_layout.php'; ?>
