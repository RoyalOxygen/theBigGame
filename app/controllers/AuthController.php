<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Mailer;
use App\Services\RateLimiter;
use App\Services\WalletService;

class AuthController
{
    public static function register(): void
    {
        if (auth_user()) {
            redirect('user/dashboard.php');
        }
        if (!setting_bool('allow_registration', true)) {
            flash('error', 'Applicant registration is currently closed. Please check back later.');
            redirect('login.php');
        }
        $errors = [];
        if (is_post()) {
            if (!verify_csrf()) {
                $errors[] = 'Invalid security token. Please try again.';
            }
            if (!RateLimiter::hit('register:' . client_ip(), 8, 600)) {
                $errors[] = 'Too many registration attempts. Please wait and try again.';
            }
            $email = strtolower(trim((string) ($_POST['email'] ?? '')));
            $password = (string) ($_POST['password'] ?? '');
            $confirm = (string) ($_POST['password_confirm'] ?? '');
            $_SESSION['_old'] = ['email' => $email];

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Enter a valid email address.';
            }
            if (strlen($password) < 8) {
                $errors[] = 'Password must be at least 8 characters.';
            }
            if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
                $errors[] = 'Password must include uppercase, lowercase and a number.';
            }
            if ($password !== $confirm) {
                $errors[] = 'Password confirmation does not match.';
            }
            if (!$errors) {
                $stmt = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $errors[] = 'An account with this email already exists. Please log in.';
                }
            }
            if (!$errors) {
                $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
                db()->prepare('INSERT INTO users (email, password_hash, created_at, updated_at) VALUES (?, ?, NOW(), NOW())')
                    ->execute([$email, $hash]);
                $userId = (int) db()->lastInsertId();
                db()->prepare('INSERT INTO user_profiles (user_id, created_at, updated_at) VALUES (?, NOW(), NOW())')->execute([$userId]);
                WalletService::ensure($userId);
                self::queueVerification($userId, $email);
                unset($_SESSION['_old']);
                flash('success', 'Account created. Please check your email to verify your address.');
                $_SESSION['pending_verify_email'] = $email;
                redirect('verify-email.php?pending=1');
            }
            flash_errors($errors);
        }
        view('auth/register', ['title' => 'Create Account']);
    }

    public static function login(): void
    {
        if (auth_user()) {
            redirect('user/dashboard.php');
        }
        $errors = [];
        if (is_post()) {
            if (!verify_csrf()) {
                $errors[] = 'Invalid security token.';
            }
            $email = strtolower(trim((string) ($_POST['email'] ?? '')));
            $password = (string) ($_POST['password'] ?? '');
            $_SESSION['_old'] = ['email' => $email];
            if ($errors) {
                flash_errors($errors);
                view('auth/login', ['title' => 'Applicant Login']);
                return;
            }
            if (!RateLimiter::hit('login:' . client_ip(), 20, 300)) {
                $errors[] = 'Too many login attempts. Please wait a few minutes.';
                flash_errors($errors);
                view('auth/login', ['title' => 'Applicant Login']);
                return;
            }
            $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user && !empty($user['locked_until']) && strtotime((string) $user['locked_until']) > time()) {
                $errors[] = 'This account is temporarily locked. Try again later.';
            } elseif (!$user || !password_verify($password, $user['password_hash'])) {
                $errors[] = 'Invalid email or password.';
                if ($user) {
                    $attempts = (int) $user['login_attempts'] + 1;
                    $lock = $attempts >= 5 ? date('Y-m-d H:i:s', time() + 900) : null;
                    db()->prepare('UPDATE users SET login_attempts = ?, locked_until = ? WHERE id = ?')->execute([$attempts, $lock, $user['id']]);
                }
            } else {
                db()->prepare('UPDATE users SET login_attempts = 0, locked_until = NULL, last_login_at = NOW(), last_login_ip = ?, updated_at = NOW() WHERE id = ?')
                    ->execute([client_ip(), $user['id']]);
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $user['id'];
                unset($_SESSION['_old']);
                if (setting_bool('require_email_verification', true) && empty($user['email_verified_at'])) {
                    redirect('verify-email.php?pending=1');
                }
                $intended = $_SESSION['intended'] ?? 'user/dashboard.php';
                unset($_SESSION['intended']);
                redirect(ltrim((string) $intended, '/'));
            }
            flash_errors($errors);
        }
        view('auth/login', ['title' => 'Applicant Login']);
    }

    public static function logout(): void
    {
        unset($_SESSION['user_id']);
        session_regenerate_id(true);
        flash('success', 'You have been signed out.');
        redirect('login.php');
    }

    public static function verifyEmail(): void
    {
        $token = (string) ($_GET['token'] ?? '');
        if ($token !== '') {
            $hash = hash('sha256', $token);
            $stmt = db()->prepare('SELECT * FROM email_verification_tokens WHERE token_hash = ? AND used_at IS NULL LIMIT 1');
            $stmt->execute([$hash]);
            $row = $stmt->fetch();
            if (!$row || strtotime((string) $row['expires_at']) < time()) {
                flash('error', 'This verification link is invalid or has expired.');
                redirect('verify-email.php?pending=1');
            }
            db()->prepare('UPDATE users SET email_verified_at = NOW(), updated_at = NOW() WHERE id = ?')->execute([$row['user_id']]);
            db()->prepare('UPDATE email_verification_tokens SET used_at = NOW() WHERE id = ?')->execute([$row['id']]);
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $row['user_id'];
            notify_user((int) $row['user_id'], 'Email verified', 'Your email address has been verified. Please complete your application.', 'success', url('user/apply.php'));
            flash('success', 'Email verified successfully. Please complete your application.');
            redirect('user/apply.php');
        }

        if (is_post() && verify_csrf()) {
            $email = strtolower(trim((string) ($_POST['email'] ?? ($_SESSION['pending_verify_email'] ?? ''))));
            $user = auth_user();
            if ($user) {
                $email = $user['email'];
            }
            if ($email && RateLimiter::hit('reverify:' . $email, 5, 600)) {
                $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
                $stmt->execute([$email]);
                $found = $stmt->fetch();
                if ($found && empty($found['email_verified_at'])) {
                    self::sendVerification((int) $found['id'], $found['email']);
                }
            }
            flash('success', 'If the account exists and is unverified, a new verification email has been sent.');
            redirect('verify-email.php?pending=1');
        }

        view('auth/verify', ['title' => 'Verify Email']);
    }

    public static function forgot(): void
    {
        if (is_post()) {
            if (!verify_csrf()) {
                flash_errors(['Invalid security token.']);
            } else {
                $email = strtolower(trim((string) ($_POST['email'] ?? '')));
                if (RateLimiter::hit('forgot:' . client_ip(), 8, 600)) {
                    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
                    $stmt->execute([$email]);
                    $user = $stmt->fetch();
                    if ($user) {
                        $token = bin2hex(random_bytes(32));
                        db()->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at, created_at) VALUES (?, ?, ?, NOW())')
                            ->execute([$user['id'], hash('sha256', $token), date('Y-m-d H:i:s', time() + 3600)]);
                        $html = Mailer::template('password_reset', [
                            'name' => $user['email'],
                            'email' => $user['email'],
                            'reset_url' => url('reset-password.php?token=' . $token),
                        ]);
                        Mailer::queue($user['email'], Mailer::subject('password_reset', ['name' => $user['email'], 'email' => $user['email']], 'Reset your NARFCARD password'), $html);
                    }
                }
                flash('success', 'If that email is registered, a password reset link has been sent.');
                redirect('forgot-password.php');
            }
        }
        view('auth/forgot', ['title' => 'Forgot Password']);
    }

    public static function reset(): void
    {
        $token = (string) ($_GET['token'] ?? $_POST['token'] ?? '');
        $hash = $token !== '' ? hash('sha256', $token) : '';
        $stmt = db()->prepare('SELECT * FROM password_resets WHERE token_hash = ? AND used_at IS NULL LIMIT 1');
        $stmt->execute([$hash]);
        $row = $stmt->fetch();
        if (!$row || strtotime((string) $row['expires_at']) < time()) {
            flash('error', 'This password reset link is invalid or has expired.');
            redirect('forgot-password.php');
        }
        if (is_post()) {
            if (!verify_csrf()) {
                flash_errors(['Invalid security token.']);
            } else {
                $password = (string) ($_POST['password'] ?? '');
                $confirm = (string) ($_POST['password_confirm'] ?? '');
                $errors = [];
                if (strlen($password) < 8) {
                    $errors[] = 'Password must be at least 8 characters.';
                }
                if ($password !== $confirm) {
                    $errors[] = 'Password confirmation does not match.';
                }
                if (!$errors) {
                    db()->prepare('UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?')
                        ->execute([password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]), $row['user_id']]);
                    db()->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = ?')->execute([$row['id']]);
                    flash('success', 'Password updated. You may now log in.');
                    redirect('login.php');
                }
                flash_errors($errors);
            }
        }
        view('auth/reset', ['title' => 'Reset Password', 'token' => $token]);
    }

    public static function sendVerification(int $userId, string $email): void
    {
        self::queueVerification($userId, $email);
    }

    public static function queueVerification(int $userId, string $email): void
    {
        $token = bin2hex(random_bytes(32));
        db()->prepare('INSERT INTO email_verification_tokens (user_id, token_hash, expires_at, created_at) VALUES (?, ?, ?, NOW())')
            ->execute([$userId, hash('sha256', $token), date('Y-m-d H:i:s', time() + 86400)]);
        $verifyUrl = url('verify-email.php?token=' . $token);
        $html = Mailer::template('verify_email', [
            'name' => $email,
            'verify_url' => $verifyUrl,
            'email' => $email,
        ]);
        Mailer::queue($email, Mailer::subject('verify_email', ['name' => $email, 'email' => $email, 'verify_url' => $verifyUrl], 'Verify your NARFCARD email address'), $html, $email);
    }
}
