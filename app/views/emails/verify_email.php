<?php
ob_start(); ?>
<p>Hello,</p>
<p>Please verify the email address <strong><?= e($email ?? '') ?></strong> to continue your NARFCARD application.</p>
<p><a href="<?= e($verify_url) ?>" style="display:inline-block;background:#0b5c35;color:#fff;padding:12px 18px;border-radius:6px;text-decoration:none;">Verify email address</a></p>
<p>This link expires in 24 hours. If you did not create an account, you may ignore this message.</p>
<?php $body = ob_get_clean(); require __DIR__ . '/_layout.php'; ?>
