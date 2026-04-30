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

function requireUser(string $redirectTo = '../auth/login.php'): void
{
    requireLogin($redirectTo);

    if (isAdmin()) {
        header('Location: ../admin/dashboard.php');
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

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function requireValidCsrf(string $redirectTo): void
{
    if (
        $_SERVER['REQUEST_METHOD'] === 'POST'
        && (
            empty($_POST['csrf_token'])
            || !hash_equals(csrfToken(), $_POST['csrf_token'])
        )
    ) {
        setFlashMessage('danger', 'Invalid request token. Please try again.');
        header('Location: ' . $redirectTo);
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
