<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Method Not Allowed</title></head><body><p>Method not allowed.</p></body></html>';
    exit;
}

$token = trim($_GET['token'] ?? '');

if ($token === '') {
    http_response_code(400);
    echo '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invalid Verification Link - Frowear</title>
<style>
  body{margin:0;padding:0;background:#040914;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;color:#f5fbff;display:flex;align-items:center;justify-content:center;min-height:100vh;}
  .card{background:#081224;border:1px solid rgba(104,199,255,0.2);border-radius:8px;padding:48px 40px;max-width:480px;width:100%;text-align:center;}
  .brand{font-size:22px;font-weight:700;color:#4be7ff;letter-spacing:.04em;margin-bottom:32px;}
  h1{font-size:22px;font-weight:600;margin:0 0 16px;color:#ff6ad5;}
  p{font-size:15px;color:#95a9c0;line-height:1.6;margin:0;}
</style>
</head>
<body>
  <div class="card">
    <div class="brand">FROWEAR</div>
    <h1>Invalid or expired verification link.</h1>
    <p>This link has already been used or has expired. Please register again or contact support.</p>
  </div>
</body>
</html>';
    exit;
}

$userId = fw_consume_verification_token($token);

if ($userId === null) {
    http_response_code(400);
    echo '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invalid Verification Link - Frowear</title>
<style>
  body{margin:0;padding:0;background:#040914;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;color:#f5fbff;display:flex;align-items:center;justify-content:center;min-height:100vh;}
  .card{background:#081224;border:1px solid rgba(104,199,255,0.2);border-radius:8px;padding:48px 40px;max-width:480px;width:100%;text-align:center;}
  .brand{font-size:22px;font-weight:700;color:#4be7ff;letter-spacing:.04em;margin-bottom:32px;}
  h1{font-size:22px;font-weight:600;margin:0 0 16px;color:#ff6ad5;}
  p{font-size:15px;color:#95a9c0;line-height:1.6;margin:0;}
</style>
</head>
<body>
  <div class="card">
    <div class="brand">FROWEAR</div>
    <h1>Invalid or expired verification link.</h1>
    <p>This link has already been used or has expired. Please register again or contact support.</p>
  </div>
</body>
</html>';
    exit;
}

fw_mark_email_verified($userId);

http_response_code(200);
echo '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Email Verified - Frowear</title>
<style>
  body{margin:0;padding:0;background:#040914;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;color:#f5fbff;display:flex;align-items:center;justify-content:center;min-height:100vh;}
  .card{background:#081224;border:1px solid rgba(104,199,255,0.2);border-radius:8px;padding:48px 40px;max-width:480px;width:100%;text-align:center;}
  .brand{font-size:22px;font-weight:700;color:#4be7ff;letter-spacing:.04em;margin-bottom:32px;}
  h1{font-size:22px;font-weight:600;margin:0 0 16px;color:#9cff8f;}
  p{font-size:15px;color:#95a9c0;line-height:1.6;margin:0 0 28px;}
  a.btn{display:inline-block;background:#4be7ff;color:#040914;font-size:15px;font-weight:600;padding:13px 32px;border-radius:6px;text-decoration:none;letter-spacing:.02em;}
</style>
</head>
<body>
  <div class="card">
    <div class="brand">FROWEAR</div>
    <h1>Email verified!</h1>
    <p>Your email address has been confirmed. You can now sign in to your account.</p>
    <a class="btn" href="/platform.php">Sign in to Platform</a>
  </div>
</body>
</html>';
exit;
