<?php
define('APP_RUNNING', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';

requireLogin('../../auth/login.php');
requireAdmin('../../user/dashboard.php');

$validStatuses = ['pending', 'confirmed', 'cancelled'];
$orderId = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = (int) ($_POST['order_id'] ?? 0);
    requireValidCsrf($orderId > 0 ? 'view.php?id=' . $orderId : 'index.php');
} else {
    $orderId = (int) ($_GET['id'] ?? 0);
}

if ($orderId <= 0) {
    setFlashMessage('danger', 'Invalid order ID.');
    header('Location: index.php');
    exit;
}

$stmtOrder = $conn->prepare(
    'SELECT
         orders.id, orders.quantity, orders.total_price, orders.status,
         orders.notes, orders.created_at,
         users.id AS user_id, users.name AS user_name, users.email AS user_email,
         menu_items.id AS item_id, menu_items.name AS item_name,
         menu_items.price AS item_unit_price
     FROM orders
     JOIN users      ON orders.user_id      = users.id
     JOIN menu_items ON orders.menu_item_id = menu_items.id
     WHERE orders.id = ?
     LIMIT 1'
);
$stmtOrder->bind_param('i', $orderId);
$stmtOrder->execute();
$resultOrder = $stmtOrder->get_result();
$order       = $resultOrder->fetch_assoc();
$stmtOrder->close();

if (!$order) {
    setFlashMessage('danger', 'Order not found.');
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = trim($_POST['status'] ?? '');
    if (!in_array($newStatus, $validStatuses, true)) {
        setFlashMessage('danger', 'Invalid status value. Please select a valid status.');
        header('Location: view.php?id=' . $orderId);
        exit;
    }
    $stmtUpdate = $conn->prepare('UPDATE orders SET status = ? WHERE id = ?');
    $stmtUpdate->bind_param('si', $newStatus, $orderId);
    $stmtUpdate->execute();
    $stmtUpdate->close();
    setFlashMessage('success', 'Order #' . $orderId . ' status updated to "' . ucfirst($newStatus) . '".');
    header('Location: view.php?id=' . $orderId);
    exit;
}

function statusBadgeClass(string $status): string
{
    return match ($status) {
        'confirmed' => 'badge-confirmed',
        'cancelled' => 'badge-cancelled',
        default     => 'badge-pending',
    };
}

$pageTitle = 'Order Details';
require_once __DIR__ . '/../../includes/header.php';
?>
    <div class="page-header">
        <h1>Order #<?= (int) $order['id'] ?></h1>
        <a href="index.php" class="back-link">&larr; Back to All Orders</a>
    </div>
    <div class="card">
        <div class="card-title">Order Information</div>
        <div class="detail-row">
            <span class="detail-label">Order ID</span>
            <span class="detail-value">#<?= (int) $order['id'] ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Date Placed</span>
            <span class="detail-value"><?= htmlspecialchars(date('d M Y, H:i', strtotime($order['created_at'])), ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Status</span>
            <span class="detail-value">
                <span class="badge <?= statusBadgeClass($order['status']) ?>">
                    <?= htmlspecialchars(ucfirst($order['status']), ENT_QUOTES, 'UTF-8') ?>
                </span>
            </span>
        </div>
    </div>
    <div class="card">
        <div class="card-title">Customer</div>
        <div class="detail-row">
            <span class="detail-label">Name</span>
            <span class="detail-value"><?= htmlspecialchars($order['user_name'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Email</span>
            <span class="detail-value">
                <a href="mailto:<?= htmlspecialchars($order['user_email'], ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($order['user_email'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            </span>
        </div>
    </div>
    <div class="card">
        <div class="card-title">Item Ordered</div>
        <div class="detail-row">
            <span class="detail-label">Item</span>
            <span class="detail-value"><?= htmlspecialchars($order['item_name'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Unit Price</span>
            <span class="detail-value">$<?= number_format((float) $order['item_unit_price'], 2) ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Quantity</span>
            <span class="detail-value"><?= (int) $order['quantity'] ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Total Price</span>
            <span class="detail-value"><strong>$<?= number_format((float) $order['total_price'], 2) ?></strong></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Special Notes</span>
            <span class="detail-value">
                <?php if (!empty($order['notes'])): ?>
                    <?= htmlspecialchars($order['notes'], ENT_QUOTES, 'UTF-8') ?>
                <?php else: ?>
                    <span class="text-muted">None</span>
                <?php endif; ?>
            </span>
        </div>
    </div>
    <div class="card">
        <div class="card-title">Update Order Status</div>
        <form method="POST" action="view.php?id=<?= (int) $order['id'] ?>">
            <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-group" style="max-width:280px;">
                <label for="status">New Status</label>
                <select id="status" name="status">
                    <?php foreach ($validStatuses as $statusOption): ?>
                        <option value="<?= htmlspecialchars($statusOption, ENT_QUOTES, 'UTF-8') ?>" <?= $order['status'] === $statusOption ? 'selected' : '' ?>>
                            <?= htmlspecialchars(ucfirst($statusOption), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="d-flex gap-2 align-center mt-2">
                <button type="submit" class="btn btn-primary btn-sm">Update Status</button>
                <a href="index.php" class="btn btn-secondary btn-sm">Cancel</a>
            </div>
        </form>
    </div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
