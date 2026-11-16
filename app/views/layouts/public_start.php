<?php
$title = $title ?? site_name();
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= e($title) ?> | <?= e(site_short()) ?></title>
    <meta name="description" content="<?= e((string) setting('site_meta_description', 'National Relief Fund Card programme application portal.')) ?>">
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
<body>
<div class="official-bar">
    <div class="container wrap">
        <span><?= e((string) setting('programme_operator', 'NARFCARD Programme')) ?> &middot; National Relief Fund</span>
        <span><?= e((string) setting('contact_phone', '')) ?> &middot; <?= e((string) setting('contact_email', '')) ?></span>
    </div>
</div>
<header class="site-header">
    <div class="container wrap">
        <a class="brand" href="<?= url() ?>">
            <img src="<?= e(brand_url('logo_path', 'images/logo.svg')) ?>" alt="<?= e(site_short()) ?> logo">
            <span>
                <span class="brand-title"><?= e(site_name()) ?></span><br>
                <span class="brand-sub"><?= e((string) setting('programme_tagline', site_short() . ' Programme')) ?></span>
            </span>
        </a>
        <nav class="nav-main">
            <a class="<?= in_array(current_path(), ['/', '/index.php'], true) ? 'active' : '' ?>" href="<?= url() ?>">Home</a>
            <a class="<?= active_nav('eligibility') ?>" href="<?= url('eligibility.php') ?>">Eligibility</a>
            <a class="<?= active_nav('how-to-apply') ?>" href="<?= url('how-to-apply.php') ?>">How to Apply</a>
            <a class="<?= active_nav('faq') ?>" href="<?= url('faq.php') ?>">FAQ</a>
            <a class="<?= active_nav('contact') ?>" href="<?= url('contact.php') ?>">Contact</a>
            <a href="<?= url('login.php') ?>">Login</a>
            <button type="button" class="btn btn-outline-success" data-install-app hidden>Install app</button>
            <a class="btn btn-official" href="<?= url('register.php') ?>">Apply Now</a>
        </nav>
        <button class="btn btn-ghost-menu d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-label="Open menu"><i class="bi bi-list"></i></button>
    </div>
</header>
<div class="offcanvas offcanvas-end" id="mobileMenu">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title"><?= e(site_short()) ?></h5>
        <button class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body d-grid gap-2">
        <a href="<?= url() ?>">Home</a>
        <a href="<?= url('about.php') ?>">About</a>
        <a href="<?= url('eligibility.php') ?>">Eligibility</a>
        <a href="<?= url('how-to-apply.php') ?>">How to Apply</a>
        <a href="<?= url('faq.php') ?>">FAQ</a>
        <a href="<?= url('contact.php') ?>">Contact</a>
        <a href="<?= url('login.php') ?>">Login</a>
        <button type="button" class="btn btn-outline-success" data-install-app hidden>Install app</button>
        <a class="btn btn-official" href="<?= url('register.php') ?>">Apply Now</a>
    </div>
</div>
<main>
<?php if ($banner = trim((string) setting('maintenance_message', ''))): ?><div class="container mt-3"><div class="alert alert-warning"><?= e($banner) ?></div></div><?php endif; ?>
<?php if ($msg = flash('success')): ?><div class="container mt-3"><div class="alert alert-success alert-flash"><?= e($msg) ?></div></div><?php endif; ?>
<?php if ($msg = flash('error')): ?><div class="container mt-3"><div class="alert alert-danger alert-flash"><?= e($msg) ?></div></div><?php endif; ?>
<?php if ($msg = flash('info')): ?><div class="container mt-3"><div class="alert alert-info alert-flash"><?= e($msg) ?></div></div><?php endif; ?>
