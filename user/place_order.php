<?php
define('APP_RUNNING', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

requireUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrf('place_order.php');
}

$stmtItems = $conn->prepare(
    'SELECT id, name, price FROM menu_items WHERE is_available = 1 ORDER BY name ASC'
);
$stmtItems->execute();
$resultItems    = $stmtItems->get_result();
$availableItems = $resultItems->fetch_all(MYSQLI_ASSOC);
$stmtItems->close();

$errors         = [];
$selectedItemId = 0;
$formQuantity   = 1;
$formNotes      = '';
$preSelectError = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['item_id'])) {
    $requestedId = (int) $_GET['item_id'];
    if ($requestedId > 0) {
        $stmtCheck = $conn->prepare('SELECT id FROM menu_items WHERE id = ? AND is_available = 1 LIMIT 1');
        $stmtCheck->bind_param('i', $requestedId);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
        if ($resultCheck->fetch_assoc()) {
            $selectedItemId = $requestedId;
        } else {
            $preSelectError = 'The selected item is no longer available. Please choose from the menu below.';
        }
        $stmtCheck->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawItemId    = trim($_POST['menu_item_id'] ?? '');
    $rawQuantity  = trim($_POST['quantity']     ?? '');
    $rawNotes     = trim($_POST['notes']        ?? '');
    $verifiedItem = null;

    $selectedItemId = (int) $rawItemId;
    $formQuantity   = (int) $rawQuantity;
    $formNotes      = htmlspecialchars($rawNotes, ENT_QUOTES, 'UTF-8');

    if ($rawItemId === '' || !is_numeric($rawItemId) || (int) $rawItemId <= 0) {
        $errors['item'] = 'Please select a menu item.';
    } else {
        $stmtVerify = $conn->prepare('SELECT id, name, price FROM menu_items WHERE id = ? AND is_available = 1 LIMIT 1');
        $stmtVerify->bind_param('i', $selectedItemId);
        $stmtVerify->execute();
        $resultVerify = $stmtVerify->get_result();
        $verifiedItem = $resultVerify->fetch_assoc();
        $stmtVerify->close();
        if (!$verifiedItem) {
            $errors['item'] = 'The selected item is not available. Please choose a different item.';
        }
    }

    if ($rawQuantity === '' || !ctype_digit($rawQuantity)) {
        $errors['quantity'] = 'Please enter a valid quantity.';
    } else {
        $qty = (int) $rawQuantity;
        if ($qty < 1 || $qty > 20) {
            $errors['quantity'] = 'Quantity must be between 1 and 20.';
        }
    }

    if (empty($errors)) {
        $quantity   = (int) $rawQuantity;
        $totalPrice = round((float) $verifiedItem['price'] * $quantity, 2);
        $notes      = $rawNotes !== '' ? $rawNotes : null;
        $userId     = (int) $_SESSION['user_id'];
        $itemId     = (int) $verifiedItem['id'];
        $status     = 'pending';

        $stmtInsert = $conn->prepare(
            'INSERT INTO orders (user_id, menu_item_id, quantity, total_price, status, notes)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmtInsert->bind_param('iiidss', $userId, $itemId, $quantity, $totalPrice, $status, $notes);
        $stmtInsert->execute();
        $stmtInsert->close();

        setFlashMessage('success', 'Your order has been placed successfully!');
        header('Location: my_orders.php');
        exit;
    }
}

$pageTitle = 'Place an Order';
require_once __DIR__ . '/../includes/header.php';
?>
    <div class="page-header">
        <h1>Place an Order</h1>
        <a href="menu.php" class="back-link">&larr; Back to Menu</a>
    </div>
    <?php if ($preSelectError !== ''): ?>
    <div class="alert alert-danger" role="alert">
        <?= htmlspecialchars($preSelectError, ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php endif; ?>
    <div class="card">
        <div class="card-title">Order Details</div>
        <form id="order-form" method="POST" action="place_order.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-group">
                <label for="menu_item_id">Select Item <span class="text-danger">*</span></label>
                <?php if (empty($availableItems)): ?>
                    <p class="text-muted">No items are currently available. Please check back later.</p>
                <?php else: ?>
                    <select id="menu_item_id" name="menu_item_id" required>
                        <option value="" data-price="0">&mdash; Choose a menu item &mdash;</option>
                        <?php foreach ($availableItems as $menuItem): ?>
                            <option value="<?= (int) $menuItem['id'] ?>" data-price="<?= number_format((float) $menuItem['price'], 2, '.', '') ?>" <?= $selectedItemId === (int) $menuItem['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($menuItem['name'], ENT_QUOTES, 'UTF-8') ?> &mdash; $<?= number_format((float) $menuItem['price'], 2) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
                <?php if (!empty($errors['item'])): ?>
                    <span class="form-error" role="alert"><?= htmlspecialchars($errors['item'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>
            <div class="form-group" style="max-width:180px;">
                <label for="quantity">Quantity <span class="text-danger">*</span></label>
                <input type="number" id="quantity" name="quantity" value="<?= (int) $formQuantity ?>" min="1" max="20" required>
                <span class="form-hint">Maximum 20 per order.</span>
                <?php if (!empty($errors['quantity'])): ?>
                    <span id="quantity-error" class="form-error" role="alert"><?= htmlspecialchars($errors['quantity'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php else: ?>
                    <span id="quantity-error" class="form-error" role="alert"></span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <div id="price-preview" style="display:none; font-size:1.1rem; font-weight:700; padding:12px 16px; border:1px solid #000; background:#f5f5f5;" aria-live="polite"></div>
            </div>
            <div class="form-group">
                <label for="notes">Special Notes <span class="text-muted">(optional)</span></label>
                <textarea id="notes" name="notes" rows="3" placeholder="Any special requests or allergies? Let us know here."><?= $formNotes ?></textarea>
            </div>
            <hr class="divider">
            <div class="d-flex gap-2 align-center">
                <button type="submit" class="btn btn-primary" <?= empty($availableItems) ? 'disabled' : '' ?>>Place Order</button>
                <a href="menu.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
