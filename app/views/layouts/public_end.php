</main>
<footer class="site-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <h5>Programme Operator</h5>
                <p class="mb-1"><?= e((string) setting('programme_operator', config('operator.name'))) ?></p>
                <p class="mb-1 small"><?= e((string) setting('operator_legal_name', '')) ?></p>
                <p class="small"><?= e((string) setting('operator_disclosure', config('operator.disclosure'))) ?></p>
            </div>
            <div class="col-md-4">
                <h5>Information</h5>
                <ul class="list-unstyled">
                    <li><a href="<?= url('privacy.php') ?>">Privacy Policy</a></li>
                    <li><a href="<?= url('terms.php') ?>">Terms and Conditions</a></li>
                    <li><a href="<?= url('eligibility.php') ?>">Eligibility Requirements</a></li>
                    <li><a href="<?= url('contact.php') ?>">Contact Us</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5>Contact</h5>
                <p class="mb-1"><?= e((string) setting('contact_address', config('operator.address'))) ?></p>
                <p class="mb-1"><?= e((string) setting('contact_email', config('operator.email'))) ?></p>
                <p class="mb-1"><?= e((string) setting('contact_phone', config('operator.phone'))) ?></p>
                <?php $alt = trim((string) setting('contact_phone_alt', '')); if ($alt !== ''): ?>
                <p><?= e($alt) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <hr class="border-secondary mt-4">
        <p class="small mb-0">&copy; <?= date('Y') ?> <?= e((string) setting('footer_text', 'NARFCARD Programme. All rights reserved.')) ?></p>
    </div>
</footer>
<div id="installBanner" class="install-banner" hidden>
    <div class="container d-flex align-items-center justify-content-between gap-3 flex-wrap">
        <div>
            <strong>Install <?= e(site_short()) ?></strong>
            <div class="small">Add this portal to your home screen. Installation runs in your browser.</div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-gold btn-sm" data-install-app>Install</button>
            <button type="button" class="btn btn-outline-light btn-sm" data-install-dismiss>Not now</button>
        </div>
    </div>
</div>
<div class="modal fade" id="iosInstallModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5">Install <?= e(site_short()) ?></h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">This installation happens on your device.</p>
                <ol class="mb-0">
                    <li>Open this site in Safari (iPhone/iPad) or Chrome (Android/desktop).</li>
                    <li>On iPhone or iPad, tap the Share button, then <strong>Add to Home Screen</strong>.</li>
                    <li>On Android or desktop Chrome, tap <strong>Install</strong> or use the install icon in the address bar.</li>
                </ol>
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
