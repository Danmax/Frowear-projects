<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

// ─────────────────────────────────────────────────────────────
// SMTP transport (used when MAIL_SMTP_HOST is set in .env)
// Supports SSL on port 465 and STARTTLS on port 587.
// Hostinger: ssl://mail.hostinger.com:465
// ─────────────────────────────────────────────────────────────
function fw_smtp_send(string $toAddress, string $toName, string $subject, string $htmlBody): bool
{
    $host      = env_value('MAIL_SMTP_HOST', '') ?? '';
    $port      = (int) (env_value('MAIL_SMTP_PORT', '465') ?? '465');
    $user      = env_value('MAIL_SMTP_USER', '') ?? '';
    $pass      = env_value('MAIL_SMTP_PASS', '') ?? '';
    $secure    = strtolower(env_value('MAIL_SMTP_SECURE', 'ssl') ?? 'ssl');
    $from      = env_value('MAIL_FROM_ADDRESS', 'hello@frowear.com') ?? 'hello@frowear.com';
    $fromName  = env_value('MAIL_FROM_NAME', 'Frowear Productions') ?? 'Frowear Productions';

    if ($host === '' || $user === '' || $pass === '') {
        return false;
    }

    $ctx = stream_context_create([
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
        ],
    ]);

    $dsn    = ($secure === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;
    $socket = @stream_socket_client($dsn, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);

    if (!is_resource($socket)) {
        return false;
    }

    stream_set_timeout($socket, 15);

    $read = static function () use ($socket): string {
        $buf = '';
        while (!feof($socket)) {
            $line = fgets($socket, 515);
            if ($line === false) {
                break;
            }
            $buf .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return $buf;
    };

    $write = static function (string $cmd) use ($socket): void {
        fwrite($socket, $cmd . "\r\n");
    };

    $ehlo = gethostname() ?: 'localhost';

    $read(); // server greeting

    $write('EHLO ' . $ehlo);
    $read();

    if ($secure === 'tls') {
        $write('STARTTLS');
        $read();
        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT);
        $write('EHLO ' . $ehlo);
        $read();
    }

    $write('AUTH LOGIN');
    $read();
    $write(base64_encode($user));
    $read();
    $write(base64_encode($pass));
    $authResp = $read();

    if (strpos($authResp, '235') === false) {
        fclose($socket);
        return false;
    }

    $write('MAIL FROM:<' . $from . '>');
    $read();
    $write('RCPT TO:<' . $toAddress . '>');
    $rcptResp = $read();

    if (strpos($rcptResp, '250') === false && strpos($rcptResp, '251') === false) {
        fclose($socket);
        return false;
    }

    $write('DATA');
    $read();

    $b64Body = chunk_split(base64_encode($htmlBody));

    $msg  = 'From: =?UTF-8?B?' . base64_encode($fromName) . '?= <' . $from . ">\r\n";
    $msg .= 'To: =?UTF-8?B?' . base64_encode($toName ?: $toAddress) . '?= <' . $toAddress . ">\r\n";
    $msg .= 'Subject: =?UTF-8?B?' . base64_encode($subject) . "?=\r\n";
    $msg .= "MIME-Version: 1.0\r\n";
    $msg .= "Content-Type: text/html; charset=UTF-8\r\n";
    $msg .= "Content-Transfer-Encoding: base64\r\n";
    $msg .= "X-Mailer: Frowear/2.0\r\n";
    $msg .= "\r\n";
    $msg .= $b64Body;
    $msg .= "\r\n.\r\n";

    fwrite($socket, $msg);
    $dataResp = $read();

    $write('QUIT');
    fclose($socket);

    return strpos($dataResp, '250') !== false;
}

// ─────────────────────────────────────────────────────────────
// Public API — tries SMTP first, falls back to mail()
// ─────────────────────────────────────────────────────────────
function fw_send_email(string $toAddress, string $toName, string $subject, string $htmlBody): bool
{
    // SMTP path (preferred — required for Hostinger to send from info@frowear.com)
    if ((env_value('MAIL_SMTP_HOST', '') ?? '') !== '') {
        return fw_smtp_send($toAddress, $toName, $subject, $htmlBody);
    }

    // Fallback: PHP mail() — works on servers with sendmail configured
    $fromAddress = env_value('MAIL_FROM_ADDRESS', 'hello@frowear.com') ?? 'hello@frowear.com';
    $fromName    = env_value('MAIL_FROM_NAME', 'Frowear Productions') ?? 'Frowear Productions';
    $encodedFrom = '=?UTF-8?B?' . base64_encode($fromName) . '?=';
    $encodedTo   = '=?UTF-8?B?' . base64_encode($toName ?: $toAddress) . '?=';
    $encodedSubj = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $to          = $encodedTo . ' <' . $toAddress . '>';

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= 'From: ' . $encodedFrom . ' <' . $fromAddress . ">\r\n";
    $headers .= 'Reply-To: ' . $encodedFrom . ' <' . $fromAddress . ">\r\n";
    $headers .= "X-Mailer: Frowear/2.0\r\n";

    return mail($to, $encodedSubj, $htmlBody, $headers, '-f' . $fromAddress);
}

function fw_mail_base_url(): string
{
    return env_value('APP_URL', 'https://frowear.com') ?? 'https://frowear.com';
}

