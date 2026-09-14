<?php
/**
 * GNARLY MTB NEPAL - Delete & Reset Image API
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

// 2. CSRF check
$csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!verify_csrf_token($csrfToken)) {
    json_err('Invalid or expired security token. Please refresh the page.', 403);
}

$registry = get_images_registry();
$slotKey = trim($_POST['slot_key'] ?? '');
$galleryId = trim($_POST['gallery_id'] ?? '');
$action = trim($_POST['action'] ?? '');

// -----------------------------------------------------------------
// ACTION: CLEAN ORPHAN FILES
// -----------------------------------------------------------------
if ($action === 'clean_orphans') {
    $activeFiles = [];
    foreach ($registry['slots'] as $slot) {
        if (!empty($slot['url']) && str_starts_with($slot['url'], 'uploads/')) {
            $activeFiles[] = basename($slot['url']);
        }
    }
    if (!empty($registry['gallery']) && is_array($registry['gallery'])) {
        foreach ($registry['gallery'] as $item) {
            if (!empty($item['url']) && str_starts_with($item['url'], 'uploads/')) {
                $activeFiles[] = basename($item['url']);
            }
        }
    }

    $allUploads = glob(UPLOADS_DIR . '/*.*');
    $removedCount = 0;
    if ($allUploads) {
        foreach ($allUploads as $file) {
            $base = basename($file);
            if (!in_array($base, $activeFiles, true) && $base !== '.htaccess') {
                if (@unlink($file)) {
                    $removedCount++;
                }
            }
        }
    }

    echo json_encode([
        'success' => true,
        'message' => "Cleaned {$removedCount} orphaned upload file(s)."
    ], JSON_UNESCAPED_SLASHES);
    exit;
}

// -----------------------------------------------------------------
// ACTION: RESET SLOT TO DEFAULT ASSET
// -----------------------------------------------------------------
if (!empty($slotKey)) {
    $cleanSlotKey = preg_replace('/[^a-zA-Z0-9_-]/', '', $slotKey);
    if (!isset($registry['slots'][$cleanSlotKey])) {
        json_err('Unknown slot key: ' . htmlspecialchars($slotKey));
    }

    $currentSlot = $registry['slots'][$cleanSlotKey];

    // Delete custom uploaded file if present
    if (!empty($currentSlot['url']) && str_starts_with($currentSlot['url'], 'uploads/')) {
        $cleanFilename = basename($currentSlot['url']);
        $filePath = UPLOADS_DIR . '/' . $cleanFilename;
        if ($cleanFilename !== '.htaccess' && file_exists($filePath) && is_file($filePath)) {
            @unlink($filePath);
        }
    }

    // Reset back to empty URL (frontend will use default asset)
    $registry['slots'][$cleanSlotKey]['url'] = '';
    $registry['slots'][$cleanSlotKey]['updated_at'] = null;

    save_images_registry($registry);

    echo json_encode([
        'success' => true,
        'message' => 'Image reset to original website default!',
        'slot_key' => $cleanSlotKey,
        'default_url' => $registry['slots'][$cleanSlotKey]['default']
    ], JSON_UNESCAPED_SLASHES);
    exit;
}

// -----------------------------------------------------------------
// ACTION: DELETE DYNAMIC GALLERY ITEM
// -----------------------------------------------------------------
if (!empty($galleryId)) {
    if (!isset($registry['gallery']) || !is_array($registry['gallery'])) {
        json_err('No gallery items exist.');
    }

    $foundIndex = -1;
    foreach ($registry['gallery'] as $index => $item) {
        if ($item['id'] === $galleryId) {
            $foundIndex = $index;
            break;
        }
    }

    if ($foundIndex === -1) {
        json_err('Gallery image not found.');
    }

    $itemToDelete = $registry['gallery'][$foundIndex];

    // Delete file from disk safely
    if (!empty($itemToDelete['url']) && str_starts_with($itemToDelete['url'], 'uploads/')) {
        $cleanFilename = basename($itemToDelete['url']);
        $filePath = UPLOADS_DIR . '/' . $cleanFilename;
        if ($cleanFilename !== '.htaccess' && file_exists($filePath) && is_file($filePath)) {
            @unlink($filePath);
        }
    }

    // Remove from array and re-index
    array_splice($registry['gallery'], $foundIndex, 1);
    save_images_registry($registry);

    echo json_encode([
        'success' => true,
        'message' => 'Gallery photo deleted successfully!',
        'gallery_id' => $galleryId
    ], JSON_UNESCAPED_SLASHES);
    exit;
}

json_err('No valid target specified for deletion.');
