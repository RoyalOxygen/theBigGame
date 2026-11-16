<?php layout_start('admin', ['title' => $title, 'admin' => $admin]); ?>
<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div class="min-w-0">
        <h1 class="h4 mb-0 text-break"><?= e($app['reference']) ?></h1>
        <div class="small"><?= status_badge($app['status']) ?> <span class="d-none d-sm-inline">&middot;</span> <span class="d-block d-sm-inline text-break"><?= e($app['email']) ?></span></div>
    </div>
    <a class="btn btn-outline-success btn-sm" href="<?= url('admin/applicants.php') ?>">Back to list</a>
</div>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-soft mb-3">
            <h2 class="h6">Personal information</h2>
            <div class="row">
                <div class="col-md-8">
                    <p><?= e(full_name($app)) ?><br><?= e($app['phone']) ?><br><?= e($app['address']) ?></p>
                    <p>DoB: <?= e($app['date_of_birth']) ?> &middot; <?= e($app['gender']) ?><br>
                    ID: <?= e($app['id_type']) ?> <?= e($app['id_number']) ?><br>
                    <?= e($app['state_name']) ?> / <?= e($app['lga_name']) ?></p>
                </div>
                <div class="col-md-4">
                    <?php if (!empty($app['photo_path'])): ?>
                        <img src="<?= upload_url($app['photo_path']) ?>" alt="" class="img-fluid rounded">
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="card-soft mb-3">
            <h2 class="h6">Occupation</h2>
            <p><?= e($app['employment_status']) ?> &middot; <?= e($app['occupation']) ?><br>
            Income: <?= e($app['monthly_income_range']) ?><br>
            <?= e($app['financial_circumstances']) ?></p>
        </div>
        <div class="card-soft">
            <h2 class="h6">Bank</h2>
            <p><?= e($app['bank_name']) ?> &middot; <?= e(mask_account($app['account_number'])) ?><br><?= e($app['account_name']) ?></p>
        </div>
    </div>
    <div class="col-lg-4">
        <?php if (admin_can('grants.approve') && in_array($app['status'], ['submitted','under_review','additional_info'], true)): ?>
        <form method="post" action="<?= url('admin/actions.php') ?>" class="card-soft mb-3">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="approve">
            <input type="hidden" name="id" value="<?= (int)$app['id'] ?>">
            <h2 class="h6">Approve grant</h2>
            <div class="mb-2">
                <label class="form-label">Funding pool</label>
                <select class="form-select" name="funding_id" required>
                    <?php foreach ($funding as $f): ?>
                        <option value="<?= (int)$f['id'] ?>"><?= e($f['source_name']) ?> (<?= money_ngn($f['available_amount']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-2">
                <label class="form-label">Amount (NGN)</label>
                <input class="form-control" type="number" name="amount" min="<?= e(setting('min_grant_amount', 25000)) ?>" max="<?= e(setting('max_grant_amount', 250000)) ?>" required>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="eligibility" required>
                <label class="form-check-label">Eligibility confirmed</label>
            </div>
            <textarea class="form-control mb-2" name="notes" placeholder="Approval notes"></textarea>
            <button class="btn btn-official w-100">Approve and credit wallet</button>
        </form>
        <form method="post" action="<?= url('admin/actions.php') ?>" class="card-soft mb-3">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="reject">
            <input type="hidden" name="id" value="<?= (int)$app['id'] ?>">
            <textarea class="form-control mb-2" name="reason" required placeholder="Rejection reason"></textarea>
            <button class="btn btn-outline-danger w-100">Reject</button>
        </form>
        <form method="post" action="<?= url('admin/actions.php') ?>" class="card-soft mb-3">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="info">
            <input type="hidden" name="id" value="<?= (int)$app['id'] ?>">
            <textarea class="form-control mb-2" name="message" required placeholder="Additional information required"></textarea>
            <button class="btn btn-outline-warning w-100">Request information</button>
        </form>
        <?php endif; ?>
        <form method="post" action="<?= url('admin/actions.php') ?>" class="card-soft">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="note">
            <input type="hidden" name="id" value="<?= (int)$app['id'] ?>">
            <textarea class="form-control mb-2" name="note" required placeholder="Internal review note"></textarea>
            <button class="btn btn-outline-secondary w-100">Add note</button>
        </form>
        <div class="mt-3">
            <?php foreach ($notes as $n): ?>
                <div class="small border-bottom py-2">
                    <strong><?= e($n['first_name'] . ' ' . $n['last_name']) ?></strong>
                    <div><?= e($n['note']) ?></div>
                    <div class="text-muted"><?= e($n['created_at']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php layout_end('admin'); ?>
