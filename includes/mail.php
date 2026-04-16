<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

function fw_send_email(string $toAddress, string $toName, string $subject, string $htmlBody): bool
{
    $fromAddress = env_value('MAIL_FROM_ADDRESS', 'hello@frowear.com') ?? 'hello@frowear.com';
    $fromName    = env_value('MAIL_FROM_NAME', 'Frowear Productions') ?? 'Frowear Productions';

    $encodedFrom = '=?UTF-8?B?' . base64_encode($fromName) . '?=';
    $encodedTo   = '=?UTF-8?B?' . base64_encode($toName) . '?=';
    $encodedSubj = '=?UTF-8?B?' . base64_encode($subject) . '?=';

    $to = $toName !== '' ? $encodedTo . ' <' . $toAddress . '>' : $toAddress;

    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";
    $headers .= 'From: ' . $encodedFrom . ' <' . $fromAddress . '>' . "\r\n";
    $headers .= 'Reply-To: ' . $encodedFrom . ' <' . $fromAddress . '>' . "\r\n";
    $headers .= 'X-Mailer: Frowear/1.0' . "\r\n";

    return mail($to, $encodedSubj, $htmlBody, $headers);
}

function fw_mail_base_url(): string
{
    return env_value('APP_URL', 'https://frowear.com') ?? 'https://frowear.com';
}

function fw_send_verification_email(string $toAddress, string $toName, string $rawToken): void
{
    $link      = fw_mail_base_url() . '/api/auth/verify.php?token=' . urlencode($rawToken);
    $firstName = $toName !== '' ? htmlspecialchars($toName, ENT_QUOTES, 'UTF-8') : 'there';

    $html = '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verify your Frowear account</title>
</head>
<body style="margin:0;padding:0;background-color:#040914;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#040914;padding:40px 20px;">
  <tr>
    <td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background-color:#081224;border:1px solid rgba(104,199,255,0.2);border-radius:8px;overflow:hidden;">
        <tr>
          <td style="padding:32px 40px 24px;border-bottom:1px solid rgba(104,199,255,0.2);">
            <span style="font-size:22px;font-weight:700;color:#4be7ff;letter-spacing:0.04em;">FROWEAR</span>
            <span style="font-size:13px;color:#95a9c0;margin-left:10px;">Productions</span>
          </td>
        </tr>
        <tr>
          <td style="padding:40px;">
            <h1 style="margin:0 0 16px;font-size:24px;font-weight:600;color:#f5fbff;line-height:1.3;">Verify your account</h1>
            <p style="margin:0 0 24px;font-size:15px;color:#95a9c0;line-height:1.6;">Hi ' . $firstName . ',</p>
            <p style="margin:0 0 24px;font-size:15px;color:#95a9c0;line-height:1.6;">Thanks for joining Frowear Productions. Click the button below to verify your email address and activate your account.</p>
            <table cellpadding="0" cellspacing="0" style="margin:32px 0;">
              <tr>
                <td style="background-color:#4be7ff;border-radius:6px;">
                  <a href="' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;padding:14px 32px;font-size:15px;font-weight:600;color:#040914;text-decoration:none;letter-spacing:0.02em;">Verify Email Address</a>
                </td>
              </tr>
            </table>
            <p style="margin:0 0 16px;font-size:13px;color:#95a9c0;line-height:1.6;">This link expires in 24 hours. If you did not create an account, you can safely ignore this email.</p>
            <p style="margin:0;font-size:12px;color:rgba(149,169,192,0.6);line-height:1.6;word-break:break-all;">Or copy this link: ' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '</p>
          </td>
        </tr>
        <tr>
          <td style="padding:24px 40px;border-top:1px solid rgba(104,199,255,0.2);">
            <p style="margin:0;font-size:12px;color:rgba(149,169,192,0.5);line-height:1.6;">&copy; Frowear Productions &mdash; hello@frowear.com</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>';

    fw_send_email($toAddress, $toName, 'Verify your Frowear account', $html);
}

