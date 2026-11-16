<?php layout_start('public', ['title' => $title]); ?>
<div class="auth-shell py-4">
    <div class="card-soft auth-card">
        <h1 class="h4 section-title">Applicant login</h1>
        <?php foreach (errors() as $err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endforeach; ?>
        <form method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label" for="email">Email address</label>
                <input class="form-control" type="email" name="email" id="email" required value="<?= e((string) old('email')) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input class="form-control" type="password" name="password" id="password" required>
            </div>
            <button class="btn btn-official w-100" type="submit">Sign in</button>
        </form>
        <p class="mt-3 mb-0 small"><a href="<?= url('forgot-password.php') ?>">Forgot password?</a> &middot; <a href="<?= url('register.php') ?>">Create an account</a></p>
    </div>
</div>
<?php layout_end('public'); ?>
