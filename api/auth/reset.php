<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET' && $method !== 'POST') {
    http_response_code(405);
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Method Not Allowed</title></head><body><p>Method not allowed.</p></body></html>';
    exit;
}

// ── Shared HTML helpers ─────────────────────────────────────────────────────

function fw_reset_html_head(string $title): string
{
    return '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . ' - Frowear</title>
<style>
  *{box-sizing:border-box;}
  body{margin:0;padding:0;background:#040914;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;color:#f5fbff;display:flex;align-items:center;justify-content:center;min-height:100vh;}
  .card{background:#081224;border:1px solid rgba(104,199,255,0.2);border-radius:8px;padding:48px 40px;max-width:480px;width:100%;}
  .brand{font-size:22px;font-weight:700;color:#4be7ff;letter-spacing:.04em;margin-bottom:32px;}
  h1{font-size:22px;font-weight:600;margin:0 0 16px;}
  p{font-size:15px;color:#95a9c0;line-height:1.6;margin:0 0 24px;}
  label{display:block;font-size:13px;color:#95a9c0;margin-bottom:6px;}
  input[type=password]{width:100%;background:#040914;border:1px solid rgba(104,199,255,0.25);border-radius:5px;color:#f5fbff;font-size:15px;padding:11px 14px;outline:none;margin-bottom:20px;}
  input[type=password]:focus{border-color:#4be7ff;}
  button{width:100%;background:#4be7ff;color:#040914;font-size:15px;font-weight:600;padding:13px;border:none;border-radius:6px;cursor:pointer;letter-spacing:.02em;}
  button:hover{background:#7aefff;}
  .error{color:#ff6ad5;font-size:14px;margin-bottom:16px;}
  a.btn{display:inline-block;background:#4be7ff;color:#040914;font-size:15px;font-weight:600;padding:13px 32px;border-radius:6px;text-decoration:none;letter-spacing:.02em;margin-top:8px;}
  .success-head{color:#9cff8f;}
  .error-head{color:#ff6ad5;}
</style>
</head>
<body>
  <div class="card">
    <div class="brand">FROWEAR</div>';
}

function fw_reset_html_foot(): string
{
    return '  </div>
</body>
</html>';
}

// ── GET: render the password reset form ─────────────────────────────────────

if ($method === 'GET') {
    $token = trim($_GET['token'] ?? '');

    if ($token === '') {
        http_response_code(400);
        echo fw_reset_html_head('Invalid Reset Link');
        echo '    <h1 class="error-head">Invalid or expired reset link.</h1>
    <p>No token was provided. Please request a new password reset from the sign-in page.</p>';
        echo fw_reset_html_foot();
        exit;
    }

    $safeToken = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');

    echo fw_reset_html_head('Reset Your Password');
    echo '    <h1>Reset your password</h1>
    <p>Enter a new password for your account. Your password must be at least 8 characters long.</p>
    <form method="POST" action="/api/auth/reset.php">
      <input type="hidden" name="token" value="' . $safeToken . '">
      <label for="password">New password</label>
      <input type="password" id="password" name="password" minlength="8" required placeholder="Minimum 8 characters">
      <label for="password_confirm">Confirm new password</label>
      <input type="password" id="password_confirm" name="password_confirm" minlength="8" required placeholder="Repeat your password">
      <button type="submit">Update Password</button>
    </form>';
    echo fw_reset_html_foot();
    exit;
}

// ── POST: process the submitted form ────────────────────────────────────────

$token           = trim($_POST['token'] ?? '');
$password        = (string) ($_POST['password'] ?? '');
$passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

if ($token === '') {
    http_response_code(400);
    echo fw_reset_html_head('Invalid Reset Link');
    echo '    <h1 class="error-head">Invalid or expired reset link.</h1>
    <p>No token was found in your submission. Please request a new password reset.</p>';
    echo fw_reset_html_foot();
    exit;
}

if (strlen($password) < 8) {
    $safeToken = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');
    http_response_code(422);
    echo fw_reset_html_head('Reset Your Password');
    echo '    <h1>Reset your password</h1>
    <p class="error">Password must be at least 8 characters.</p>
    <form method="POST" action="/api/auth/reset.php">
      <input type="hidden" name="token" value="' . $safeToken . '">
      <label for="password">New password</label>
      <input type="password" id="password" name="password" minlength="8" required placeholder="Minimum 8 characters">
      <label for="password_confirm">Confirm new password</label>
      <input type="password" id="password_confirm" name="password_confirm" minlength="8" required placeholder="Repeat your password">
      <button type="submit">Update Password</button>
    </form>';
    echo fw_reset_html_foot();
    exit;
}

if ($password !== $passwordConfirm) {
    $safeToken = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');
    http_response_code(422);
    echo fw_reset_html_head('Reset Your Password');
    echo '    <h1>Reset your password</h1>
    <p class="error">Passwords do not match.</p>
    <form method="POST" action="/api/auth/reset.php">
      <input type="hidden" name="token" value="' . $safeToken . '">
      <label for="password">New password</label>
      <input type="password" id="password" name="password" minlength="8" required placeholder="Minimum 8 characters">
      <label for="password_confirm">Confirm new password</label>
      <input type="password" id="password_confirm" name="password_confirm" minlength="8" required placeholder="Repeat your password">
      <button type="submit">Update Password</button>
    </form>';
    echo fw_reset_html_foot();
    exit;
}

$userId = fw_consume_reset_token($token);

if ($userId === null) {
    http_response_code(400);
    echo fw_reset_html_head('Invalid Reset Link');
    echo '    <h1 class="error-head">Invalid or expired reset link.</h1>
    <p>This link has already been used or has expired. Please request a new password reset from the sign-in page.</p>';
    echo fw_reset_html_foot();
    exit;
}

fw_update_password($userId, $password);

http_response_code(200);
echo fw_reset_html_head('Password Updated');
echo '    <h1 class="success-head">Password updated.</h1>
    <p>Your password has been changed successfully. You can now sign in with your new password.</p>
    <a class="btn" href="/platform.php">Sign in to Platform</a>';
echo fw_reset_html_foot();
exit;
