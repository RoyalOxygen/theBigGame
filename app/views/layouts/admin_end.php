        </div>
    </div>
</div>
<nav class="mobile-nav" aria-label="Administration">
    <a class="<?= active_nav('admin/index') ?>" href="<?= url('admin/index.php') ?>"><i class="bi bi-speedometer2"></i><span>Home</span></a>
    <a class="<?= active_nav('applicants') || active_nav('application.php') ?>" href="<?= url('admin/applicants.php') ?>"><i class="bi bi-people"></i><span>Applicants</span></a>
    <?php if (admin_can('grants.approve') || admin_can('funding.manage')): ?>
    <a class="<?= active_nav('grants') || active_nav('funding') ?>" href="<?= url('admin/grants.php') ?>"><i class="bi bi-cash-stack"></i><span>Grants</span></a>
    <?php endif; ?>
    <?php if (admin_can('cards.manage')): ?>
    <a class="<?= active_nav('cards') || active_nav('card.php') ?>" href="<?= url('admin/cards.php') ?>"><i class="bi bi-credit-card-2-front"></i><span>Cards</span></a>
    <?php endif; ?>
    <?php if (admin_can('*')): ?>
    <a class="<?= active_nav('settings') ?>" href="<?= url('admin/settings.php') ?>"><i class="bi bi-gear"></i><span>Settings</span></a>
    <?php else: ?>
    <a href="<?= url('admin/logout.php') ?>"><i class="bi bi-box-arrow-right"></i><span>Sign out</span></a>
    <?php endif; ?>
</nav>
<div class="offcanvas offcanvas-start portal-drawer" tabindex="-1" id="adminMenu">
    <div class="offcanvas-header">
        <div class="text-white">
            <strong><?= e(site_short()) ?> Admin</strong>
            <div class="small opacity-75"><?= e($admin['role_name'] ?? '') ?></div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
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
    </div>
</div>
<script>window.BASE_URL = <?= json_encode(rtrim(BASE_URL, '/')) ?>;</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="<?= asset('js/app.js') ?>"></script>
<?php if (!empty($chartsScript)): ?><script><?= $chartsScript ?></script><?php endif; ?>
</body>
</html>
