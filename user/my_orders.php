<?php
define('APP_RUNNING', true);
$pageTitle = 'My Orders';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';

requireLogin();

$stmtOrders = $conn->prepare(
    'SELECT
         orders.id, orders.quantity, orders.total_price, orders.status,
         orders.notes, orders.created_at,
         menu_items.name AS item_name
     FROM orders
     JOIN menu_items ON orders.menu_item_id = menu_items.id
     WHERE orders.user_id = ?
     ORDER BY orders.created_at DESC'
);
$stmtOrders->bind_param('i', $_SESSION['user_id']);
$stmtOrders->execute();
$resultOrders = $stmtOrders->get_result();
$myOrders     = $resultOrders->fetch_all(MYSQLI_ASSOC);
$stmtOrders->close();

$stmtTotal = $conn->prepare('SELECT COALESCE(SUM(total_price), 0.00) FROM orders WHERE user_id = ?');
$stmtTotal->bind_param('i', $_SESSION['user_id']);
$stmtTotal->execute();
$stmtTotal->bind_result($totalSpent);
$stmtTotal->fetch();
$stmtTotal->close();
$totalSpent = (float) $totalSpent;

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
        <h1>My Orders</h1>
        <a href="menu.php" class="btn btn-primary btn-sm">+ Place New Order</a>
    </div>
    <?php if (empty($myOrders)): ?>
    <div class="empty-state">
        <p>You haven't placed any orders yet.</p>
        <a href="menu.php" class="btn btn-primary">Browse Menu</a>
    </div>
    <?php else: ?>
    <div class="table-wrapper">
        <table class="table" aria-label="My order history">
            <thead>
                <tr>
                    <th scope="col">Order #</th>
                    <th scope="col">Item</th>
                    <th scope="col">Qty</th>
                    <th scope="col">Total</th>
                    <th scope="col">Status</th>
                    <th scope="col">Notes</th>
                    <th scope="col">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($myOrders as $order): ?>
                <tr>
                    <td>#<?= (int) $order['id'] ?></td>
                    <td><?= htmlspecialchars($order['item_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= (int) $order['quantity'] ?></td>
                    <td>$<?= number_format((float) $order['total_price'], 2) ?></td>
                    <td>
                        <span class="badge <?= statusBadgeClass($order['status']) ?>">
                            <?= htmlspecialchars(ucfirst($order['status']), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </td>
                    <td>
                        <?php if (!empty($order['notes'])): ?>
                            <span title="<?= htmlspecialchars($order['notes'], ENT_QUOTES, 'UTF-8') ?>">
                                <?php
                                $noteDisplay = mb_strlen($order['notes']) > 40
                                    ? htmlspecialchars(mb_substr($order['notes'], 0, 40), ENT_QUOTES, 'UTF-8') . '&hellip;'
                                    : htmlspecialchars($order['notes'], ENT_QUOTES, 'UTF-8');
                                echo $noteDisplay;
                                ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted">&mdash;</span>
                        <?php endif; ?>
                    </td>
                    <td style="white-space:nowrap;">
                        <?= htmlspecialchars(date('d M Y, H:i', strtotime($order['created_at'])), ENT_QUOTES, 'UTF-8') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card mt-3">
        <div class="d-flex justify-between align-center">
            <span style="font-size:0.9rem; color:#333333; text-transform:uppercase; letter-spacing:0.06em; font-weight:700;">Total Spent</span>
            <span style="font-size:1.4rem; font-weight:700; font-family:'Georgia', serif;">$<?= number_format($totalSpent, 2) ?></span>
        </div>
        <p class="text-muted mt-1" style="font-size:0.82rem; margin-bottom:0;">
            Across <?= count($myOrders) ?> order<?= count($myOrders) !== 1 ? 's' : '' ?> total.
        </p>
    </div>
    <?php endif; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
