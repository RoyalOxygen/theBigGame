<?php layout_start('public', ['title' => $title]); ?>
<div class="auth-shell py-4">
    <div class="card-soft auth-card">
        <h1 class="h4 section-title">Verify your email</h1>
        <p>Open the verification link sent to your inbox. If you cannot find it, request another email below.</p>
        <form method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label" for="email">Email address</label>
                <input class="form-control" type="email" name="email" id="email" required value="<?= e((string) ($_SESSION['pending_verify_email'] ?? old('email'))) ?>">
            </div>
            <button class="btn btn-official w-100" type="submit">Resend verification email</button>
        </form>
    </div>
</div>
<?php layout_end('public'); ?>
