<?php layout_start('user', ['title' => $title, 'user' => $user]); ?>
<h1 class="h4 mb-4">Profile</h1>
<div class="row g-4">
    <div class="col-lg-7">
        <form method="post" enctype="multipart/form-data" class="card-soft">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="profile">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input class="form-control" name="phone" value="<?= e($profile['phone'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Alternative phone</label>
                    <input class="form-control" name="alt_phone" value="<?= e($profile['alt_phone'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Residential address</label>
                    <textarea class="form-control" name="address"><?= e($profile['address'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Profile photograph</label>
                    <input class="form-control" type="file" name="photo" accept="image/jpeg,image/png,image/webp">
                </div>
            </div>
            <button class="btn btn-official mt-3 portal-primary" type="submit">Save profile</button>
        </form>
        <form method="post" class="card-soft mt-3">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="password">
            <h2 class="h6">Change password</h2>
            <div class="mb-2"><input class="form-control" type="password" name="current_password" placeholder="Current password" required></div>
            <div class="mb-2"><input class="form-control" type="password" name="new_password" placeholder="New password" required></div>
            <div class="mb-2"><input class="form-control" type="password" name="confirm_password" placeholder="Confirm new password" required></div>
            <button class="btn btn-outline-success portal-primary" type="submit">Update password</button>
        </form>
    </div>
    <div class="col-lg-5">
        <div class="card-soft mb-3">
            <h2 class="h6">Personal information</h2>
            <p class="mb-1"><?= e(full_name($profile) ?: 'Not completed') ?></p>
            <p class="mb-1"><?= e($user['email']) ?></p>
            <p class="mb-0">ID: <?= e($profile['id_type'] ?? '—') ?></p>
        </div>
        <div class="card-soft">
            <h2 class="h6">Bank account (masked)</h2>
            <p class="mb-0"><?= e(mask_account($app['account_number'] ?? '')) ?></p>
            <p class="small text-muted">Sensitive bank changes require contacting the Secretariat.</p>
        </div>
    </div>
</div>
<?php layout_end('user'); ?>
