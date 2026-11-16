<?php

declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{
    /** @var array<int, array{to:string,subject:string,html:string,toName:?string}> */
    private static array $queue = [];

    public static function send(string $to, string $subject, string $html, ?string $toName = null): bool
    {
        $cfg = self::mailConfig();
        $logDir = BASE_PATH . '/storage/emails';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $plain = trim(html_entity_decode(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $html))));
        preg_match_all('/href=["\']([^"\']+)["\']/', $html, $hrefs);
        $links = $hrefs[1] ?? [];
        $entry = sprintf(
            "[%s]\nTo: %s\nSubject: %s\nLinks:\n%s\n\n%s\n\n----\n",
            date('c'),
            $to,
            $subject,
            $links ? implode("\n", $links) : '(none)',
            $plain
        );
        file_put_contents($logDir . '/mailbox.log', $entry, FILE_APPEND);
        file_put_contents($logDir . '/' . date('YmdHis') . '_' . preg_replace('/[^a-z0-9]+/i', '_', $to) . '.html', $html);

        $useSmtp = self::shouldUseSmtp($cfg);
        if (!$useSmtp) {
            self::log('Mail stored in mailbox.log (SMTP not enabled, host missing, or password not saved).');
            return true;
        }
        if (!class_exists(PHPMailer::class)) {
            self::log('PHPMailer class missing; mail was logged only.');
            return false;
        }

        try {
            @ini_set('default_socket_timeout', '12');
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Timeout = 12;
            $mail->SMTPKeepAlive = false;
            $mail->CharSet = PHPMailer::CHARSET_UTF8;
            $mail->Encoding = PHPMailer::ENCODING_BASE64;
            $mail->Host = trim((string) $cfg['host']);
            $port = (int) ($cfg['port'] ?: 465);
            $mail->Port = $port > 0 ? $port : 465;
            $username = trim((string) ($cfg['username'] ?? ''));
            $password = (string) ($cfg['password'] ?? '');
            $mail->SMTPAuth = true;
            $mail->AuthType = 'LOGIN';
            $mail->Username = $username;
            $mail->Password = $password;
            $enc = strtolower(trim((string) ($cfg['encryption'] ?? '')));
            if ($enc === '' || $enc === 'none') {
                $enc = $mail->Port === 465 ? 'ssl' : ($mail->Port === 587 ? 'tls' : 'ssl');
            }
            if ($enc === 'ssl' || $mail->Port === 465) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;
            } elseif ($enc === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = '';
                $mail->SMTPAutoTLS = false;
            }
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ];
            if ($username !== '' && filter_var($username, FILTER_VALIDATE_EMAIL)) {
                $from = $username;
            } else {
                $from = trim((string) ($cfg['from_address'] ?? ''));
                if ($from === '' || !filter_var($from, FILTER_VALIDATE_EMAIL)) {
                    $from = 'noreply@localhost';
                }
            }
            $fromName = trim((string) ($cfg['from_name'] ?? 'NARFCARD Programme'));
            $mail->setFrom($from, $fromName !== '' ? $fromName : 'NARFCARD Programme');
            $mail->Sender = $from;
            $mail->addAddress($to, $toName ?: $to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $html;
            $mail->AltBody = $plain !== '' ? $plain : $subject;
            $ok = $mail->send();
            if ($ok) {
                self::log('SMTP sent to ' . $to . ' via ' . $mail->Host . ':' . $mail->Port);
            } else {
                self::log('SMTP send returned false: ' . $mail->ErrorInfo);
            }
            return $ok;
        } catch (\Throwable $e) {
            self::log('SMTP failed: ' . $e->getMessage());
            return false;
        }
    }

    public static function queue(string $to, string $subject, string $html, ?string $toName = null): void
    {
        self::$queue[] = [
            'to' => $to,
            'subject' => $subject,
            'html' => $html,
            'toName' => $toName,
        ];
    }

    public static function flushQueue(): void
    {
        if (self::$queue === []) {
            return;
        }
        $jobs = self::$queue;
        self::$queue = [];
        ignore_user_abort(true);
        if (function_exists('session_write_close') && session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        foreach ($jobs as $job) {
            try {
                self::send($job['to'], $job['subject'], $job['html'], $job['toName']);
            } catch (\Throwable $e) {
                self::log('Queued mail failed: ' . $e->getMessage());
            }
        }
    }

    public static function subject(string $name, array $data = [], ?string $fallback = null): string
    {
        $templates = email_templates();
        $default = (string) ($templates[$name]['subject'] ?? $fallback ?? '');
        $text = trim((string) setting('email_subject_' . $name, $default));
        if ($text === '') {
            $text = $default;
        }
        return render_placeholders($text, $data);
    }

    public static function template(string $name, array $data = []): string
    {
        $templates = email_templates();
        $default = (string) ($templates[$name]['body'] ?? '');
        $custom = trim((string) setting('email_body_' . $name, $default));
        if ($custom === '') {
            $custom = $default;
        }
        if ($custom !== '') {
            $rendered = render_placeholders($custom, $data);
            if (!preg_match('/<(p|div|ul|ol|li|h[1-6]|table|br)\b/i', $rendered)) {
                $rendered = nl2br($rendered, false);
            }
            $body = sanitize_email_html($rendered);
            ob_start();
            require BASE_PATH . '/app/views/emails/_layout.php';
            return (string) ob_get_clean();
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require BASE_PATH . '/app/views/emails/' . $name . '.php';
        return (string) ob_get_clean();
    }

    public static function status(): array
    {
        $cfg = self::mailConfig();
        $password = (string) ($cfg['password'] ?? '');
        return [
            'smtp' => self::shouldUseSmtp($cfg),
            'host' => (string) ($cfg['host'] ?? ''),
            'port' => (int) ($cfg['port'] ?? 465),
            'username' => (string) ($cfg['username'] ?? ''),
            'from' => (string) ($cfg['from_address'] ?? ''),
            'mailer' => (string) ($cfg['mailer'] ?? 'log'),
            'encryption' => (string) ($cfg['encryption'] ?? 'ssl'),
            'password_set' => $password !== '',
        ];
    }

    private static function shouldUseSmtp(array $cfg): bool
    {
        $host = trim((string) ($cfg['host'] ?? ''));
        $username = trim((string) ($cfg['username'] ?? ''));
        $password = (string) ($cfg['password'] ?? '');
        if ($host === '' || $username === '' || $password === '') {
            return false;
        }
        $mailer = strtolower(trim((string) ($cfg['mailer'] ?? 'smtp')));
        return $mailer !== 'log' || ($host !== '' && $username !== '' && $password !== '');
    }

    public static function mailConfig(): array
    {
        $defaults = setting_defaults();
        $base = config('mail') ?: [];
        $map = [
            'mailer' => 'mail_mailer',
            'host' => 'mail_host',
            'port' => 'mail_port',
            'username' => 'mail_username',
            'password' => 'mail_password',
            'encryption' => 'mail_encryption',
            'from_address' => 'mail_from_address',
            'from_name' => 'mail_from_name',
        ];
        foreach ($map as $cfgKey => $setKey) {
            $val = setting($setKey, $defaults[$setKey] ?? '');
            if ($val !== null && $val !== '') {
                $base[$cfgKey] = $val;
            }
        }
        if (trim((string) ($base['host'] ?? '')) === '') {
            $base['host'] = 'mail.oxygendata.com.ng';
        }
        if ((int) ($base['port'] ?? 0) <= 0) {
            $base['port'] = 465;
        }
        if (trim((string) ($base['username'] ?? '')) === '') {
            $base['username'] = 'nerf@oxygendata.com.ng';
        }
        $enc = strtolower(trim((string) ($base['encryption'] ?? '')));
        if ($enc === '' || $enc === 'none') {
            $base['encryption'] = ((int) $base['port'] === 587) ? 'tls' : 'ssl';
        }
        $from = trim((string) ($base['from_address'] ?? ''));
        $username = trim((string) ($base['username'] ?? ''));
        if ($from === '' || str_contains($from, 'narfcard.ng') || str_contains($from, 'noreply@localhost')) {
            if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
                $base['from_address'] = $username;
            }
        }
        $host = trim((string) ($base['host'] ?? ''));
        $password = (string) ($base['password'] ?? '');
        if ($host !== '' && $username !== '' && $password !== '') {
            $base['mailer'] = 'smtp';
        }
        return $base;
    }

    private static function log(string $message): void
    {
        $dir = BASE_PATH . '/storage/logs';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($dir . '/mail.log', '[' . date('c') . '] ' . $message . "\n", FILE_APPEND);
    }
}
