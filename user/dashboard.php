<?php
define('APP_RUNNING', true);
$pageTitle = 'My Dashboard';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';

requireLogin();

if (isAdmin()) {
    header('Location: ../admin/dashboard.php');
    exit;
}

$stmtOrderCount = $conn->prepare('SELECT COUNT(*) FROM orders WHERE user_id = ?');
$stmtOrderCount->bind_param('i', $_SESSION['user_id']);
$stmtOrderCount->execute();
$stmtOrderCount->bind_result($myOrderCount);
$stmtOrderCount->fetch();
$stmtOrderCount->close();
$myOrderCount = (int) $myOrderCount;

$pdfPath   = __DIR__ . '/../uploads/pdfs/menu.pdf';
$pdfExists = file_exists($pdfPath);

$userName = htmlspecialchars($_SESSION['user_name'] ?? 'Guest', ENT_QUOTES, 'UTF-8');
?>
    <div class="page-header">
        <h1>Welcome, <?= $userName ?></h1>
        <span class="text-muted" style="font-size:0.9rem;"><?= date('l, d F Y') ?></span>
    </div>
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
        <div class="stat-card">
            <span class="stat-number"><?= $myOrderCount ?></span>
            <span class="stat-label">My Orders</span>
        </div>
        <?php if ($pdfExists): ?>
        <div class="stat-card" style="display:flex; flex-direction:column; align-items:center; justify-content:center;">
            <a href="<?= htmlspecialchars($basePath . 'uploads/pdfs/menu.pdf', ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="btn btn-secondary" style="white-space:nowrap;">
                Download PDF Menu
            </a>
            <span class="stat-label" style="margin-top:10px;">Full Menu PDF</span>
        </div>
        <?php endif; ?>
    </div>
    <div class="card">
        <div class="card-title">Quick Actions</div>
        <div class="quick-actions">
            <a href="menu.php" class="btn btn-primary">Browse Menu</a>
            <a href="place_order.php" class="btn btn-secondary">Place an Order</a>
            <a href="my_orders.php" class="btn btn-secondary">My Orders</a>
        </div>
    </div>
    <?php if ($myOrderCount === 0): ?>
    <div class="card">
        <div class="card-title">Getting Started</div>
        <p>You haven't placed any orders yet. Browse our menu to find something you'd like.</p>
        <div class="mt-2">
            <a href="menu.php" class="btn btn-primary">View the Menu</a>
        </div>
    </div>
    <?php endif; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
