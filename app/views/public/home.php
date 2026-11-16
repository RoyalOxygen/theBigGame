<?php layout_start('public', ['title' => $title]); ?>
<section class="hero">
    <div class="container">
        <div class="disclosure mb-4">
            <?= e((string) setting('operator_disclosure', config('operator.disclosure'))) ?>
        </div>
        <p class="section-kicker text-warning"><?= e((string) setting('hero_kicker', 'Relief Programme Portal')) ?></p>
        <h1><?= e((string) setting('hero_title', site_name())) ?></h1>
        <p class="lead"><?= e((string) setting('hero_lead', 'A transparent application portal for eligible residents seeking one-off household relief support.')) ?></p>
        <div class="d-flex gap-3 flex-wrap mt-4">
            <a class="btn btn-gold btn-lg" href="<?= url('register.php') ?>">Start Application</a>
            <a class="btn btn-outline-light btn-lg" href="<?= url('eligibility.php') ?>">Check Eligibility</a>
            <button type="button" class="btn btn-outline-warning btn-lg" data-install-app>Install app</button>
        </div>
    </div>
</section>

<section class="section bg-white border-bottom">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-7">
                <div class="section-kicker">On your device</div>
                <h2 class="section-title">Install NARFCARD in one click</h2>
                <p>Add the programme portal to your home screen. Installation is handled by your browser on this device.</p>
                <ul>
                    <li>Works from Chrome, Edge, Safari and supported Android browsers</li>
                    <li>Opens as a standalone app after install</li>
                    <li>Uses only files already loaded in the browser</li>
                </ul>
                <button type="button" class="btn btn-official" data-install-app>Click to install</button>
                <p class="small text-muted mt-2 mb-0" id="iosInstallHint" hidden>On iPhone, tap Share, then Add to Home Screen.</p>
                <p class="small text-success mt-2 mb-0" id="installInstalled" hidden>NARFCARD is installed on this device.</p>
            </div>
            <div class="col-lg-5">
                <div class="card-soft">
                    <h3 class="h6">How to install</h3>
                    <ol class="mb-0">
                        <li>Tap <strong>Click to install</strong>.</li>
                        <li>Confirm the browser prompt, or follow the on-screen steps for iPhone.</li>
                        <li>Open NARFCARD from your home screen or app list.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card-soft">
                    <div class="section-kicker">Objective</div>
                    <h3 class="section-title h5">Household relief support</h3>
                    <p><?= e((string) setting('objective_text', 'The programme reviews applications from eligible households and, subject to available funding, may approve a one-off relief amount credited to an applicant wallet.')) ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-soft">
                    <div class="section-kicker">Transparency</div>
                    <h3 class="section-title h5">Clear process and fees</h3>
                    <p><?= e((string) setting('transparency_text', 'Card issuance is optional. Any processing or delivery charge is disclosed before payment and is not a condition for viewing an approved grant.')) ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-soft">
                    <div class="section-kicker">Operator</div>
                    <h3 class="section-title h5"><?= e((string) setting('programme_operator', config('operator.name'))) ?></h3>
                    <p>This portal is operated and subject to the NARFCARD Programme.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section bg-white">
    <div class="container">
        <div class="section-kicker">How it works</div>
        <h2 class="section-title">Application process</h2>
        <div class="row g-4 mt-2">
            <?php
            $steps = [
                ['Create an account', 'Register with your email and verify the address before you can apply.'],
                ['Complete the form', 'Provide personal, occupation and bank details in four guided steps.'],
                ['Review', 'The Secretariat reviews eligibility against published criteria and available funds.'],
                ['Decision', 'You are notified by email. Approved amounts are credited only when funds are allocated.'],
                ['Request Card', 'Request for NARFCARD once approved funds are credited to your wallet, choose prefered delivery location.'],
                ['Withdraw funds', 'You are notified by email when your card is ready for collection.'],
            ];
            foreach ($steps as $i => $step): ?>
            <div class="col-md-3">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="step-num"><?= $i + 1 ?></div>
                    <h3 class="h6 mb-0"><?= e($step[0]) ?></h3>
                </div>
                <p class="small text-muted"><?= e($step[1]) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <a class="btn btn-official mt-3" href="<?= url('how-to-apply.php') ?>">Read the full guide</a>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6">
                <div class="section-kicker">Eligibility</div>
                <h2 class="section-title">Who may apply</h2>
                <ul>
                    <li>Nigerian residents aged <?= e((string) setting('min_age', '18')) ?> years and above</li>
                    <li>Applicants able to provide a valid means of identification</li>
                    <li>Households able to demonstrate need within published income bands</li>
                    <li>One application per email address and identification number</li>
                </ul>
                <p>Eligibility does not guarantee approval. Awards depend on review and available programme funding.</p>
                <a href="<?= url('eligibility.php') ?>">Full eligibility requirements</a>
            </div>
            <div class="col-lg-6">
                <div class="card-soft">
                    <h3 class="h5">Frequently asked</h3>
                    <p class="mb-1"><strong>What do i need to apply</strong></p>
                    <p>You need a valid email address, National Identity Number, Valid phone number and a verified Bank Account</p>
                    <p class="mb-1"><strong>Do I pay to apply?</strong></p>
                    <p>There is no application fee. Optional card production and delivery charges are shown before payment.</p>
                    <a href="<?= url('faq.php') ?>">More questions</a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php layout_end('public'); ?>
