<?php layout_start('public', ['title' => $title]); ?>
<div class="auth-shell py-4">
    <div class="card-soft auth-card">
        <h1 class="h4 section-title">Create an applicant account</h1>
        <p class="text-muted">Use a personal email address. You must verify it before you can complete an application.</p>
        <?php foreach (errors() as $err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endforeach; ?>
        <form method="post" novalidate>
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label" for="email">Email address</label>
                <input class="form-control" type="email" name="email" id="email" required value="<?= e((string) old('email')) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input class="form-control" type="password" name="password" id="password" required minlength="8">
                <div class="form-text">At least 8 characters, with uppercase, lowercase and a number.</div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password_confirm">Confirm password</label>
                <input class="form-control" type="password" name="password_confirm" id="password_confirm" required>
            </div>
            <button class="btn btn-official w-100" type="submit">Create account</button>
        </form>
        <p class="mt-3 mb-0 small">Already registered? <a href="<?= url('login.php') ?>">Sign in</a></p>
    </div>
</div>
<?php layout_end('public'); ?>
