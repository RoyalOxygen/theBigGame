<?php layout_start('user', ['title' => $title, 'user' => $user]); ?>
<h1 class="h4 mb-3">Relief fund card</h1>
<?php if (!$approved): ?>
<div class="alert alert-info">Card requests become available after your application is approved. Approval does not require you to request a physical card.</div>
<?php endif; ?>

<?php if ($issuedCard): ?>
<div class="physical-card mb-4">
    <div class="d-flex justify-content-between">
        <strong>NARFCARD</strong>
        <span>RELIEF</span>
    </div>
    <div class="chip my-4"></div>
    <div class="small">Cardholder</div>
    <div class="h5"><?= e($issuedCard['display_name']) ?></div>
    <div class="small mt-3"><?= e($issuedCard['masked_pan'] ?: 'Issued without a payment PAN') ?></div>
</div>
<p class="small text-muted">This preview is not a bank payment card number. Actual payment card issuance, if any, is handled by an authorised issuer.</p>
<?php endif; ?>

<?php if ($request && $request['status'] !== 'cancelled'): ?>
<div class="card-soft mb-3">
    <p>Request <?= e($request['reference']) ?> &middot; <?= status_badge($request['status']) ?></p>
    <p class="mb-1">Delivery: <?= e($request['delivery_address']) ?></p>
    <p class="mb-0">Fees paid: <?= money_ngn($request['total_fee']) ?></p>
</div>
<?php elseif ($approved): ?>
<div class="card-soft mb-4">
    <h2 class="h6">Before you request a card</h2>
    <ul>
        <li>The card identifies you as an approved programme participant.</li>
        <li>Issuance is optional. You may receive an approved grant by bank transfer instead.</li>
        <li>Processing fee: <strong><?= money_ngn($processingFee) ?></strong></li>
        <li>Delivery fee: <strong><?= money_ngn($deliveryFee) ?></strong></li>
        <li>Total payable if you proceed: <strong><?= money_ngn($processingFee + $deliveryFee) ?></strong></li>
    </ul>
    <p class="small"><?= e((string) setting('card_fees_note', 'These charges are not a payment to unlock your grant.')) ?></p>
</div>
<form method="post" class="card-soft">
    <?= csrf_field() ?>
    <h2 class="h6">Request a physical card</h2>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Full name</label>
            <input class="form-control" name="full_name" required value="<?= e(full_name($user)) ?>" readonly>
        </div>
        <div class="col-md-6">
            <label class="form-label">Phone number</label>
            <input class="form-control" name="phone" required value="<?= e($user['phone'] ?? '') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">State of delivery</label>
            <select class="form-select" name="state_id" data-lga-target="card_lga" required>
                <option value="">Select</option>
                <?php foreach ($states as $st): ?>
                    <option value="<?= (int) $st['id'] ?>" <?= (int) ($user['state_id'] ?? 0) === (int) $st['id'] ? 'selected' : '' ?>><?= e($st['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">LGA</label>
            <select class="form-select" name="lga_id" id="card_lga" required>
                <option value="">Select</option>
                <?php foreach ($lgas as $lga): ?>
                    <option value="<?= (int) $lga['id'] ?>" <?= (int) ($user['lga_id'] ?? 0) === (int) $lga['id'] ? 'selected' : '' ?>><?= e($lga['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">Delivery address</label>
            <textarea class="form-control" name="delivery_address" required><?= e($user['address'] ?? '') ?></textarea>
        </div>
        <div class="col-12">
            <label class="form-label">Preferred collection centre</label>
            <select class="form-select" name="collection_centre_id">
                <option value="">Home delivery</option>
                <?php foreach ($centres as $c): ?>
                    <option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?> — <?= e($c['address']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="confirm_fees" id="confirm_fees" required>
                <label class="form-check-label" for="confirm_fees">I confirm the disclosed processing and delivery charges of <?= money_ngn($processingFee + $deliveryFee) ?> and understand this is optional.</label>
            </div>
        </div>
    </div>
    <button class="btn btn-official mt-3 portal-primary" type="submit">Continue to payment</button>
</form>
<?php endif; ?>
<?php layout_end('user'); ?>
