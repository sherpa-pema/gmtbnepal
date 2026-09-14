<?php
/**
 * GNARLY MTB NEPAL - Image Upload API
 * Handles file validation, automatic old file cleanup, and registry updates
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../data/config.php';

function json_err($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $message], JSON_UNESCAPED_SLASHES);
    exit;
}

// 1. Authentication check
if (!is_admin_logged_in()) {
    json_err('Unauthorized. Please log in first.', 401);
}

// 2. CSRF token check
$csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!verify_csrf_token($csrfToken)) {
    json_err('Invalid or expired security token. Please refresh the page.', 403);
}

// 3. Validate uploaded file presence
if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $errCode = $_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE;
    $errMap = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds PHP upload_max_filesize directive.',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE specified in form.',
        UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder on server.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.'
    ];
    json_err($errMap[$errCode] ?? 'File upload failed with error code: ' . $errCode);
}

$file = $_FILES['image'];

// Max size: 15MB
$maxSize = 15 * 1024 * 1024;
if ($file['size'] > $maxSize) {
    json_err('File size exceeds the 15MB limit.');
}

// Validate file extension
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
if (!in_array($ext, $allowedExts, true)) {
    json_err('Invalid file format. Only JPG, JPEG, PNG, and WebP images are allowed.');
}

// Validate MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
if (!in_array($mime, $allowedMimes, true)) {
    json_err('Invalid image MIME type (' . htmlspecialchars($mime) . '). Upload rejected.');
}

// Ensure uploads folder exists and is writable
if (!is_dir(UPLOADS_DIR)) {
    mkdir(UPLOADS_DIR, 0755, true);
}
if (!is_writable(UPLOADS_DIR)) {
    json_err('Uploads directory is not writable. Please check server folder permissions.');
}

$registry = get_images_registry();
$slotKey = trim($_POST['slot_key'] ?? '');
$targetType = trim($_POST['type'] ?? '');

if (!empty($slotKey)) {
    // -------------------------------------------------------------
    // CASE A: REPLACE PAGE SLOT IMAGE
    // -------------------------------------------------------------
    $cleanSlotKey = preg_replace('/[^a-zA-Z0-9_-]/', '', $slotKey);
    if (!isset($registry['slots'][$cleanSlotKey])) {
        json_err('Unknown image slot: ' . htmlspecialchars($slotKey));
    }

    $currentSlot = $registry['slots'][$cleanSlotKey];

    // AUTOMATIC DISK CLEANUP: If this slot had a previous custom upload, delete it from disk!
    if (!empty($currentSlot['url']) && str_starts_with($currentSlot['url'], 'uploads/')) {
        $oldFilename = basename($currentSlot['url']);
        $oldFilePath = UPLOADS_DIR . '/' . $oldFilename;
        if ($oldFilename !== '.htaccess' && file_exists($oldFilePath) && is_file($oldFilePath)) {
            @unlink($oldFilePath);
        }
    }

    // Generate safe unique filename
    $newFilename = 'slot_' . $cleanSlotKey . '_' . time() . '_' . substr(bin2hex(random_bytes(4)), 0, 8) . '.' . $ext;
    $destination = UPLOADS_DIR . '/' . $newFilename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        json_err('Failed to save uploaded file to server storage.', 500);
    }

    // Update slot registry
    $relativeUrl = 'uploads/' . $newFilename;
    $registry['slots'][$cleanSlotKey]['url'] = $relativeUrl;
    $registry['slots'][$cleanSlotKey]['updated_at'] = date('c');

    if (!empty($_POST['alt'])) {
        $registry['slots'][$cleanSlotKey]['alt'] = strip_tags(trim($_POST['alt']));
    }

    save_images_registry($registry);

    echo json_encode([
        'success' => true,
        'message' => 'Image updated successfully!',
        'slot_key' => $cleanSlotKey,
        'url' => $relativeUrl,
        'alt' => $registry['slots'][$cleanSlotKey]['alt']
    ], JSON_UNESCAPED_SLASHES);
    exit;

} else {
    // -------------------------------------------------------------
    // CASE B: GALLERY PHOTO UPLOAD
    // -------------------------------------------------------------
    $newFilename = 'gallery_' . time() . '_' . substr(bin2hex(random_bytes(4)), 0, 8) . '.' . $ext;
    $destination = UPLOADS_DIR . '/' . $newFilename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        json_err('Failed to save gallery photo to server storage.', 500);
    }

    $relativeUrl = 'uploads/' . $newFilename;
    $title = strip_tags(trim($_POST['title'] ?? 'Tour Gallery Dispatch'));
    $category = strip_tags(trim($_POST['category'] ?? 'Tours 2024'));
    $alt = strip_tags(trim($_POST['alt'] ?? $title));
    $itemId = 'gal_' . time() . '_' . substr(bin2hex(random_bytes(3)), 0, 6);

    $newItem = [
        'id' => $itemId,
        'url' => $relativeUrl,
        'title' => $title,
        'category' => $category,
        'alt' => $alt,
        'created_at' => date('c')
    ];

    if (!isset($registry['gallery']) || !is_array($registry['gallery'])) {
        $registry['gallery'] = [];
    }

    // Prepend to show newest photos first
    array_unshift($registry['gallery'], $newItem);
    save_images_registry($registry);

    echo json_encode([
        'success' => true,
        'message' => 'Gallery photo uploaded successfully!',
        'item' => $newItem
    ], JSON_UNESCAPED_SLASHES);
    exit;
}
