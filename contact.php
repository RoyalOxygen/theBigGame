<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

if (is_post()) {
    if (!verify_csrf()) {
        flash_errors(['Invalid security token.']);
    } else {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $subject = trim((string) ($_POST['subject'] ?? ''));
        $message = trim((string) ($_POST['message'] ?? ''));
        $errors = [];
        if ($name === '' || $subject === '' || $message === '') {
            $errors[] = 'All fields are required.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Enter a valid email address.';
        }
        if ($errors) {
            flash_errors($errors);
        } else {
            db()->prepare('INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())')
                ->execute([$name, $email, $subject, $message]);
            flash('success', 'Your message has been received. The Secretariat will respond through the email you provided.');
            redirect('contact.php');
        }
    }
}

ob_start();
?>
<h2>Contact the Secretariat</h2>
<p><?= e((string) setting('contact_address', config('operator.address'))) ?><br>
<?= e((string) setting('contact_email', config('operator.email'))) ?><br>
<?= e((string) setting('contact_phone', config('operator.phone'))) ?>
<?php $alt = trim((string) setting('contact_phone_alt', '')); if ($alt !== ''): ?><br><?= e($alt) ?><?php endif; ?><br>
<?= e((string) setting('support_hours', '')) ?></p>
<?php foreach (errors() as $err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endforeach; ?>
<form method="post" class="mt-3">
    <?= csrf_field() ?>
    <div class="mb-3"><label class="form-label">Name</label><input class="form-control" name="name" required></div>
    <div class="mb-3"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required></div>
    <div class="mb-3"><label class="form-label">Subject</label><input class="form-control" name="subject" required></div>
    <div class="mb-3"><label class="form-label">Message</label><textarea class="form-control" name="message" rows="5" required></textarea></div>
    <button class="btn btn-official" type="submit">Send message</button>
</form>
<?php
$content = ob_get_clean();
view('public/page', ['title' => 'Contact Us', 'content' => $content]);
