<?php
define('APP_RUNNING', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';

requireLogin('../../auth/login.php');
requireAdmin('../../user/dashboard.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    setFlashMessage('danger', 'Invalid request token. Please try again.');
    header('Location: index.php');
    exit;
}

$itemId = (int) ($_POST['id'] ?? 0);

if ($itemId <= 0) {
    setFlashMessage('danger', 'Invalid item ID. No item was deleted.');
    header('Location: index.php');
    exit;
}

$stmtFetch = $conn->prepare('SELECT id, name, image_path FROM menu_items WHERE id = ? LIMIT 1');
$stmtFetch->bind_param('i', $itemId);
$stmtFetch->execute();
$resultFetch = $stmtFetch->get_result();
$item        = $resultFetch->fetch_assoc();
$stmtFetch->close();

if (!$item) {
    setFlashMessage('danger', 'Item not found. It may have already been deleted.');
    header('Location: index.php');
    exit;
}

$stmtDelete = $conn->prepare('DELETE FROM menu_items WHERE id = ?');
$stmtDelete->bind_param('i', $itemId);
$stmtDelete->execute();

if ($stmtDelete->affected_rows === 0) {
    $stmtDelete->close();
    setFlashMessage('danger', 'No item was deleted. It may have already been removed.');
    header('Location: index.php');
    exit;
}

$stmtDelete->close();

if (!empty($item['image_path'])) {
    $absoluteImagePath = __DIR__ . '/../../' . $item['image_path'];
    if (file_exists($absoluteImagePath)) {
        unlink($absoluteImagePath);
    }
}

$deletedName = htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8');
setFlashMessage('success', 'Menu item "' . $deletedName . '" was deleted successfully.');
header('Location: index.php');
exit;
