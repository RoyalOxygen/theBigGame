<?php
$user = $user ?? auth_user();
$title = $title ?? 'Applicant Portal';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= e($title) ?> | <?= e(site_short()) ?></title>
    <meta name="theme-color" content="<?= e(theme_color()) ?>">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="<?= e(site_short()) ?>">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="manifest" href="<?= url('manifest.php') ?>">
    <link rel="apple-touch-icon" href="<?= e(brand_url('apple_icon_path', 'images/apple-touch-icon.png')) ?>">
    <link rel="icon" href="<?= e(brand_url('favicon_path', 'images/favicon.svg')) ?>">
    <?= theme_style_tag() ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
</head>
<body class="portal-body">
<div class="app-shell">
    <aside class="sidebar" id="userSidebar">
        <a class="brand text-white mb-4 d-flex align-items-center" href="<?= url('user/dashboard.php') ?>">
            <img src="<?= e(brand_url('logo_path', 'images/logo.svg')) ?>" alt="" width="40" height="40">
            <span class="ms-2">
                <strong><?= e(site_short()) ?></strong><br>
                <small>Applicant Portal</small>
            </span>
        </a>
        <nav class="sidebar-nav">
            <a class="<?= active_nav('dashboard') ?>" href="<?= url('user/dashboard.php') ?>"><i class="bi bi-grid"></i> Dashboard</a>
            <a class="<?= active_nav('apply') ?>" href="<?= url('user/apply.php') ?>"><i class="bi bi-file-earmark-text"></i> Application</a>
            <a class="<?= active_nav('wallet') ?>" href="<?= url('user/wallet.php') ?>"><i class="bi bi-wallet2"></i> Wallet</a>
            <a class="<?= active_nav('card') ?>" href="<?= url('user/card.php') ?>"><i class="bi bi-credit-card"></i> Card</a>
            <a class="<?= active_nav('profile') ?>" href="<?= url('user/profile.php') ?>"><i class="bi bi-person"></i> Profile</a>
            <button type="button" class="btn btn-outline-light text-start sidebar-install" data-install-app hidden><i class="bi bi-download"></i> Install app</button>
            <a href="<?= url('logout.php') ?>"><i class="bi bi-box-arrow-right"></i> Sign out</a>
        </nav>
    </aside>
    <div class="main-col">
        <div class="topbar">
            <div class="topbar-lead">
                <button class="btn btn-ghost-menu d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#userMenu" aria-label="Open menu">
                    <i class="bi bi-list"></i>
                </button>
                <div class="topbar-copy">
                    <div class="small text-muted d-none d-sm-block">Applicant Portal</div>
                    <strong class="topbar-title"><?= e($title) ?></strong>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="topbar-name d-none d-sm-inline"><?= e(full_name($user) ?: $user['email']) ?></span>
                <span class="avatar">
                    <?php if (!empty($user['photo_path'])): ?>
                        <img src="<?= upload_url($user['photo_path']) ?>" alt="">
                    <?php else: ?>
                        <?= e(initials($user)) ?>
                    <?php endif; ?>
                </span>
            </div>
        </div>
        <div class="content">
<?php if ($msg = flash('success')): ?><div class="alert alert-success alert-flash"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('error')): ?><div class="alert alert-danger alert-flash"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('info')): ?><div class="alert alert-info alert-flash"><?= e($msg) ?></div><?php endif; ?>
<?php foreach (errors() as $err): ?><div class="alert alert-danger alert-flash"><?= e($err) ?></div><?php endforeach; ?>
