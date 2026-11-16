<?php layout_start('user', ['title' => $title, 'user' => $user]); ?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <div class="text-muted">Welcome</div>
        <h1 class="h3 mb-0"><?= e($user['first_name'] ?: explode('@', $user['email'])[0]) ?></h1>
    </div>
    <div><?= status_badge($app['status'] ?? 'draft') ?></div>
</div>

<?php if (($app['status'] ?? 'draft') !== 'approved'): ?>
<div class="alert alert-warning">
    <strong>Application status:</strong> <?= e(ucwords(str_replace('_', ' ', $app['status'] ?? 'draft'))) ?>
    <?php if (($app['status'] ?? '') === 'draft'): ?>
        &mdash; <a href="<?= url('user/apply.php') ?>">Continue your application</a>
    <?php elseif (($app['status'] ?? '') === 'additional_info'): ?>
        &mdash; Additional information is required. <a href="<?= url('user/apply.php') ?>">Update application</a>
        <?php if (!empty($app['additional_info_request'])): ?><div class="mt-2"><?= e($app['additional_info_request']) ?></div><?php endif; ?>
    <?php else: ?>
        &mdash; Your application is being reviewed. You will be notified by email when there is an update.
    <?php endif; ?>
</div>
<?php else: ?>
<div class="alert alert-success">
    Your application has been approved. Approved grant: <strong><?= money_ngn($grant['amount'] ?? 0) ?></strong>.
    Sign in to Wallet to view credited funds. A physical card is optional.
    <div class="small mt-2"><?= e((string) setting('grant_disbursement_note', '')) ?></div>
</div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="balance-card mb-4">
            <div class="d-flex justify-content-between">
                <span>Available balance</span>
                <button class="btn btn-sm btn-outline-light" type="button" data-balance-toggle>Show / hide</button>
            </div>
            <div class="display-6 fw-bold mt-2 text-break" data-balance-value="<?= e(number_format((float) ($wallet['available_balance'] ?? 0), 2)) ?>">****</div>
            <div class="small mt-2">Reference <?= e($app['reference'] ?? '—') ?></div>
        </div>
        <div class="card-soft">
            <h2 class="h6">Recent transactions</h2>
            <?php if (!$transactions): ?>
                <p class="text-muted mb-0">No wallet transactions yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>Date</th><th>Description</th><th>Amount</th></tr></thead>
                        <tbody>
                        <?php foreach ($transactions as $tx): ?>
                            <tr>
                                <td><?= e(date('d M Y', strtotime($tx['created_at']))) ?></td>
                                <td><?= e($tx['description']) ?></td>
                                <td class="<?= $tx['direction'] === 'credit' ? 'text-success' : 'text-danger' ?>">
                                    <?= $tx['direction'] === 'credit' ? '+' : '-' ?><?= money_ngn($tx['amount']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card-soft mb-3">
            <h2 class="h6">Application</h2>
            <p class="mb-1">Reference: <strong><?= e($app['reference'] ?? '—') ?></strong></p>
            <p class="mb-1">Status: <?= status_badge($app['status'] ?? 'draft') ?></p>
            <p class="mb-0">Grant: <?= isset($grant['amount']) ? money_ngn($grant['amount']) : 'Not approved' ?></p>
        </div>
        <div class="card-soft mb-3">
            <h2 class="h6">Card request</h2>
            <p class="mb-0"><?= status_badge($cardRequest['status'] ?? 'not_requested') ?></p>
            <a class="btn btn-official btn-sm mt-3" href="<?= url('user/card.php') ?>">Open Card</a>
        </div>
        <div class="card-soft">
            <h2 class="h6">Notifications</h2>
            <?php if (!$notifications): ?>
                <p class="text-muted mb-0">No notifications.</p>
            <?php else: foreach ($notifications as $n): ?>
                <div class="border-bottom py-2">
                    <strong><?= e($n['title']) ?></strong>
                    <div class="small text-muted"><?= e($n['body']) ?></div>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</div>
<?php layout_end('user'); ?>
