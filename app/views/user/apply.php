<?php layout_start('user', ['title' => $title, 'user' => $user]);
$labels = [1 => 'Personal information', 2 => 'Occupation', 3 => 'Bank account', 4 => 'Review & submit'];
?>
<h1 class="h4 mb-3">Grant application</h1>
<div class="progress-steps">
    <?php for ($i = 1; $i <= 4; $i++): ?>
        <div class="item <?= $i < $step ? 'done' : ($i === $step ? 'current' : '') ?>">
            <span class="d-none d-sm-inline">Step <?= $i ?>: <?= e($labels[$i]) ?></span>
            <span class="d-sm-none"><?= $i ?>. <?= e($labels[$i]) ?></span>
        </div>
    <?php endfor; ?>
</div>

<div class="card-soft">
<?php if ($step === 1): ?>
<form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="step" value="1">
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">First name</label>
            <input class="form-control" name="first_name" required value="<?= e((string) old('first_name', $profile['first_name'] ?? '')) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Middle name (optional)</label>
            <input class="form-control" name="middle_name" value="<?= e((string) old('middle_name', $profile['middle_name'] ?? '')) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Last name</label>
            <input class="form-control" name="last_name" required value="<?= e((string) old('last_name', $profile['last_name'] ?? '')) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Date of birth</label>
            <input class="form-control" type="date" name="date_of_birth" required value="<?= e((string) old('date_of_birth', $profile['date_of_birth'] ?? '')) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Gender</label>
            <select class="form-select" name="gender" required>
                <?php foreach (['male'=>'Male','female'=>'Female','other'=>'Other','prefer_not_to_say'=>'Prefer not to say'] as $k=>$v): ?>
                    <option value="<?= $k ?>" <?= old('gender', $profile['gender'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Nationality</label>
            <input class="form-control" name="nationality" value="<?= e((string) old('nationality', $profile['nationality'] ?? 'Nigerian')) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Phone number</label>
            <input class="form-control" name="phone" required value="<?= e((string) old('phone', $profile['phone'] ?? '')) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Alternative phone (optional)</label>
            <input class="form-control" name="alt_phone" value="<?= e((string) old('alt_phone', $profile['alt_phone'] ?? '')) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">State of residence</label>
            <select class="form-select" name="state_id" id="state_id" required data-lga-target="lga_id">
                <option value="">Select state</option>
                <?php foreach ($states as $st): ?>
                    <option value="<?= (int) $st['id'] ?>" <?= (int) old('state_id', $profile['state_id'] ?? 0) === (int) $st['id'] ? 'selected' : '' ?>><?= e($st['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Local Government Area</label>
            <select class="form-select" name="lga_id" id="lga_id" required>
                <option value="">Select LGA</option>
                <?php foreach ($lgas as $lga): ?>
                    <option value="<?= (int) $lga['id'] ?>" <?= (int) old('lga_id', $profile['lga_id'] ?? 0) === (int) $lga['id'] ? 'selected' : '' ?>><?= e($lga['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">Residential address</label>
            <textarea class="form-control" name="address" required rows="2"><?= e((string) old('address', $profile['address'] ?? '')) ?></textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label">Means of identification</label>
            <select class="form-select" name="id_type" required>
                <?php foreach (['nin'=>'National Identification Number (NIN)','voters_card'=>'Voter\'s Card','drivers_license'=>'Driver\'s Licence','international_passport'=>'International Passport'] as $k=>$v): ?>
                    <option value="<?= $k ?>" <?= old('id_type', $profile['id_type'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Identification number</label>
            <input class="form-control" name="id_number" required value="<?= e((string) old('id_number', $profile['id_number'] ?? '')) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Passport photograph</label>
            <input class="form-control" type="file" name="photo" accept="image/jpeg,image/png,image/webp" <?= empty($profile['photo_path']) ? 'required' : '' ?>>
            <div class="form-text">JPG, PNG or WEBP, maximum 2MB.</div>
        </div>
    </div>
    <button class="btn btn-official mt-4 portal-primary" type="submit">Save and continue</button>
</form>
<?php elseif ($step === 2): ?>
<form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="step" value="2">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Employment status</label>
            <select class="form-select" name="employment_status" required>
                <?php foreach (['employed'=>'Employed','self_employed'=>'Self-employed','unemployed'=>'Unemployed','student'=>'Student','retired'=>'Retired'] as $k=>$v): ?>
                    <option value="<?= $k ?>" <?= old('employment_status', $app['employment_status'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Occupation</label>
            <select class="form-select" name="occupation" required>
                <?php foreach ($occupations as $occ): ?>
                    <option <?= old('occupation', $app['occupation'] ?? '') === $occ['name'] ? 'selected' : '' ?>><?= e($occ['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Employment category</label>
            <input class="form-control" name="employment_category" value="<?= e((string) old('employment_category', $app['employment_category'] ?? '')) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Employer / business name</label>
            <input class="form-control" name="employer_name" value="<?= e((string) old('employer_name', $app['employer_name'] ?? '')) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Monthly income range</label>
            <select class="form-select" name="monthly_income_range" required>
                <?php foreach (['0-30000','30001-50000','50001-80000','80001-120000','120001-200000','200001+'] as $r): ?>
                    <option value="<?= $r ?>" <?= old('monthly_income_range', $app['monthly_income_range'] ?? '') === $r ? 'selected' : '' ?>><?= e($r) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">State of employment</label>
            <select class="form-select" name="employment_state_id" data-lga-target="employment_lga_id">
                <option value="">Select</option>
                <?php foreach ($states as $st): ?>
                    <option value="<?= (int) $st['id'] ?>" <?= (int) old('employment_state_id', $app['employment_state_id'] ?? 0) === (int) $st['id'] ? 'selected' : '' ?>><?= e($st['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">LGA of employment</label>
            <select class="form-select" name="employment_lga_id" id="employment_lga_id">
                <option value="">Select</option>
                <?php foreach ($empLgas as $lga): ?>
                    <option value="<?= (int) $lga['id'] ?>" <?= (int) old('employment_lga_id', $app['employment_lga_id'] ?? 0) === (int) $lga['id'] ? 'selected' : '' ?>><?= e($lga['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">Residential or business address</label>
            <input class="form-control" name="work_address" value="<?= e((string) old('work_address', $app['work_address'] ?? '')) ?>">
        </div>
        <div class="col-12">
            <label class="form-label">Financial circumstances</label>
            <textarea class="form-control" name="financial_circumstances" rows="4" required><?= e((string) old('financial_circumstances', $app['financial_circumstances'] ?? '')) ?></textarea>
        </div>
    </div>
    <div class="form-actions">
        <a class="btn btn-outline-secondary" href="<?= url('user/apply.php?step=1') ?>">Back</a>
        <button class="btn btn-official" type="submit">Save and continue</button>
    </div>
</form>
<?php elseif ($step === 3): ?>
<div class="alert alert-info">
    Bank details are collected only to enable grant disbursement if your application is approved. They are stored securely and never used to collect PINs, CVVs, internet banking passwords or OTPs.
</div>
<form method="post" id="bankForm">
    <?= csrf_field() ?>
    <input type="hidden" name="step" value="3">
    <input type="hidden" name="account_verified" id="account_verified" value="<?= (int) $app['account_verified'] ?>">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Bank name</label>
            <select class="form-select" name="bank_id" id="bank_id" required>
                <option value="">Select bank</option>
                <?php foreach ($banks as $b): ?>
                    <option value="<?= (int) $b['id'] ?>" data-code="<?= e($b['code']) ?>" <?= (int) old('bank_id', $app['bank_id'] ?? 0) === (int) $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Account number</label>
            <input class="form-control" name="account_number" id="account_number" maxlength="10" required value="<?= e((string) old('account_number', $app['account_number'] ?? '')) ?>">
        </div>
        <div class="col-md-8">
            <label class="form-label">Account holder name</label>
            <input class="form-control" name="account_name" id="account_name" required value="<?= e((string) old('account_name', $app['account_name'] ?? '')) ?>">
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button class="btn btn-outline-success w-100" type="button" id="verifyAccount">Verify account</button>
        </div>
        <div class="col-12"><div id="verifyResult" class="small"></div></div>
    </div>
    <div class="form-actions">
        <a class="btn btn-outline-secondary" href="<?= url('user/apply.php?step=2') ?>">Back</a>
        <button class="btn btn-official" type="submit">Save and continue</button>
    </div>
</form>
<?php else:
    $bankName = '';
    foreach ($banks as $b) {
        if ((int) $b['id'] === (int) $app['bank_id']) {
            $bankName = $b['name'];
        }
    }
?>
<p>Review your information before submitting. You may go back to edit any section.</p>
<div class="row g-3">
    <div class="col-md-6">
        <div class="border rounded p-3 h-100">
            <h2 class="h6">Personal</h2>
            <p class="mb-1"><?= e(full_name($profile)) ?></p>
            <p class="mb-1"><?= e($profile['phone'] ?? '') ?></p>
            <p class="mb-1"><?= e($profile['address'] ?? '') ?></p>
            <a href="<?= url('user/apply.php?step=1') ?>">Edit</a>
        </div>
    </div>
    <div class="col-md-6">
        <div class="border rounded p-3 h-100">
            <h2 class="h6">Occupation</h2>
            <p class="mb-1"><?= e($app['employment_status'] ?? '') ?> &middot; <?= e($app['occupation'] ?? '') ?></p>
            <p class="mb-1">Income: <?= e($app['monthly_income_range'] ?? '') ?></p>
            <a href="<?= url('user/apply.php?step=2') ?>">Edit</a>
        </div>
    </div>
    <div class="col-12">
        <div class="border rounded p-3">
            <h2 class="h6">Bank</h2>
            <p class="mb-1"><?= e($bankName) ?> &middot; <?= e(mask_account($app['account_number'] ?? '')) ?></p>
            <p class="mb-1"><?= e($app['account_name'] ?? '') ?></p>
            <a href="<?= url('user/apply.php?step=3') ?>">Edit</a>
        </div>
    </div>
</div>
<form method="post" class="mt-4">
    <?= csrf_field() ?>
    <input type="hidden" name="step" value="4">
    <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" name="privacy_accepted" id="privacy_accepted" required>
        <label class="form-check-label" for="privacy_accepted">I have read the <a href="<?= url('privacy.php') ?>" target="_blank">privacy notice</a> and consent to processing of my personal data for this application under the Nigeria Data Protection Act.</label>
    </div>
    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="declaration_accepted" id="declaration_accepted" required>
        <label class="form-check-label" for="declaration_accepted">I declare that the information provided is true and complete. I understand that eligibility does not guarantee approval and that awards depend on available programme funding.</label>
    </div>
    <button class="btn btn-official apply-submit" type="submit">Submit application</button>
</form>
<?php endif; ?>
</div>
<?php layout_end('user'); ?>
