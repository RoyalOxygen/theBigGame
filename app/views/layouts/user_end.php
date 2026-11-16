        </div>
    </div>
</div>
<nav class="mobile-nav" aria-label="Applicant">
    <a class="<?= active_nav('dashboard') ?>" href="<?= url('user/dashboard.php') ?>"><i class="bi bi-grid"></i><span>Home</span></a>
    <a class="<?= active_nav('apply') ?>" href="<?= url('user/apply.php') ?>"><i class="bi bi-file-earmark-text"></i><span>Apply</span></a>
    <a class="<?= active_nav('wallet') ?>" href="<?= url('user/wallet.php') ?>"><i class="bi bi-wallet2"></i><span>Wallet</span></a>
    <a class="<?= active_nav('card') ?>" href="<?= url('user/card.php') ?>"><i class="bi bi-credit-card"></i><span>Card</span></a>
    <a class="<?= active_nav('profile') ?>" href="<?= url('user/profile.php') ?>"><i class="bi bi-person"></i><span>Profile</span></a>
</nav>
<div class="offcanvas offcanvas-start portal-drawer" tabindex="-1" id="userMenu">
    <div class="offcanvas-header">
        <div class="d-flex align-items-center gap-2 text-white">
            <img src="<?= e(brand_url('logo_path', 'images/logo.svg')) ?>" alt="" width="36" height="36">
            <div>
                <strong><?= e(site_short()) ?></strong>
                <div class="small opacity-75">Applicant Portal</div>
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <nav class="sidebar-nav">
            <a class="<?= active_nav('dashboard') ?>" href="<?= url('user/dashboard.php') ?>"><i class="bi bi-grid"></i> Dashboard</a>
            <a class="<?= active_nav('apply') ?>" href="<?= url('user/apply.php') ?>"><i class="bi bi-file-earmark-text"></i> Application</a>
            <a class="<?= active_nav('wallet') ?>" href="<?= url('user/wallet.php') ?>"><i class="bi bi-wallet2"></i> Wallet</a>
            <a class="<?= active_nav('card') ?>" href="<?= url('user/card.php') ?>"><i class="bi bi-credit-card"></i> Card</a>
            <a class="<?= active_nav('profile') ?>" href="<?= url('user/profile.php') ?>"><i class="bi bi-person"></i> Profile</a>
            <button type="button" class="btn btn-outline-light text-start sidebar-install" data-install-app hidden><i class="bi bi-download"></i> Install app</button>
            <a href="<?= url('logout.php') ?>"><i class="bi bi-box-arrow-right"></i> Sign out</a>
        </nav>
    </div>
</div>
<div class="modal fade" id="iosInstallModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5">Install <?= e(site_short()) ?></h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Installation runs in your browser. On iPhone tap Share, then Add to Home Screen. On Android or desktop Chrome tap Install.</p>
            </div>
        </div>
    </div>
</div>
<script>window.BASE_URL = <?= json_encode(rtrim(BASE_URL, '/')) ?>;</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/app.js') ?>"></script>
<script src="<?= asset('js/install.js') ?>"></script>
</body>
</html>
