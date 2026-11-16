<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Mailer;
use App\Services\RateLimiter;
use App\Services\WalletService;

class AdminController
{
    public static function login(): void
    {
        if (auth_admin()) {
            redirect('admin/index.php');
        }
        if (is_post()) {
            $errors = [];
            if (!verify_csrf()) {
                $errors[] = 'Invalid security token.';
            }
            if (!RateLimiter::hit('admin-login:' . client_ip(), 8, 300)) {
                $errors[] = 'Too many login attempts.';
            }
            $email = strtolower(trim((string) ($_POST['email'] ?? '')));
            $password = (string) ($_POST['password'] ?? '');
            $stmt = db()->prepare('SELECT * FROM administrators WHERE email = ? AND is_active = 1 LIMIT 1');
            $stmt->execute([$email]);
            $admin = $stmt->fetch();
            if (!$admin || !password_verify($password, $admin['password_hash'])) {
                $errors[] = 'Invalid administrator credentials.';
                flash_errors($errors);
            } else {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = (int) $admin['id'];
                db()->prepare('UPDATE administrators SET last_login_at = NOW() WHERE id = ?')->execute([$admin['id']]);
                audit_admin('login');
                redirect('admin/index.php');
            }
        }
        view('admin/login', ['title' => 'Administrator Login']);
    }

    public static function logout(): void
    {
        unset($_SESSION['admin_id']);
        session_regenerate_id(true);
        redirect('admin/login.php');
    }

