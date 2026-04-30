<?php
define('APP_RUNNING', true);
$pageTitle = 'All Orders';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/db.php';

requireLogin('../../auth/login.php');
requireAdmin('../../user/dashboard.php');

$stmtOrders = $conn->prepare(
    'SELECT
         orders.id,
         orders.quantity,
         orders.total_price,
         orders.status,
         orders.created_at,
         users.name  AS user_name,
         menu_items.name AS item_name
     FROM orders
     JOIN users      ON orders.user_id      = users.id
     JOIN menu_items ON orders.menu_item_id = menu_items.id
     ORDER BY orders.created_at DESC'
);
$stmtOrders->execute();
$resultOrders = $stmtOrders->get_result();
$orders       = $resultOrders->fetch_all(MYSQLI_ASSOC);
$stmtOrders->close();

function statusBadgeClass(string $status): string
{
    return match ($status) {
        'confirmed' => 'badge-confirmed',
        'cancelled' => 'badge-cancelled',
        default     => 'badge-pending',
    };
}
?>
    <div class="page-header">
        <h1>All Orders</h1>
        <a href="../dashboard.php" class="back-link">&larr; Back to Dashboard</a>
    </div>
    <?php if (empty($orders)): ?>
    <div class="empty-state">
        <p>No orders have been placed yet.</p>
        <p class="text-muted" style="font-size:0.9rem;">Orders will appear here once customers start placing them.</p>
    </div>
    <?php else: ?>
    <div class="table-wrapper">
        <table class="table" aria-label="All orders">
            <thead>
                <tr>
                    <th scope="col">Order #</th>
                    <th scope="col">Customer</th>
                    <th scope="col">Item Ordered</th>
                    <th scope="col">Qty</th>
                    <th scope="col">Total</th>
                    <th scope="col">Status</th>
                    <th scope="col">Date</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?= (int) $order['id'] ?></td>
                    <td><?= htmlspecialchars($order['user_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($order['item_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= (int) $order['quantity'] ?></td>
                    <td>$<?= number_format((float) $order['total_price'], 2) ?></td>
                    <td>
                        <span class="badge <?= statusBadgeClass($order['status']) ?>">
                            <?= htmlspecialchars(ucfirst($order['status']), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </td>
                    <td style="white-space:nowrap;">
                        <?= htmlspecialchars(date('d M Y, H:i', strtotime($order['created_at'])), ENT_QUOTES, 'UTF-8') ?>
                    </td>
                    <td>
                        <a href="view.php?id=<?= (int) $order['id'] ?>" class="btn btn-secondary btn-sm">View Details</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="text-muted mt-2" style="font-size:0.85rem;">
        Showing <?= count($orders) ?> order<?= count($orders) !== 1 ? 's' : '' ?> total.
    </p>
    <?php endif; ?>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