// ─────────────────────────────────────────────────────────────
// Branded email templates
// ─────────────────────────────────────────────────────────────
function fw_mail_wrap(string $title, string $bodyHtml): string
{
    $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    return '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>' . $safeTitle . '</title>
</head>
<body style="margin:0;padding:0;background:#040914;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#040914;padding:40px 20px;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#081224;border:1px solid rgba(104,199,255,0.2);border-radius:8px;overflow:hidden;">
      <tr>
        <td style="padding:28px 40px;border-bottom:1px solid rgba(104,199,255,0.2);">
          <span style="font-size:13px;font-weight:700;color:#4be7ff;letter-spacing:0.22em;text-transform:uppercase;">FWP</span>
          <span style="font-size:13px;color:#95a9c0;margin-left:10px;letter-spacing:0.04em;">Frowear Productions</span>
        </td>
      </tr>
      <tr><td style="padding:40px;">' . $bodyHtml . '</td></tr>
      <tr>
        <td style="padding:20px 40px;border-top:1px solid rgba(104,199,255,0.2);">
          <p style="margin:0;font-size:12px;color:rgba(149,169,192,0.5);">&copy; Frowear Productions &mdash; info@frowear.com</p>
        </td>
      </tr>
    </table>
  </td></tr>
</table>
</body>
</html>';
}

function fw_send_verification_email(string $toAddress, string $toName, string $rawToken): void
{
    $link      = fw_mail_base_url() . '/api/auth/verify.php?token=' . urlencode($rawToken);
    $firstName = $toName !== '' ? htmlspecialchars($toName, ENT_QUOTES, 'UTF-8') : 'there';
    $safeLink  = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');

    $body = '
<h1 style="margin:0 0 16px;font-size:22px;font-weight:600;color:#f5fbff;">Verify your account</h1>
<p style="margin:0 0 16px;font-size:15px;color:#95a9c0;line-height:1.65;">Hi ' . $firstName . ', thanks for joining Frowear Productions. Click below to verify your email and activate your account.</p>
<table cellpadding="0" cellspacing="0" style="margin:28px 0;">
  <tr><td style="background:#4be7ff;border-radius:6px;">
    <a href="' . $safeLink . '" style="display:inline-block;padding:13px 30px;font-size:14px;font-weight:700;color:#040914;text-decoration:none;letter-spacing:0.03em;">Verify Email Address</a>
  </td></tr>
</table>
<p style="margin:0 0 12px;font-size:13px;color:#95a9c0;line-height:1.6;">This link expires in 24 hours. If you did not create an account, ignore this email.</p>
<p style="margin:0;font-size:11px;color:rgba(149,169,192,0.55);word-break:break-all;">Or copy: ' . $safeLink . '</p>';

    fw_send_email($toAddress, $toName, 'Verify your Frowear account', fw_mail_wrap('Verify your account', $body));
}

function fw_send_reset_email(string $toAddress, string $toName, string $rawToken): void
{
    $link      = fw_mail_base_url() . '/api/auth/reset.php?token=' . urlencode($rawToken);
    $firstName = $toName !== '' ? htmlspecialchars($toName, ENT_QUOTES, 'UTF-8') : 'there';
    $safeLink  = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');

    $body = '
<h1 style="margin:0 0 16px;font-size:22px;font-weight:600;color:#f5fbff;">Reset your password</h1>
<p style="margin:0 0 16px;font-size:15px;color:#95a9c0;line-height:1.65;">Hi ' . $firstName . ', we received a reset request for your account. This link expires in <strong style="color:#f5fbff;">1 hour</strong>.</p>
<table cellpadding="0" cellspacing="0" style="margin:28px 0;">
  <tr><td style="background:#4be7ff;border-radius:6px;">
    <a href="' . $safeLink . '" style="display:inline-block;padding:13px 30px;font-size:14px;font-weight:700;color:#040914;text-decoration:none;letter-spacing:0.03em;">Reset Password</a>
  </td></tr>
</table>
<p style="margin:0 0 12px;font-size:13px;color:#95a9c0;line-height:1.6;">If you did not request this, no action is needed.</p>
<p style="margin:0;font-size:11px;color:rgba(149,169,192,0.55);word-break:break-all;">Or copy: ' . $safeLink . '</p>';

    fw_send_email($toAddress, $toName, 'Reset your Frowear password', fw_mail_wrap('Reset your password', $body));
}

function fw_send_notification_email(string $toAddress, string $toName, string $title, string $body): void
{
    $firstName     = $toName !== '' ? htmlspecialchars($toName, ENT_QUOTES, 'UTF-8') : 'there';
    $safeTitle     = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $formattedBody = nl2br(htmlspecialchars($body, ENT_QUOTES, 'UTF-8'));

    $html = '
<h1 style="margin:0 0 16px;font-size:22px;font-weight:600;color:#f5fbff;">' . $safeTitle . '</h1>
<p style="margin:0 0 16px;font-size:15px;color:#95a9c0;line-height:1.65;">Hi ' . $firstName . ',</p>
<div style="font-size:15px;color:#95a9c0;line-height:1.7;">' . $formattedBody . '</div>';

    fw_send_email($toAddress, $toName, $title, fw_mail_wrap($title, $html));
}
