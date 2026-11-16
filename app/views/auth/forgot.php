<?php layout_start('public', ['title' => $title]); ?>
<div class="auth-shell py-4">
    <div class="card-soft auth-card">
        <h1 class="h4 section-title">Reset password</h1>
        <p>Enter your account email. If it is registered, we will send a reset link.</p>
        <?php foreach (errors() as $err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endforeach; ?>
        <form method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label" for="email">Email address</label>
                <input class="form-control" type="email" name="email" id="email" required>
            </div>
            <button class="btn btn-official w-100" type="submit">Send reset link</button>
        </form>
    </div>
</div>
<?php layout_end('public'); ?>