function fw_send_reset_email(string $toAddress, string $toName, string $rawToken): void
{
    $link      = fw_mail_base_url() . '/api/auth/reset.php?token=' . urlencode($rawToken);
    $firstName = $toName !== '' ? htmlspecialchars($toName, ENT_QUOTES, 'UTF-8') : 'there';

    $html = '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset your Frowear password</title>
</head>
<body style="margin:0;padding:0;background-color:#040914;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#040914;padding:40px 20px;">
  <tr>
    <td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background-color:#081224;border:1px solid rgba(104,199,255,0.2);border-radius:8px;overflow:hidden;">
        <tr>
          <td style="padding:32px 40px 24px;border-bottom:1px solid rgba(104,199,255,0.2);">
            <span style="font-size:22px;font-weight:700;color:#4be7ff;letter-spacing:0.04em;">FROWEAR</span>
            <span style="font-size:13px;color:#95a9c0;margin-left:10px;">Productions</span>
          </td>
        </tr>
        <tr>
          <td style="padding:40px;">
            <h1 style="margin:0 0 16px;font-size:24px;font-weight:600;color:#f5fbff;line-height:1.3;">Reset your password</h1>
            <p style="margin:0 0 24px;font-size:15px;color:#95a9c0;line-height:1.6;">Hi ' . $firstName . ',</p>
            <p style="margin:0 0 24px;font-size:15px;color:#95a9c0;line-height:1.6;">We received a request to reset your Frowear password. Click the button below to choose a new password. This link expires in <strong style="color:#f5fbff;">1 hour</strong>.</p>
            <table cellpadding="0" cellspacing="0" style="margin:32px 0;">
              <tr>
                <td style="background-color:#4be7ff;border-radius:6px;">
                  <a href="' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;padding:14px 32px;font-size:15px;font-weight:600;color:#040914;text-decoration:none;letter-spacing:0.02em;">Reset Password</a>
                </td>
              </tr>
            </table>
            <p style="margin:0 0 16px;font-size:13px;color:#95a9c0;line-height:1.6;">If you did not request a password reset, no action is needed and your password will remain unchanged.</p>
            <p style="margin:0;font-size:12px;color:rgba(149,169,192,0.6);line-height:1.6;word-break:break-all;">Or copy this link: ' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '</p>
          </td>
        </tr>
        <tr>
          <td style="padding:24px 40px;border-top:1px solid rgba(104,199,255,0.2);">
            <p style="margin:0;font-size:12px;color:rgba(149,169,192,0.5);line-height:1.6;">&copy; Frowear Productions &mdash; hello@frowear.com</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>';

    fw_send_email($toAddress, $toName, 'Reset your Frowear password', $html);
}

function fw_send_notification_email(string $toAddress, string $toName, string $title, string $body): void
{
    $firstName    = $toName !== '' ? htmlspecialchars($toName, ENT_QUOTES, 'UTF-8') : 'there';
    $safeTitle    = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $formattedBody = nl2br(htmlspecialchars($body, ENT_QUOTES, 'UTF-8'));

    $html = '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>' . $safeTitle . '</title>
</head>
<body style="margin:0;padding:0;background-color:#040914;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#040914;padding:40px 20px;">
  <tr>
    <td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background-color:#081224;border:1px solid rgba(104,199,255,0.2);border-radius:8px;overflow:hidden;">
        <tr>
          <td style="padding:32px 40px 24px;border-bottom:1px solid rgba(104,199,255,0.2);">
            <span style="font-size:22px;font-weight:700;color:#4be7ff;letter-spacing:0.04em;">FROWEAR</span>
            <span style="font-size:13px;color:#95a9c0;margin-left:10px;">Productions</span>
          </td>
        </tr>
        <tr>
          <td style="padding:40px;">
            <h1 style="margin:0 0 16px;font-size:24px;font-weight:600;color:#f5fbff;line-height:1.3;">' . $safeTitle . '</h1>
            <p style="margin:0 0 24px;font-size:15px;color:#95a9c0;line-height:1.6;">Hi ' . $firstName . ',</p>
            <div style="font-size:15px;color:#95a9c0;line-height:1.7;">' . $formattedBody . '</div>
          </td>
        </tr>
        <tr>
          <td style="padding:24px 40px;border-top:1px solid rgba(104,199,255,0.2);">
            <p style="margin:0;font-size:12px;color:rgba(149,169,192,0.5);line-height:1.6;">&copy; Frowear Productions &mdash; hello@frowear.com</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>';

    fw_send_email($toAddress, $toName, $title, $html);
}
