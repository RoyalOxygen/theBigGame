<?php layout_start('public', ['title' => $title]); ?>
<div class="auth-shell py-4">
    <div class="card-soft auth-card">
        <h1 class="h4 section-title">Choose a new password</h1>
        <?php foreach (errors() as $err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endforeach; ?>
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="token" value="<?= e($token) ?>">
            <div class="mb-3">
                <label class="form-label" for="password">New password</label>
                <input class="form-control" type="password" name="password" id="password" required minlength="8">
            </div>
            <div class="mb-3">
                <label class="form-label" for="password_confirm">Confirm password</label>
                <input class="form-control" type="password" name="password_confirm" id="password_confirm" required>
            </div>
            <button class="btn btn-official w-100" type="submit">Update password</button>
        </form>
    </div>
</div>
<?php layout_end('public'); ?>
