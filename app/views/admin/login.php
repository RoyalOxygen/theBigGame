<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Administrator Login | <?= e(site_short()) ?></title>
    <link rel="icon" href="<?= e(brand_url('favicon_path', 'images/favicon.svg')) ?>">
    <?= theme_style_tag() ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
</head>
<body class="auth-shell">
<div class="card-soft auth-card">
    <h1 class="h4 section-title">Administrator access</h1>
    <p class="small text-muted">Restricted Super Administrator portal. Applicants cannot sign in here. Use the account created during install.php.</p>
    <?php foreach (errors() as $err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endforeach; ?>
    <form method="post">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input class="form-control" type="email" name="email" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input class="form-control" type="password" name="password" required>
        </div>
        <button class="btn btn-official w-100" type="submit">Sign in</button>
    </form>
</div>
</body>
</html>
