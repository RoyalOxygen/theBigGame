<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Mailer;

class ApplicationController
{
    public static function current(int $userId): array
    {
        $stmt = db()->prepare('SELECT * FROM applications WHERE user_id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $app = $stmt->fetch();
        if ($app) {
            return $app;
        }
        $ref = generate_reference('NAR');
        db()->prepare('INSERT INTO applications (user_id, reference, status, step, created_at, updated_at) VALUES (?, ?, ?, 1, NOW(), NOW())')
            ->execute([$userId, $ref, 'draft']);
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: [];
    }

    public static function handle(): void
    {
        $user = require_user();
        if (setting_bool('require_email_verification', true) && empty($user['email_verified_at'])) {
            flash('info', 'Verify your email address before submitting an application.');
            redirect('verify-email.php?pending=1');
        }
        $existing = db()->prepare('SELECT id FROM applications WHERE user_id = ? LIMIT 1');
        $existing->execute([(int) $user['id']]);
        if (!$existing->fetch() && !setting_bool('applications_open', true)) {
            flash('info', 'New applications are not being accepted at this time.');
            redirect('user/dashboard.php');
        }
        $app = self::current((int) $user['id']);
        if (in_array($app['status'], ['submitted', 'under_review', 'approved', 'rejected'], true)) {
            flash('info', 'Your application has already been submitted.');
            redirect('user/dashboard.php');
        }

        $step = (int) ($app['step'] ?? 1);
        if (isset($_GET['step'])) {
            $req = (int) $_GET['step'];
            if ($req >= 1 && $req <= $step) {
                $step = $req;
            }
        }

        if (is_post()) {
            if (!verify_csrf()) {
                flash_errors(['Invalid security token.']);
                redirect('user/apply.php?step=' . $step);
            }
            $postedStep = (int) ($_POST['step'] ?? $step);
            if ($postedStep === 1) {
                self::saveStep1($user, $app);
            } elseif ($postedStep === 2) {
                self::saveStep2($app);
            } elseif ($postedStep === 3) {
                self::saveStep3($app);
            } elseif ($postedStep === 4) {
                self::submit($user, $app);
            }
        }

        $states = db()->query('SELECT id, name FROM states ORDER BY name')->fetchAll();
        $banks = db()->query('SELECT id, name, code FROM banks WHERE is_active = 1 ORDER BY name')->fetchAll();
        $occupations = db()->query('SELECT name, category FROM occupations ORDER BY name')->fetchAll();
        $profile = db()->prepare('SELECT * FROM user_profiles WHERE user_id = ?');
        $profile->execute([$user['id']]);
        $profile = $profile->fetch() ?: [];
        $lgas = [];
        if (!empty($profile['state_id'])) {
            $ls = db()->prepare('SELECT id, name FROM local_governments WHERE state_id = ? ORDER BY name');
            $ls->execute([$profile['state_id']]);
            $lgas = $ls->fetchAll();
        }
        $empLgas = [];
        if (!empty($app['employment_state_id'])) {
            $ls = db()->prepare('SELECT id, name FROM local_governments WHERE state_id = ? ORDER BY name');
            $ls->execute([$app['employment_state_id']]);
            $empLgas = $ls->fetchAll();
        }

        view('user/apply', [
            'title' => 'Grant Application',
            'user' => $user,
            'app' => $app,
            'profile' => $profile,
            'step' => $step,
            'states' => $states,
            'lgas' => $lgas,
            'empLgas' => $empLgas,
            'banks' => $banks,
            'occupations' => $occupations,
        ]);
    }

    private static function saveStep1(array $user, array $app): void
    {
        $errors = [];
        $first = trim((string) ($_POST['first_name'] ?? ''));
        $middle = trim((string) ($_POST['middle_name'] ?? ''));
        $last = trim((string) ($_POST['last_name'] ?? ''));
        $dob = (string) ($_POST['date_of_birth'] ?? '');
        $gender = (string) ($_POST['gender'] ?? '');
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $alt = trim((string) ($_POST['alt_phone'] ?? ''));
        $nationality = trim((string) ($_POST['nationality'] ?? 'Nigerian'));
        $stateId = (int) ($_POST['state_id'] ?? 0);
        $lgaId = (int) ($_POST['lga_id'] ?? 0);
        $address = trim((string) ($_POST['address'] ?? ''));
        $idType = (string) ($_POST['id_type'] ?? '');
        $idNumber = trim((string) ($_POST['id_number'] ?? ''));

        if ($first === '' || $last === '') {
            $errors[] = 'First name and last name are required.';
        }
        if ($dob === '' || strtotime($dob) === false) {
            $errors[] = 'Enter a valid date of birth.';
        } else {
            $age = (int) ((time() - strtotime($dob)) / 31557600);
            $minAge = (int) setting('min_age', 18);
            if ($age < $minAge) {
                $errors[] = 'Applicants must be at least ' . $minAge . ' years old.';
            }
        }
        if (!in_array($gender, ['male', 'female', 'other', 'prefer_not_to_say'], true)) {
            $errors[] = 'Select a gender option.';
        }
        if (!nigeria_phone_valid($phone)) {
            $errors[] = 'Enter a valid Nigerian phone number.';
        }
        if ($stateId < 1 || $lgaId < 1) {
            $errors[] = 'Select your state and local government area.';
        }
        if (strlen($address) < 8) {
            $errors[] = 'Enter a complete residential address.';
        }
        if (!in_array($idType, ['nin', 'voters_card', 'drivers_license', 'international_passport'], true)) {
            $errors[] = 'Select a means of identification.';
        }
        if ($idNumber === '') {
            $errors[] = 'Enter your identification number.';
        }

        $photoPath = $user['photo_path'] ?? null;
        if (!empty($_FILES['photo']['tmp_name'])) {
            $photoPath = self::storeUpload($_FILES['photo'], 'photos', ['image/jpeg', 'image/png', 'image/webp']);
            if (!$photoPath) {
                $errors[] = 'Upload a valid passport photograph (JPG, PNG or WEBP, max 2MB).';
            }
        } elseif (!$photoPath) {
            $errors[] = 'A passport photograph is required.';
        }

        if ($errors) {
            flash_errors($errors);
            $_SESSION['_old'] = $_POST;
            redirect('user/apply.php?step=1');
        }

        db()->prepare('UPDATE user_profiles SET first_name=?, middle_name=?, last_name=?, date_of_birth=?, gender=?, phone=?, alt_phone=?, nationality=?, state_id=?, lga_id=?, address=?, id_type=?, id_number=?, photo_path=?, updated_at=NOW() WHERE user_id=?')
            ->execute([$first, $middle ?: null, $last, $dob, $gender, $phone, $alt ?: null, $nationality, $stateId, $lgaId, $address, $idType, $idNumber, $photoPath, $user['id']]);

        $next = max((int) $app['step'], 2);
        db()->prepare('UPDATE applications SET step = ?, updated_at = NOW() WHERE id = ?')->execute([$next, $app['id']]);
        redirect('user/apply.php?step=2');
    }

    private static function saveStep2(array $app): void
    {
        $errors = [];
        $emp = (string) ($_POST['employment_status'] ?? '');
        $occ = trim((string) ($_POST['occupation'] ?? ''));
        $cat = (string) ($_POST['employment_category'] ?? '');
        $employer = trim((string) ($_POST['employer_name'] ?? ''));
        $income = (string) ($_POST['monthly_income_range'] ?? '');
        $stateId = (int) ($_POST['employment_state_id'] ?? 0);
        $lgaId = (int) ($_POST['employment_lga_id'] ?? 0);
        $work = trim((string) ($_POST['work_address'] ?? ''));
        $circ = trim((string) ($_POST['financial_circumstances'] ?? ''));

        if (!in_array($emp, ['employed', 'self_employed', 'unemployed', 'student', 'retired'], true)) {
            $errors[] = 'Select your employment status.';
        }
        if ($occ === '' || $income === '') {
            $errors[] = 'Occupation and monthly income range are required.';
        }
        if ($circ === '') {
            $errors[] = 'Please describe your financial circumstances.';
        }
        if ($errors) {
            flash_errors($errors);
            $_SESSION['_old'] = $_POST;
            redirect('user/apply.php?step=2');
        }
        db()->prepare('UPDATE applications SET employment_status=?, occupation=?, employment_category=?, employer_name=?, monthly_income_range=?, employment_state_id=?, employment_lga_id=?, work_address=?, financial_circumstances=?, step=GREATEST(step,3), updated_at=NOW() WHERE id=?')
            ->execute([$emp, $occ, $cat ?: null, $employer ?: null, $income, $stateId ?: null, $lgaId ?: null, $work ?: null, $circ, $app['id']]);
        redirect('user/apply.php?step=3');
    }

    private static function saveStep3(array $app): void
    {
        $errors = [];
        $bankId = (int) ($_POST['bank_id'] ?? 0);
        $acct = preg_replace('/\D+/', '', (string) ($_POST['account_number'] ?? '')) ?? '';
        $name = strtoupper(trim((string) ($_POST['account_name'] ?? '')));
        $verified = (int) ($_POST['account_verified'] ?? 0);
        if ($bankId < 1) {
            $errors[] = 'Select your bank.';
        }
        if (!preg_match('/^\d{10}$/', $acct)) {
            $errors[] = 'Enter a 10-digit NUBAN account number.';
        }
        if (strlen($name) < 3) {
            $errors[] = 'Enter the account holder name.';
        }
        if ($errors) {
            flash_errors($errors);
            $_SESSION['_old'] = $_POST;
            redirect('user/apply.php?step=3');
        }
        db()->prepare('UPDATE applications SET bank_id=?, account_number=?, account_name=?, account_verified=?, step=GREATEST(step,4), updated_at=NOW() WHERE id=?')
            ->execute([$bankId, $acct, $name, $verified ? 1 : 0, $app['id']]);
        redirect('user/apply.php?step=4');
    }

    private static function submit(array $user, array $app): void
    {
        $privacy = !empty($_POST['privacy_accepted']);
        $decl = !empty($_POST['declaration_accepted']);
        if (!$privacy || !$decl) {
            flash_errors(['You must accept the privacy notice and application declaration.']);
            redirect('user/apply.php?step=4');
        }
        db()->prepare("UPDATE applications SET privacy_accepted=1, declaration_accepted=1, status='submitted', submitted_at=NOW(), updated_at=NOW() WHERE id=? AND status IN ('draft','additional_info')")
            ->execute([$app['id']]);
        $html = Mailer::template('application_received', [
            'name' => full_name($user) ?: $user['email'],
            'email' => $user['email'],
            'reference' => $app['reference'],
            'date' => date('d M Y H:i'),
        ]);
        Mailer::queue($user['email'], Mailer::subject('application_received', ['name' => full_name($user) ?: $user['email'], 'email' => $user['email'], 'reference' => $app['reference']], 'Your NARFCARD Application Has Been Received'), $html, full_name($user));
        notify_user((int) $user['id'], 'Application submitted', 'Your application ' . $app['reference'] . ' has been received and is under review.', 'success', url('user/dashboard.php'));
        flash('success', 'Application submitted. Reference: ' . $app['reference']);
        redirect('user/dashboard.php');
    }

    private static function storeUpload(array $file, string $folder, array $mimes): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }
        if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
            return null;
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array($mime, $mimes, true)) {
            return null;
        }
        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'application/pdf' => 'pdf',
            default => 'bin',
        };
        $name = bin2hex(random_bytes(12)) . '.' . $ext;
        $dir = BASE_PATH . '/uploads/' . $folder;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $dest = $dir . '/' . $name;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return null;
        }
        return $folder . '/' . $name;
    }
}
