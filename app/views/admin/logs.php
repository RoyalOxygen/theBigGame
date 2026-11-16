<?php layout_start('admin', ['title' => $title, 'admin' => $admin]); ?>
<div class="card-soft table-responsive">
<table class="table">
    <thead><tr><th>Time</th><th>Admin</th><th>Action</th><th>Entity</th><th>IP</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $row): ?>
        <tr>
            <td><?= e($row['created_at']) ?></td>
            <td><?= e($row['first_name'] . ' ' . $row['last_name']) ?></td>
            <td><?= e($row['action']) ?></td>
            <td><?= e($row['entity_type']) ?> <?= e((string)$row['entity_id']) ?></td>
            <td><?= e($row['ip_address']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php layout_end('admin'); ?>
