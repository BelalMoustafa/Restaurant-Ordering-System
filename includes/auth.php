<?php
if (!defined('APP_RUNNING')) {
    http_response_code(403);
    exit('Direct access forbidden.');
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin(): bool
{
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin(string $redirectTo = '../auth/login.php'): void
{
    if (!isLoggedIn()) {
        header('Location: ' . $redirectTo);
        exit;
    }
}

function requireAdmin(string $redirectTo = '../user/dashboard.php'): void
{
    if (!isAdmin()) {
        header('Location: ' . $redirectTo);
        exit;
    }
}

function requireGuest(): void
{
    if (isLoggedIn()) {
        if (isAdmin()) {
            header('Location: ../admin/dashboard.php');
        } else {
            header('Location: ../user/dashboard.php');
        }
        exit;
    }
}

function setFlashMessage(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message,
    ];
}

function getFlashMessage(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}
