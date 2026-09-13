<?php
/**
 * GNARLY MTB NEPAL - Authentication API
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../data/config.php';

// Helper function to return JSON response
function json_response($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit;
}

// Parse request payload
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($method === 'POST') {
    $rawInput = file_get_contents('php://input');
    $postJson = json_decode($rawInput, true);
    if (is_array($postJson)) {
        $_POST = array_merge($_POST, $postJson);
    }
    if (empty($action) && isset($_POST['action'])) {
        $action = $_POST['action'];
    }
}

// Handle actions
switch ($action) {
    case 'login':
        $username = trim($_POST['username'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            json_response(['success' => false, 'error' => 'Please enter both username and password.'], 400);
        }

        if (verify_login($username, $password)) {
            session_regenerate_id(true);
            $_SESSION['gnarly_admin_logged_in'] = true;
            $_SESSION['gnarly_admin_username'] = $username;
            $_SESSION['gnarly_login_time'] = time();

            json_response([
                'success' => true,
                'message' => 'Login successful',
                'csrf_token' => get_csrf_token()
            ]);
        } else {
            // Anti brute-force delay
            usleep(300000);
            json_response(['success' => false, 'error' => 'Invalid username or password.'], 401);
        }
        break;

    case 'logout':
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        json_response(['success' => true, 'message' => 'Logged out successfully']);
        break;

    case 'status':
        $loggedIn = is_admin_logged_in();
        json_response([
            'logged_in' => $loggedIn,
            'username' => $loggedIn ? ($_SESSION['gnarly_admin_username'] ?? 'admin') : null,
            'csrf_token' => $loggedIn ? get_csrf_token() : null
        ]);
        break;

    case 'change_credentials':
        if (!is_admin_logged_in()) {
            json_response(['success' => false, 'error' => 'Unauthorized. Please log in first.'], 401);
        }

        $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!verify_csrf_token($csrfToken)) {
            json_response(['success' => false, 'error' => 'Invalid or expired security token. Please refresh.'], 403);
        }

        $currentPassword = (string)($_POST['current_password'] ?? '');
        $newUsername = trim($_POST['new_username'] ?? '');
        $newPassword = (string)($_POST['new_password'] ?? '');

        if (empty($currentPassword) || empty($newUsername) || empty($newPassword)) {
            json_response(['success' => false, 'error' => 'All credential fields are required.'], 400);
        }

        if (strlen($newPassword) < 6) {
            json_response(['success' => false, 'error' => 'New password must be at least 6 characters long.'], 400);
        }

        $currentAdmin = get_admin_credentials();
        if (!password_verify($currentPassword, $currentAdmin['password_hash'])) {
            json_response(['success' => false, 'error' => 'Current password is incorrect.'], 400);
        }

        if (update_admin_credentials($newUsername, $newPassword)) {
            $_SESSION['gnarly_admin_username'] = $newUsername;
            json_response(['success' => true, 'message' => 'Admin credentials updated successfully!']);
        } else {
            json_response(['success' => false, 'error' => 'Failed to write updated credentials. Check folder permissions.'], 500);
        }
        break;

    default:
        json_response(['success' => false, 'error' => 'Invalid action specified.'], 400);
}
