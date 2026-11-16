<?php layout_start('admin', ['title' => $title, 'admin' => $admin]); ?>
<p class="text-muted small mb-3"><?= (int) $totalUsers ?> registered applicant<?= (int) $totalUsers === 1 ? '' : 's' ?> &middot; <?= (int) $unverifiedUsers ?> email not verified. Accounts appear here after registration, including those who have not verified email or started an application.</p>
<form class="row g-2 mb-3" method="get">
    <div class="col-12 col-md-3"><input class="form-control" name="q" placeholder="Name, email or reference" value="<?= e($q) ?>"></div>
    <div class="col-6 col-md-2">
        <select class="form-select" name="status">
            <option value="">All applications</option>
            <option value="not_started" <?= $status === 'not_started' ? 'selected' : '' ?>>No application yet</option>
            <?php foreach (['draft','submitted','under_review','additional_info','approved','rejected'] as $s): ?>
                <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(str_replace('_',' ',$s)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-6 col-md-2">
        <select class="form-select" name="verified">
            <option value="">All emails</option>
            <option value="yes" <?= $verified === 'yes' ? 'selected' : '' ?>>Verified</option>
            <option value="no" <?= $verified === 'no' ? 'selected' : '' ?>>Not verified</option>
        </select>
    </div>
    <div class="col-6 col-md-2">
        <select class="form-select" name="state_id" data-lga-target="filter_lga">
            <option value="">All states</option>
            <?php foreach ($states as $st): ?>
                <option value="<?= (int)$st['id'] ?>" <?= $stateId === (int)$st['id'] ? 'selected' : '' ?>><?= e($st['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-6 col-md-2">
        <select class="form-select" name="lga_id" id="filter_lga">
            <option value="">All LGAs</option>
            <?php foreach ($lgas as $lga): ?>
                <option value="<?= (int)$lga['id'] ?>" <?= $lgaId === (int)$lga['id'] ? 'selected' : '' ?>><?= e($lga['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-12 col-md-1"><button class="btn btn-official w-100">Filter</button></div>
</form>
<div class="card-soft table-responsive">
<table class="table align-middle">
    <thead><tr><th>Registered</th><th>Applicant</th><th>Email</th><th>Verified</th><th>Reference</th><th>Application</th><th></th></tr></thead>
    <tbody>
    <?php if (!$rows): ?>
        <tr><td colspan="7" class="text-muted">No registered applicants match these filters.</td></tr>
    <?php else: foreach ($rows as $row): ?>
        <tr>
            <td class="text-nowrap"><?= e(date('d M Y', strtotime((string) $row['created_at']))) ?></td>
            <td><?= e(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?: '—') ?></td>
            <td class="text-break"><?= e($row['email']) ?></td>
            <td><?= !empty($row['email_verified_at']) ? status_badge('verified') : status_badge('unverified') ?></td>
            <td><?= e($row['reference'] ?? '—') ?></td>
            <td><?= !empty($row['application_id']) ? status_badge((string) $row['status']) : status_badge('not_started') ?></td>
            <td>
                <?php if (!empty($row['application_id'])): ?>
                    <a href="<?= url('admin/application.php?id=' . (int) $row['application_id']) ?>">Review</a>
                <?php else: ?>
                    <span class="text-muted">No application</span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>
</div>
<?php layout_end('admin'); ?>
