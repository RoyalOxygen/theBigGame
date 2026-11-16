<?php layout_start('admin', ['title' => $title, 'admin' => $admin]); ?>
<div class="card-soft table-responsive">
<table class="table">
    <thead><tr><th>Reference</th><th>Applicant</th><th>State</th><th>Status</th><th>Fee</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $row): ?>
        <tr>
            <td><?= e($row['reference']) ?></td>
            <td><?= e($row['full_name']) ?></td>
            <td><?= e($row['state_name']) ?></td>
            <td><?= status_badge($row['status']) ?></td>
            <td><?= money_ngn($row['total_fee']) ?></td>
            <td><a href="<?= url('admin/card.php?id=' . $row['id']) ?>">Open</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php layout_end('admin'); ?>
