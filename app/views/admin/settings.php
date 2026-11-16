<?php
layout_start('admin', ['title' => $title, 'admin' => $admin]);
$group = $catalog[$section];
$isTemplates = $section === 'templates';
$templates = $isTemplates ? email_templates() : [];
?>
<p class="text-muted">Update programme display, payments and email from this page. Applicants never see these controls.</p>
<ul class="nav nav-pills flex-nowrap gap-2 mb-4">
    <?php foreach ($catalog as $key => $item): ?>
        <li class="nav-item">
            <a class="nav-link <?= $key === $section ? 'active' : '' ?>" href="<?= url('admin/settings.php?section=' . urlencode($key)) ?>"><?= e($item['label']) ?></a>
        </li>
    <?php endforeach; ?>
</ul>

<?php if ($isTemplates): ?>
<p class="small text-muted"><?= e($group['help'] ?? '') ?></p>
<form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="section" value="<?= e($section) ?>">
    <?php foreach ($templates as $name => $tpl):
        $subjectKey = 'email_subject_' . $name;
        $bodyKey = 'email_body_' . $name;
        $subject = (string) ($values[$subjectKey] ?? '');
        $body = (string) ($values[$bodyKey] ?? '');
        if (trim($subject) === '') {
            $subject = (string) $tpl['subject'];
        }
        if (trim($body) === '') {
            $body = (string) $tpl['body'];
        }
        ?>
        <div class="card-soft mb-3">
            <h2 class="h6 mb-1"><?= e($tpl['label']) ?></h2>
            <p class="small text-muted mb-3">Placeholders: <code><?= e($tpl['placeholders']) ?></code></p>
            <div class="mb-3">
                <label class="form-label" for="<?= e($subjectKey) ?>">Subject</label>
                <input class="form-control" type="text" name="settings[<?= e($subjectKey) ?>]" id="<?= e($subjectKey) ?>" value="<?= e($subject) ?>">
            </div>
            <div class="mb-0">
                <label class="form-label" for="<?= e($bodyKey) ?>">Body</label>
                <textarea class="form-control" name="settings[<?= e($bodyKey) ?>]" id="<?= e($bodyKey) ?>" rows="8"><?= e($body) ?></textarea>
            </div>
        </div>
    <?php endforeach; ?>
    <button class="btn btn-official portal-primary" type="submit">Save <?= e($group['label']) ?></button>
