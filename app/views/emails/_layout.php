<?php
$body = $body ?? '';
?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(site_short()) ?></title>
</head>
<body style="margin:0;background:#f6f7f4;font-family:Arial,sans-serif;color:#1c2430;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f6f7f4;padding:24px 0;">
<tr><td align="center">
<table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background:#ffffff;border:1px solid #dfe6dc;border-radius:12px;overflow:hidden;">
<tr><td style="background:<?= e(theme_color()) ?>;color:#fff;padding:20px 28px;border-bottom:4px solid <?= e(accent_color()) ?>;">
<div style="font-size:18px;font-weight:bold;"><?= e(site_short()) ?></div>
<div style="font-size:12px;letter-spacing:1px;"><?= e(strtoupper(site_name())) ?></div>
</td></tr>
<tr><td style="padding:28px;font-size:15px;line-height:1.6;"><?= $body ?></td></tr>
<tr><td style="padding:16px 28px;background:#f0f5f1;font-size:12px;color:#5b6775;">
<?= e((string) setting('email_footer', 'This message was sent by the NARFCARD Programme Secretariat, an independent civic relief initiative. It is not a communication from the Federal Government of Nigeria unless expressly stated.')) ?>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
