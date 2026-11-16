<?php layout_start('user', ['title' => $title, 'user' => $user]); ?>
<h1 class="h4 mb-4">Wallet</h1>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="card-soft stat"><div class="small text-muted">Available</div><strong><?= money_ngn($wallet['available_balance'] ?? 0) ?></strong></div></div>
    <div class="col-6 col-md-3"><div class="card-soft stat"><div class="small text-muted">Total approved grant</div><strong><?= money_ngn($approved) ?></strong></div></div>
    <div class="col-6 col-md-3"><div class="card-soft stat"><div class="small text-muted">Total credited</div><strong><?= money_ngn($credited) ?></strong></div></div>
    <div class="col-6 col-md-3"><div class="card-soft stat"><div class="small text-muted">Total debited</div><strong><?= money_ngn($debited) ?></strong></div></div>
</div>
<div class="card-soft">
    <h2 class="h6">Transaction history</h2>
    <div class="table-responsive">
        <table class="table">
            <thead><tr><th>Date</th><th>Reference</th><th>Category</th><th>Description</th><th>Status</th><th>Amount</th></tr></thead>
            <tbody>
            <?php if (!$transactions): ?>
                <tr><td colspan="6" class="text-muted">No transactions.</td></tr>
            <?php else: foreach ($transactions as $tx): ?>
                <tr>
                    <td><?= e(date('d M Y H:i', strtotime($tx['created_at']))) ?></td>
                    <td><?= e($tx['reference']) ?></td>
                    <td><?= e(str_replace('_', ' ', $tx['category'])) ?></td>
                    <td><?= e($tx['description']) ?></td>
                    <td><?= status_badge($tx['status']) ?></td>
                    <td class="<?= $tx['direction'] === 'credit' ? 'text-success' : 'text-danger' ?>">
                        <?= $tx['direction'] === 'credit' ? '+' : '-' ?><?= money_ngn($tx['amount']) ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <p class="small text-muted mb-0">Balances are calculated from the wallet ledger. Applicants cannot edit these records.</p>
</div>
<?php layout_end('user'); ?>