    public static function approve(int $applicationId): void
    {
        require_admin(['grants.approve']);
        $admin = auth_admin();
        $amount = (float) ($_POST['amount'] ?? 0);
        $notes = trim((string) ($_POST['notes'] ?? ''));
        $fundingId = (int) ($_POST['funding_id'] ?? 0);
        $min = (float) setting('min_grant_amount', 25000);
        $max = (float) setting('max_grant_amount', 250000);
        if ($amount < $min || $amount > $max) {
            flash('error', 'Grant amount must be within authorised limits.');
            redirect('admin/application.php?id=' . $applicationId);
        }

        $pdo = db();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("SELECT * FROM applications WHERE id = ? FOR UPDATE");
            $stmt->execute([$applicationId]);
            $app = $stmt->fetch();
            if (!$app || !in_array($app['status'], ['submitted', 'under_review', 'additional_info'], true)) {
                throw new \RuntimeException('Application cannot be approved in its current status.');
            }
            $fund = null;
            if ($fundingId) {
                $fs = $pdo->prepare('SELECT * FROM grant_funding WHERE id = ? FOR UPDATE');
                $fs->execute([$fundingId]);
                $fund = $fs->fetch();
                if (!$fund || (float) $fund['available_amount'] < $amount) {
                    throw new \RuntimeException('Insufficient programme funding for this approval.');
                }
            }
            $ref = generate_reference('GAP');
            $pdo->prepare("UPDATE applications SET status='approved', reviewed_at=NOW(), reviewed_by=?, updated_at=NOW() WHERE id=?")
                ->execute([$admin['id'], $applicationId]);
            $pdo->prepare('INSERT INTO grant_approvals (application_id, user_id, admin_id, funding_id, amount, approval_reference, eligibility_confirmed, funding_confirmed, wallet_credited, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, 1, 1, 1, ?, NOW())')
                ->execute([$applicationId, $app['user_id'], $admin['id'], $fundingId ?: null, $amount, $ref, $notes ?: null]);
            if ($fund) {
                $pdo->prepare('UPDATE grant_funding SET allocated_amount = allocated_amount + ?, available_amount = available_amount - ?, updated_at = NOW() WHERE id = ?')
                    ->execute([$amount, $amount, $fund['id']]);
            }
            $pdo->commit();
            WalletService::credit((int) $app['user_id'], $amount, 'grant_credit', 'Approved relief grant ' . $ref, $ref, $applicationId);
            $user = $pdo->prepare('SELECT u.email, p.first_name, p.last_name FROM users u LEFT JOIN user_profiles p ON p.user_id = u.id WHERE u.id = ?');
            $user->execute([$app['user_id']]);
            $u = $user->fetch() ?: [];
            $html = Mailer::template('application_approved', [
                'name' => full_name($u) ?: $u['email'],
                'email' => $u['email'],
                'reference' => $app['reference'],
                'amount' => money_ngn($amount),
                'approval_reference' => $ref,
            ]);
            Mailer::queue($u['email'], Mailer::subject('application_approved', ['name' => full_name($u) ?: $u['email'], 'email' => $u['email'], 'amount' => money_ngn($amount), 'approval_reference' => $ref], 'Your NARFCARD application has been approved'), $html, full_name($u));
            $creditHtml = Mailer::template('grant_credit', [
                'name' => full_name($u) ?: $u['email'],
                'email' => $u['email'],
                'reference' => $ref,
                'amount' => money_ngn($amount),
            ]);
            Mailer::queue($u['email'], Mailer::subject('grant_credit', ['name' => full_name($u) ?: $u['email'], 'amount' => money_ngn($amount), 'reference' => $ref], 'A grant credit has been posted to your NARFCARD wallet'), $creditHtml, full_name($u));
            notify_user((int) $app['user_id'], 'Application approved', 'Your application was approved. Grant amount: ' . money_ngn($amount), 'success', url('user/wallet.php'));
            audit_admin('application.approve', 'application', $applicationId, ['amount' => $amount, 'ref' => $ref]);
            flash('success', 'Application approved and wallet credited.');
        } catch (\Throwable $e) {
            $pdo->rollBack();
            flash('error', $e->getMessage());
        }
        redirect('admin/application.php?id=' . $applicationId);
    }

    public static function reject(int $applicationId): void
    {
        require_admin(['applicants.review']);
        $reason = trim((string) ($_POST['reason'] ?? ''));
        if ($reason === '') {
            flash('error', 'A rejection reason is required.');
            redirect('admin/application.php?id=' . $applicationId);
        }
        $admin = auth_admin();
        $stmt = db()->prepare("UPDATE applications SET status='rejected', rejection_reason=?, reviewed_at=NOW(), reviewed_by=?, updated_at=NOW() WHERE id=? AND status IN ('submitted','under_review','additional_info')");
        $stmt->execute([$reason, $admin['id'], $applicationId]);
        $app = db()->prepare('SELECT a.*, u.email, p.first_name, p.last_name FROM applications a JOIN users u ON u.id=a.user_id LEFT JOIN user_profiles p ON p.user_id=u.id WHERE a.id=?');
        $app->execute([$applicationId]);
        $row = $app->fetch();
        if ($row) {
            $html = Mailer::template('application_rejected', [
                'name' => full_name($row) ?: $row['email'],
                'email' => $row['email'],
                'reference' => $row['reference'],
                'reason' => $reason,
            ]);
            Mailer::queue($row['email'], Mailer::subject('application_rejected', ['name' => full_name($row) ?: $row['email'], 'email' => $row['email'], 'reference' => $row['reference'], 'reason' => $reason], 'Update on your NARFCARD application'), $html, full_name($row));
            notify_user((int) $row['user_id'], 'Application not approved', 'Your application was not approved. Please read the notification email for details.', 'danger');
        }
        audit_admin('application.reject', 'application', $applicationId, ['reason' => $reason]);
        flash('success', 'Application rejected and applicant notified.');
        redirect('admin/application.php?id=' . $applicationId);
    }

    public static function requestInfo(int $applicationId): void
    {
        require_admin(['applicants.review']);
        $message = trim((string) ($_POST['message'] ?? ''));
        if ($message === '') {
            flash('error', 'Enter the additional information required.');
            redirect('admin/application.php?id=' . $applicationId);
        }
        $admin = auth_admin();
        db()->prepare("UPDATE applications SET status='additional_info', additional_info_request=?, reviewed_by=?, updated_at=NOW() WHERE id=?")
            ->execute([$message, $admin['id'], $applicationId]);
        $app = db()->prepare('SELECT a.*, u.email, p.first_name, p.last_name FROM applications a JOIN users u ON u.id=a.user_id LEFT JOIN user_profiles p ON p.user_id=u.id WHERE a.id=?');
        $app->execute([$applicationId]);
        $row = $app->fetch();
        if ($row) {
            $html = Mailer::template('additional_info', [
                'name' => full_name($row) ?: $row['email'],
                'email' => $row['email'],
                'reference' => $row['reference'],
                'message' => $message,
            ]);
            Mailer::queue($row['email'], Mailer::subject('additional_info', ['name' => full_name($row) ?: $row['email'], 'email' => $row['email'], 'reference' => $row['reference'], 'message' => $message], 'Additional information required for your NARFCARD application'), $html, full_name($row));
            notify_user((int) $row['user_id'], 'Additional information required', $message, 'warning', url('user/apply.php'));
        }
        audit_admin('application.request_info', 'application', $applicationId);
        flash('success', 'Applicant has been asked for additional information.');
        redirect('admin/application.php?id=' . $applicationId);
    }

    public static function addNote(int $applicationId): void
    {
        require_admin(['applicants.notes', 'applicants.review']);
        $note = trim((string) ($_POST['note'] ?? ''));
        if ($note !== '') {
            $admin = auth_admin();
            db()->prepare('INSERT INTO application_notes (application_id, admin_id, note, created_at) VALUES (?, ?, ?, NOW())')
                ->execute([$applicationId, $admin['id'], $note]);
            audit_admin('application.note', 'application', $applicationId);
        }
        redirect('admin/application.php?id=' . $applicationId);
    }

    public static function updateCardStatus(int $requestId): void
    {
        require_admin(['cards.manage']);
        $status = (string) ($_POST['status'] ?? '');
        $allowed = ['processing', 'produced', 'dispatched', 'ready_for_collection', 'delivered', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            flash('error', 'Invalid card status.');
            redirect('admin/cards.php');
        }
        $prod = trim((string) ($_POST['production_reference'] ?? ''));
        $dispatch = trim((string) ($_POST['dispatch_info'] ?? ''));
        $centre = (int) ($_POST['collection_centre_id'] ?? 0);
        db()->prepare('UPDATE card_requests SET status=?, production_reference=COALESCE(NULLIF(?, ""), production_reference), dispatch_info=COALESCE(NULLIF(?, ""), dispatch_info), collection_centre_id=IF(?=0, collection_centre_id, ?), updated_at=NOW() WHERE id=?')
            ->execute([$status, $prod, $dispatch, $centre, $centre, $requestId]);

        $rowStmt = db()->prepare('SELECT cr.*, u.email, p.first_name, p.last_name, s.name AS state_name, l.name AS lga_name, cc.name AS centre_name, cc.address AS centre_address FROM card_requests cr JOIN users u ON u.id=cr.user_id LEFT JOIN user_profiles p ON p.user_id=u.id LEFT JOIN states s ON s.id=cr.state_id LEFT JOIN local_governments l ON l.id=cr.lga_id LEFT JOIN collection_centres cc ON cc.id=cr.collection_centre_id WHERE cr.id=?');
        $rowStmt->execute([$requestId]);
        $row = $rowStmt->fetch();
        if ($row) {
            if (in_array($status, ['produced', 'processing'], true)) {
                $exists = db()->prepare('SELECT id FROM cards WHERE card_request_id = ?');
                $exists->execute([$requestId]);
                if (!$exists->fetch()) {
                    db()->prepare('INSERT INTO cards (user_id, card_request_id, display_name, masked_pan, status, issued_at, created_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())')
                        ->execute([$row['user_id'], $requestId, $row['full_name'], '**** **** **** ----', 'issued']);
                }
            }
            if ($status === 'ready_for_collection') {
                $html = Mailer::template('card_ready', [
                    'name' => full_name($row) ?: $row['email'],
                    'email' => $row['email'],
                    'reference' => $row['reference'],
                    'centre' => $row['centre_name'] ?: 'your designated collection centre',
                    'address' => $row['centre_address'] ?: $row['delivery_address'],
                    'state' => $row['state_name'],
                    'lga' => $row['lga_name'],
                ]);
                Mailer::queue($row['email'], Mailer::subject('card_ready', ['name' => full_name($row) ?: $row['email'], 'email' => $row['email'], 'reference' => $row['reference'], 'centre' => $row['centre_name'] ?: 'your designated collection centre'], 'Your NARFCARD is ready for collection'), $html, full_name($row));
                notify_user((int) $row['user_id'], 'Card ready for collection', 'Your relief fund card is ready for collection.', 'success', url('user/card.php'));
            }
            if ($status === 'delivered') {
                $html = Mailer::template('card_delivered', [
                    'name' => full_name($row) ?: $row['email'],
                    'email' => $row['email'],
                    'reference' => $row['reference'],
                ]);
                Mailer::queue($row['email'], Mailer::subject('card_delivered', ['name' => full_name($row) ?: $row['email'], 'email' => $row['email'], 'reference' => $row['reference']], 'Your NARFCARD collection has been confirmed'), $html, full_name($row));
            }
        }
        audit_admin('card.status', 'card_request', $requestId, ['status' => $status]);
        flash('success', 'Card request updated.');
        redirect('admin/card.php?id=' . $requestId);
    }
}