</form>
<?php else: ?>
<div class="card-soft">
    <h2 class="h5 mb-1"><?= e($group['label']) ?></h2>
    <p class="small text-muted"><?= e($group['help'] ?? '') ?></p>
    <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="section" value="<?= e($section) ?>">
        <?php foreach ($group['fields'] as $key => $field):
            $type = $field['type'] ?? 'text';
            $current = (string) ($values[$key] ?? ($field['default'] ?? ''));
            ?>
            <div class="mb-3">
                <?php if ($type === 'checkbox'): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="settings[<?= e($key) ?>]" id="<?= e($key) ?>" value="1" <?= setting_bool($key, ($field['default'] ?? '1') === '1') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="<?= e($key) ?>"><?= e($field['label']) ?></label>
                    </div>
                <?php elseif ($type === 'file'): ?>
                    <label class="form-label" for="<?= e($key) ?>"><?= e($field['label']) ?></label>
                    <?php if ($current !== ''): ?>
                        <div class="mb-2"><img src="<?= e(upload_url($current)) ?>" alt="" style="max-height:64px;background:#fff;border:1px solid #dfe6dc;padding:4px;border-radius:8px"></div>
                    <?php endif; ?>
                    <input class="form-control" type="file" name="file_<?= e($key) ?>" id="<?= e($key) ?>" accept="<?= e($field['accept'] ?? 'image/*') ?>">
                    <div class="form-text">PNG, JPG, WEBP or SVG. Max 2 MB. Leave empty to keep the current file.</div>
                <?php elseif ($type === 'select'): ?>
                    <label class="form-label" for="<?= e($key) ?>"><?= e($field['label']) ?></label>
                    <select class="form-select" name="settings[<?= e($key) ?>]" id="<?= e($key) ?>">
                        <?php foreach ($field['options'] as $opt => $label): ?>
                            <option value="<?= e((string) $opt) ?>" <?= (string) $opt === $current ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php elseif ($type === 'textarea' || $type === 'html'): ?>
                    <label class="form-label" for="<?= e($key) ?>"><?= e($field['label']) ?></label>
                    <textarea class="form-control" name="settings[<?= e($key) ?>]" id="<?= e($key) ?>" rows="<?= $type === 'html' ? 8 : 4 ?>"><?= e($current) ?></textarea>
                <?php elseif ($type === 'password'): ?>
                    <label class="form-label" for="<?= e($key) ?>"><?= e($field['label']) ?></label>
                    <input class="form-control" type="password" name="settings[<?= e($key) ?>]" id="<?= e($key) ?>" value="" autocomplete="new-password">
                    <div class="form-text"><?= $current !== '' ? 'A value is already saved. Leave blank to keep it.' : 'Not set yet.' ?></div>
                <?php elseif ($type === 'color'): ?>
                    <label class="form-label" for="<?= e($key) ?>"><?= e($field['label']) ?></label>
                    <div class="d-flex gap-2 align-items-center">
                        <input class="form-control form-control-color" type="color" name="settings[<?= e($key) ?>]" id="<?= e($key) ?>" value="<?= e($current !== '' ? $current : ($field['default'] ?? '#073b22')) ?>">
                        <code><?= e($current !== '' ? $current : ($field['default'] ?? '')) ?></code>
                    </div>
                <?php else: ?>
                    <label class="form-label" for="<?= e($key) ?>"><?= e($field['label']) ?></label>
                    <input class="form-control" type="<?= e($type) ?>" name="settings[<?= e($key) ?>]" id="<?= e($key) ?>" value="<?= e($current) ?>">
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <button class="btn btn-official portal-primary" type="submit">Save <?= e($group['label']) ?></button>
    </form>
</div>
<?php if ($section === 'email'):
    $mailStatus = $mailStatus ?? ['smtp' => false, 'host' => '', 'port' => 465, 'username' => '', 'from' => '', 'mailer' => 'log', 'encryption' => 'ssl', 'password_set' => false];
    ?>
<div class="card-soft mt-4">
    <h2 class="h6">SMTP status</h2>
    <p class="small text-muted mb-2">cPanel SSL: host <code>mail.oxygendata.com.ng</code>, port <code>465</code>, encryption SSL, username the full mailbox. From must be that mailbox.</p>
    <ul class="small mb-3">
        <li>Driver: <?= e((string) $mailStatus['mailer']) ?> <?= !empty($mailStatus['smtp']) ? '(SMTP will send)' : '(messages stay in mailbox.log until SMTP is complete)' ?></li>
        <li>Host: <?= e((string) $mailStatus['host']) ?>:<?= e((string) $mailStatus['port']) ?> / <?= e((string) $mailStatus['encryption']) ?></li>
        <li>Username: <?= e((string) $mailStatus['username']) ?></li>
        <li>From: <?= e((string) $mailStatus['from']) ?></li>
        <li>Password: <?= !empty($mailStatus['password_set']) ? 'saved' : 'not saved — emails will not leave the server until you save it' ?></li>
    </ul>
    <form method="post" class="row g-2 align-items-end">
        <?= csrf_field() ?>
        <input type="hidden" name="section" value="email">
        <input type="hidden" name="action" value="test_mail">
        <div class="col-md-8">
            <label class="form-label" for="test_to">Send a test email to</label>
            <input class="form-control" type="email" name="test_to" id="test_to" value="<?= e((string) ($admin['email'] ?? '')) ?>" required>
        </div>
        <div class="col-md-4">
            <button class="btn btn-outline-success w-100" type="submit">Send test email</button>
        </div>
    </form>
</div>
<?php endif; ?>
<?php endif; ?>
<?php layout_end('admin'); ?>
