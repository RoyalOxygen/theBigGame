<?php
$admin = $admin ?? auth_admin();
$title = $title ?? 'Administration';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= e($title) ?> | <?= e(site_short()) ?> Admin</title>
    <meta name="theme-color" content="<?= e(theme_color()) ?>">
    <link rel="manifest" href="<?= url('manifest.php') ?>">
    <link rel="icon" href="<?= e(brand_url('favicon_path', 'images/favicon.svg')) ?>">
    <?= theme_style_tag() ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
</head>
<body class="portal-body">
<div class="app-shell">
    <aside class="sidebar" id="adminSidebar">
        <div class="mb-4 text-white">
            <strong><?= e(site_short()) ?> Admin</strong><br>
            <small><?= e($admin['role_name'] ?? '') ?></small>
        </div>
        <nav class="sidebar-nav">
            <a class="<?= active_nav('admin/index') ?>" href="<?= url('admin/index.php') ?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
            <a class="<?= active_nav('applicants') || active_nav('application.php') ?>" href="<?= url('admin/applicants.php') ?>"><i class="bi bi-people"></i> Applicants</a>
            <?php if (admin_can('grants.approve') || admin_can('funding.manage')): ?>
            <a class="<?= active_nav('grants') || active_nav('funding') ?>" href="<?= url('admin/grants.php') ?>"><i class="bi bi-cash-stack"></i> Grants</a>
            <?php endif; ?>
            <?php if (admin_can('cards.manage')): ?>
            <a class="<?= active_nav('cards') || active_nav('card.php') ?>" href="<?= url('admin/cards.php') ?>"><i class="bi bi-credit-card-2-front"></i> Cards</a>
            <?php endif; ?>
            <?php if (admin_can('*')): ?>
            <a class="<?= active_nav('settings') ?>" href="<?= url('admin/settings.php') ?>"><i class="bi bi-gear"></i> Settings</a>
            <a class="<?= active_nav('admins') ?>" href="<?= url('admin/admins.php') ?>"><i class="bi bi-shield-lock"></i> Administrators</a>
            <a class="<?= active_nav('logs') ?>" href="<?= url('admin/logs.php') ?>"><i class="bi bi-journal-text"></i> Audit log</a>
            <?php endif; ?>
            <a href="<?= url('admin/logout.php') ?>"><i class="bi bi-box-arrow-right"></i> Sign out</a>
        </nav>
    </aside>
    <div class="main-col">
        <div class="topbar">
            <div class="topbar-lead">
                <button class="btn btn-ghost-menu d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminMenu" aria-label="Open menu">
                    <i class="bi bi-list"></i>
                </button>
                <strong class="topbar-title"><?= e($title) ?></strong>
            </div>
            <span class="topbar-name"><?= e(($admin['first_name'] ?? '') . ' ' . ($admin['last_name'] ?? '')) ?></span>
        </div>
        <div class="content">
<?php if ($msg = flash('success')): ?><div class="alert alert-success alert-flash"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('error')): ?><div class="alert alert-danger alert-flash"><?= e($msg) ?></div><?php endif; ?>
<?php foreach (errors() as $err): ?><div class="alert alert-danger alert-flash"><?= e($err) ?></div><?php endforeach; ?>
