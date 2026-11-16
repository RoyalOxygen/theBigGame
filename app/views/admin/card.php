<?php layout_start('admin', ['title' => $title, 'admin' => $admin]); ?>
<h1 class="h4 text-break"><?= e($row['reference']) ?> <?= status_badge($row['status']) ?></h1>
<p class="text-break"><?= e($row['full_name']) ?> &middot; <?= e($row['phone']) ?><br>
<?= e($row['delivery_address']) ?><br>
<?= e($row['state_name']) ?> / <?= e($row['lga_name']) ?></p>
<p>Payment: <?= e($row['pay_status'] ?? 'n/a') ?> &middot; <?= money_ngn($row['total_fee']) ?></p>
<form method="post" action="<?= url('admin/actions.php') ?>" class="card-soft">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="card_status">
    <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
                <?php foreach (['processing','produced','dispatched','ready_for_collection','delivered','cancelled'] as $s): ?>
                    <option value="<?= $s ?>" <?= $row['status'] === $s ? 'selected' : '' ?>><?= e(str_replace('_',' ',$s)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Production reference</label>
            <input class="form-control" name="production_reference" value="<?= e($row['production_reference'] ?? '') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Collection centre</label>
            <select class="form-select" name="collection_centre_id">
                <option value="0">Unchanged / none</option>
                <?php foreach ($centres as $c): ?>
                    <option value="<?= (int)$c['id'] ?>" <?= (int)$row['collection_centre_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">Dispatch information</label>
            <input class="form-control" name="dispatch_info" value="<?= e($row['dispatch_info'] ?? '') ?>">
        </div>
    </div>
    <button class="btn btn-official mt-3 portal-primary">Update card request</button>
</form>
<?php layout_end('admin'); ?>
