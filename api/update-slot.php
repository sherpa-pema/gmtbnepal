<?php
/**
 * GNARLY MTB NEPAL - Update Slot Metadata API (Alt text, captions)
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../data/config.php';

function json_err($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $message], JSON_UNESCAPED_SLASHES);
    exit;
}

if (!is_admin_logged_in()) {
    json_err('Unauthorized. Please log in first.', 401);
}

$csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!verify_csrf_token($csrfToken)) {
    json_err('Invalid or expired security token. Please refresh the page.', 403);
}

$slotKey = trim($_POST['slot_key'] ?? '');
$cleanSlotKey = preg_replace('/[^a-zA-Z0-9_-]/', '', $slotKey);

$registry = get_images_registry();
if (!isset($registry['slots'][$cleanSlotKey])) {
    json_err('Slot not found: ' . htmlspecialchars($slotKey));
}

if (isset($_POST['alt'])) {
    $registry['slots'][$cleanSlotKey]['alt'] = strip_tags(trim($_POST['alt']));
}

save_images_registry($registry);

echo json_encode([
    'success' => true,
    'message' => 'Metadata updated successfully!',
    'slot_key' => $cleanSlotKey,
    'alt' => $registry['slots'][$cleanSlotKey]['alt']
], JSON_UNESCAPED_SLASHES);
