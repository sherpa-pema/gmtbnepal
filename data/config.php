<?php
/**
 * GNARLY MTB NEPAL - Backend Configuration & Auth Helper
 */

// Prevent direct execution if accessed improperly
if (!defined('GNARLY_BACKEND_INIT')) {
    define('GNARLY_BACKEND_INIT', true);
}

// Directory constants
define('ROOT_DIR', dirname(__DIR__));
define('DATA_DIR', ROOT_DIR . '/data');
define('UPLOADS_DIR', ROOT_DIR . '/uploads');
define('IMAGES_JSON_FILE', DATA_DIR . '/images.json');
define('CREDENTIALS_FILE', DATA_DIR . '/credentials.json');

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    if (!headers_sent()) {
        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
        
        session_set_cookie_params([
            'lifetime' => 86400 * 7, // 7 days
            'path' => '/',
            'domain' => '',
            'secure' => $isSecure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        
        session_name('GNARLY_SESSION_ID');
        session_start();
    }
}

/**
 * Get or initialize admin credentials
 */
function get_admin_credentials() {
    if (file_exists(CREDENTIALS_FILE)) {
        $content = file_get_contents(CREDENTIALS_FILE);
        $data = json_decode($content, true);
        if ($data && !empty($data['username']) && !empty($data['password_hash'])) {
            return $data;
        }
    }

    // Default credentials on initial setup: admin / gnarlymtb2026
    $defaultData = [
        'username' => 'admin',
        'password_hash' => password_hash('gnarlymtb2026', PASSWORD_DEFAULT),
        'created_at' => date('c'),
        'updated_at' => date('c')
    ];
    file_put_contents(CREDENTIALS_FILE, json_encode($defaultData, JSON_PRETTY_PRINT));
    return $defaultData;
}

/**
 * Verify username & password
 */
function verify_login($username, $password) {
    $creds = get_admin_credentials();
    if (trim($username) !== $creds['username']) {
        return false;
    }
    return password_verify($password, $creds['password_hash']);
}

/**
 * Update username & password
 */
function update_admin_credentials($newUsername, $newPassword) {
    $data = [
        'username' => trim($newUsername),
        'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
        'updated_at' => date('c')
    ];
    return file_put_contents(CREDENTIALS_FILE, json_encode($data, JSON_PRETTY_PRINT)) !== false;
}

/**
 * Check if the user is currently authenticated
 */
function is_admin_logged_in() {
    return !empty($_SESSION['gnarly_admin_logged_in']) && $_SESSION['gnarly_admin_logged_in'] === true;
}

/**
 * Protect page: redirect to login if not authenticated
 */
function require_admin_login($redirectUrl = 'login.php') {
    if (!is_admin_logged_in()) {
        header("Location: " . $redirectUrl);
        exit;
    }
}

/**
 * CSRF token helpers
 */
function get_csrf_token() {
    if (empty($_SESSION['gnarly_csrf_token'])) {
        $_SESSION['gnarly_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['gnarly_csrf_token'];
}

function verify_csrf_token($token) {
    return !empty($_SESSION['gnarly_csrf_token']) && hash_equals($_SESSION['gnarly_csrf_token'], (string)$token);
}

/**
 * Load images.json registry
 */
function get_images_registry() {
    if (!file_exists(IMAGES_JSON_FILE)) {
        return ['slots' => [], 'gallery' => []];
    }
    $content = file_get_contents(IMAGES_JSON_FILE);
    $data = json_decode($content, true);
    return is_array($data) ? $data : ['slots' => [], 'gallery' => []];
}

/**
 * Save images.json registry
 */
function save_images_registry($data) {
    return file_put_contents(IMAGES_JSON_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false;
}
