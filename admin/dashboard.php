<?php
define('APP_RUNNING', true);
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';

requireLogin();
requireAdmin();

$stmtTotalItems = $conn->prepare('SELECT COUNT(*) FROM menu_items');
$stmtTotalItems->execute();
$stmtTotalItems->bind_result($totalMenuItems);
$stmtTotalItems->fetch();
$stmtTotalItems->close();
$totalMenuItems = (int) $totalMenuItems;

$stmtAvailableItems = $conn->prepare('SELECT COUNT(*) FROM menu_items WHERE is_available = 1');
$stmtAvailableItems->execute();
$stmtAvailableItems->bind_result($availableItems);
$stmtAvailableItems->fetch();
$stmtAvailableItems->close();
$availableItems = (int) $availableItems;

$stmtTotalOrders = $conn->prepare('SELECT COUNT(*) FROM orders');
$stmtTotalOrders->execute();
$stmtTotalOrders->bind_result($totalOrders);
$stmtTotalOrders->fetch();
$stmtTotalOrders->close();
$totalOrders = (int) $totalOrders;

$stmtPendingOrders = $conn->prepare("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
$stmtPendingOrders->execute();
$stmtPendingOrders->bind_result($pendingOrders);
$stmtPendingOrders->fetch();
$stmtPendingOrders->close();
$pendingOrders = (int) $pendingOrders;

$adminName = htmlspecialchars($_SESSION['user_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8');
?>
    <div class="page-header">
        <h1>Welcome back, <?= $adminName ?></h1>
        <span class="text-muted" style="font-size:0.9rem;"><?= date('l, d F Y') ?></span>
    </div>
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-number"><?= $totalMenuItems ?></span>
            <span class="stat-label">Total Menu Items</span>
        </div>
        <div class="stat-card">
            <span class="stat-number"><?= $availableItems ?></span>
            <span class="stat-label">Available Items</span>
        </div>
        <div class="stat-card">
            <span class="stat-number"><?= $totalOrders ?></span>
            <span class="stat-label">Total Orders</span>
        </div>
        <div class="stat-card">
            <span class="stat-number"><?= $pendingOrders ?></span>
            <span class="stat-label">Pending Orders</span>
        </div>
    </div>
    <div class="card">
        <div class="card-title">Quick Actions</div>
        <div class="quick-actions">
            <a href="menu_items/index.php" class="btn btn-primary">Manage Menu Items</a>
            <a href="menu_items/create.php" class="btn btn-secondary">Add New Item</a>
            <a href="orders/index.php" class="btn btn-secondary">View All Orders</a>
            <a href="upload_menu_pdf.php" class="btn btn-secondary">Upload PDF Menu</a>
        </div>
    </div>
    <div class="card">
        <div class="card-title">System Overview</div>
        <div class="detail-row">
            <span class="detail-label">Menu Coverage</span>
            <span class="detail-value"><?= $availableItems ?> of <?= $totalMenuItems ?> items currently on the menu</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Orders Awaiting</span>
            <span class="detail-value">
                <?php if ($pendingOrders > 0): ?>
                    <strong><?= $pendingOrders ?></strong> order<?= $pendingOrders !== 1 ? 's' : '' ?> pending action
                    &mdash; <a href="orders/index.php">Review now</a>
                <?php else: ?>
                    No pending orders at this time.
                <?php endif; ?>
            </span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Hidden Items</span>
            <span class="detail-value">
                <?= ($totalMenuItems - $availableItems) ?> item<?= ($totalMenuItems - $availableItems) !== 1 ? 's' : '' ?> hidden from the public menu
            </span>
        </div>
    </div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
