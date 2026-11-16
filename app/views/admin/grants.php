<?php layout_start('admin', ['title' => $title, 'admin' => $admin]); ?>
<div class="card-soft mb-4">
    <h2 class="h6">Funding pools</h2>
    <div class="table-responsive">
    <table class="table">
        <thead><tr><th>Source</th><th>Total</th><th>Allocated</th><th>Available</th></tr></thead>
        <tbody>
        <?php foreach ($funding as $f): ?>
            <tr>
                <td><?= e($f['source_name']) ?></td>
                <td><?= money_ngn($f['total_amount']) ?></td>
                <td><?= money_ngn($f['allocated_amount']) ?></td>
                <td><?= money_ngn($f['available_amount']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<div class="card-soft">
    <h2 class="h6">Approvals</h2>
    <div class="table-responsive">
    <table class="table">
        <thead><tr><th>Reference</th><th>Applicant</th><th>Amount</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($approvals as $a): ?>
            <tr>
                <td><?= e($a['approval_reference']) ?></td>
                <td><?= e($a['email']) ?></td>
                <td><?= money_ngn($a['amount']) ?></td>
                <td><?= e($a['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php layout_end('admin'); ?>
