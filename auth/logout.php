<?php
define('APP_RUNNING', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$logoutUserId = (int) ($_SESSION['user_id'] ?? 0);

if ($logoutUserId > 0) {
    $emptyToken  = null;
    $stmtClear   = $conn->prepare('UPDATE users SET remember_token = ? WHERE id = ?');
    $stmtClear->bind_param('si', $emptyToken, $logoutUserId);
    $stmtClear->execute();
    $stmtClear->close();
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $cookieParams = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $cookieParams['path'],
        $cookieParams['domain'],
        $cookieParams['secure'],
        $cookieParams['httponly']
    );
}

session_destroy();

setcookie('remember_user', '', time() - 3600, '/');

header('Location: login.php?logged_out=1');
exit;
