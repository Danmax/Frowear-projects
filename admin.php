<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    json_response([
        'ok' => true,
        'authenticated' => is_admin_authenticated(),
        'content' => get_site_content(),
    ]);
}

// Image upload — multipart/form-data, not JSON.
if ($method === 'POST' && !empty($_FILES['file'])) {
    require_admin();

    $file = $_FILES['file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE   => 'File exceeds the server upload size limit.',
            UPLOAD_ERR_FORM_SIZE  => 'File exceeds the form size limit.',
            UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was received.',
            UPLOAD_ERR_NO_TMP_DIR => 'Server temporary directory is missing.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION  => 'Upload blocked by a server extension.',
        ];
        json_response(['ok' => false, 'message' => $uploadErrors[$file['error']] ?? 'Upload failed.'], 422);
    }

    if ($file['size'] > 716_800) {
        json_response(['ok' => false, 'message' => 'File exceeds the 700 KB limit.'], 422);
    }

    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mime     = $finfo->file($file['tmp_name']);
    $allowed  = ['image/webp' => 'webp', 'image/jpeg' => 'jpg', 'image/png' => 'png'];

    if (!array_key_exists($mime, $allowed)) {
        json_response(['ok' => false, 'message' => 'Only WebP, JPEG, and PNG images are accepted.'], 422);
    }

    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
        json_response(['ok' => false, 'message' => 'Upload directory is unavailable.'], 500);
    }

    $filename    = bin2hex(random_bytes(12)) . '.' . $allowed[$mime];
    $destination = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        json_response(['ok' => false, 'message' => 'Failed to save the uploaded file.'], 500);
    }

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $url    = $scheme . '://' . $host . '/uploads/' . $filename;

    json_response(['ok' => true, 'url' => $url]);
}

$payload = request_json();
$action = (string) ($payload['action'] ?? '');

if ($action === 'login') {
    $configuredKey = admin_key();
    $providedKey = (string) ($payload['key'] ?? '');

    if ($configuredKey === '') {
        json_response(['ok' => false, 'message' => 'FW_ADMIN_KEY is not configured on the server.'], 500);
    }

    if (!hash_equals($configuredKey, $providedKey)) {
        json_response(['ok' => false, 'message' => 'Admin key is invalid.'], 401);
    }

    $_SESSION[FW_ADMIN_SESSION_KEY] = true;
    json_response([
        'ok' => true,
        'authenticated' => true,
        'content' => get_site_content(),
    ]);
}

if ($action === 'logout') {
    unset($_SESSION[FW_ADMIN_SESSION_KEY]);
    json_response(['ok' => true, 'authenticated' => false]);
}

if ($action === 'save') {
    require_admin();

    $content = $payload['content'] ?? null;
    if (!is_array($content)) {
        json_response(['ok' => false, 'message' => 'Invalid content payload.'], 422);
    }

    $merged = merge_deep(default_site_content(), $content);
    save_site_content($merged);

    json_response([
        'ok' => true,
        'message' => 'Content saved.',
        'content' => $merged,
    ]);
}

if ($action === 'reset') {
    require_admin();
    $defaults = default_site_content();
    save_site_content($defaults);

    json_response([
        'ok' => true,
        'message' => 'Default content restored.',
        'content' => $defaults,
    ]);
}

json_response(['ok' => false, 'message' => 'Unsupported action.'], 400);
