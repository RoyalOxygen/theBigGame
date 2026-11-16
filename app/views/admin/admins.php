<?php layout_start('admin', ['title' => $title, 'admin' => $admin]); ?>
<form method="post" class="card-soft mb-4">
    <?= csrf_field() ?>
    <h2 class="h6">Add administrator</h2>
    <div class="row g-2">
        <div class="col-md-3"><input class="form-control" name="first_name" placeholder="First name" required></div>
        <div class="col-md-3"><input class="form-control" name="last_name" placeholder="Last name" required></div>
        <div class="col-md-3"><input class="form-control" type="email" name="email" placeholder="Email" required></div>
        <div class="col-md-3">
            <select class="form-select" name="role_id" required>
                <?php foreach ($roles as $r): ?>
                    <option value="<?= (int)$r['id'] ?>"><?= e($r['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-3"><input class="form-control" type="password" name="password" placeholder="Temporary password" required></div>
        <div class="col-12 col-md-3"><button class="btn btn-official w-100">Create</button></div>
    </div>
</form>
<div class="card-soft table-responsive">
<table class="table">
    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Active</th></tr></thead>
    <tbody>
    <?php foreach ($admins as $a): ?>
        <tr>
            <td><?= e($a['first_name'] . ' ' . $a['last_name']) ?></td>
            <td><?= e($a['email']) ?></td>
            <td><?= e($a['role_name']) ?></td>
            <td><?= $a['is_active'] ? 'Yes' : 'No' ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php layout_end('admin'); ?>
