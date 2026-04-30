<?php
if (!defined('APP_RUNNING')) {
    define('APP_RUNNING', true);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/auth.php';

$_depth   = substr_count($_SERVER['PHP_SELF'], '/') - 2;
$basePath = $_depth > 0 ? str_repeat('../', $_depth) : '';

$_currentPath = $_SERVER['PHP_SELF'];

function navActive(string $segment): string
{
    global $_currentPath;
    return (strpos($_currentPath, $segment) !== false) ? ' active' : '';
}

$_flash = getFlashMessage();

$_pageTitle = isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — The Restaurant' : 'The Restaurant';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="The Restaurant — A curated dining experience.">
    <title><?= $_pageTitle ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars($basePath) ?>assets/css/style.css">
</head>
<body>
<div class="page-wrapper">
    <nav class="navbar" role="navigation" aria-label="Main navigation">
        <div class="container">
            <a href="<?= htmlspecialchars($basePath) ?>index.php" class="navbar-brand">
                The Restaurant
            </a>
            <ul class="navbar-nav">
                <?php if (!isLoggedIn()): ?>
                    <li>
                        <a href="<?= htmlspecialchars($basePath) ?>index.php<?= navActive('index.php') ?>">
                            Menu
                        </a>
                    </li>
                    <li>
                        <a href="<?= htmlspecialchars($basePath) ?>auth/login.php"
                           class="<?= trim(navActive('auth/login.php')) ?>">
                            Login
                        </a>
                    </li>
                    <li>
                        <a href="<?= htmlspecialchars($basePath) ?>auth/register.php"
                           class="<?= trim(navActive('auth/register.php')) ?>">
                            Register
                        </a>
                    </li>
                <?php elseif (isAdmin()): ?>
                    <li>
                        <a href="<?= htmlspecialchars($basePath) ?>admin/dashboard.php"
                           class="<?= trim(navActive('admin/dashboard.php')) ?>">
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="<?= htmlspecialchars($basePath) ?>admin/menu_items/index.php"
                           class="<?= trim(navActive('admin/menu_items')) ?>">
                            Menu Items
                        </a>
                    </li>
                    <li>
                        <a href="<?= htmlspecialchars($basePath) ?>admin/orders/index.php"
                           class="<?= trim(navActive('admin/orders')) ?>">
                            Orders
                        </a>
                    </li>
                    <li>
                        <a href="<?= htmlspecialchars($basePath) ?>admin/upload_menu_pdf.php"
                           class="<?= trim(navActive('upload_menu_pdf')) ?>">
                            Upload PDF
                        </a>
                    </li>
                    <li>
                        <a href="<?= htmlspecialchars($basePath) ?>auth/logout.php"
                           class="nav-logout">
                            Logout
                        </a>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="<?= htmlspecialchars($basePath) ?>user/menu.php"
                           class="<?= trim(navActive('user/menu.php')) ?>">
                            Menu
                        </a>
                    </li>
                    <li>
                        <a href="<?= htmlspecialchars($basePath) ?>user/place_order.php"
                           class="<?= trim(navActive('place_order.php')) ?>">
                            Place Order
                        </a>
                    </li>
                    <li>
                        <a href="<?= htmlspecialchars($basePath) ?>user/my_orders.php"
                           class="<?= trim(navActive('my_orders.php')) ?>">
                            My Orders
                        </a>
                    </li>
                    <li>
                        <a href="<?= htmlspecialchars($basePath) ?>auth/logout.php"
                           class="nav-logout">
                            Logout
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    <main class="main-content" id="main-content">
        <div class="container">
            <?php if ($_flash !== null):
                $alertType    = htmlspecialchars($_flash['type'] ?? 'info');
                $alertMessage = htmlspecialchars($_flash['message'] ?? '');
            ?>
                <div class="alert alert-<?= $alertType ?>" role="alert">
                    <?= $alertMessage ?>
                </div>
            <?php endif; ?>
