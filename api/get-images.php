<?php
/**
 * GNARLY MTB NEPAL - Get Active Images API
 * Returns slots registry and dynamic gallery items as JSON
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../data/config.php';

$registry = get_images_registry();

// Add storage stats if requested by admin
$includeStats = isset($_GET['stats']) && is_admin_logged_in();

$response = [
    'success' => true,
    'slots' => $registry['slots'] ?? [],
    'gallery' => $registry['gallery'] ?? []
];

if ($includeStats) {
    $uploadFiles = glob(UPLOADS_DIR . '/*.*');
    $totalSize = 0;
    $fileCount = 0;
    if ($uploadFiles) {
        foreach ($uploadFiles as $file) {
            if (is_file($file)) {
                $totalSize += filesize($file);
                $fileCount++;
            }
        }
    }
    $response['stats'] = [
        'total_files' => $fileCount,
        'total_size_bytes' => $totalSize,
        'total_size_mb' => round($totalSize / (1024 * 1024), 2)
    ];
}

echo json_encode($response, JSON_UNESCAPED_SLASHES);
