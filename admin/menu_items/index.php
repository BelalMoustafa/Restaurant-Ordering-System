<?php
define('APP_RUNNING', true);
$pageTitle = 'Menu Items';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/db.php';

requireLogin('../../auth/login.php');
requireAdmin('../../user/dashboard.php');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$stmtItems = $conn->prepare(
    'SELECT id, name, category, price, image_path, is_available, created_at
     FROM menu_items
     ORDER BY created_at DESC'
);
$stmtItems->execute();
$result    = $stmtItems->get_result();
$menuItems = $result->fetch_all(MYSQLI_ASSOC);
$stmtItems->close();
?>
    <div class="page-header">
        <h1>Menu Items</h1>
        <a href="create.php" class="btn btn-primary">+ Add New Item</a>
    </div>
    <?php if (empty($menuItems)): ?>
    <div class="empty-state">
        <p>No menu items found.</p>
        <a href="create.php" class="btn btn-primary">Add Your First Item</a>
    </div>
    <?php else: ?>
    <div class="table-wrapper">
        <table class="table" aria-label="Menu items list">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Image</th>
                    <th scope="col">Name</th>
                    <th scope="col">Category</th>
                    <th scope="col">Price</th>
                    <th scope="col">Available</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($menuItems as $rowNum => $item): ?>
                <tr>
                    <td><?= $rowNum + 1 ?></td>
                    <td>
                        <?php if (!empty($item['image_path'])): ?>
                            <img src="<?= htmlspecialchars($basePath . $item['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>" class="img-thumbnail">
                        <?php else: ?>
                            <span class="text-muted" style="font-size:0.8rem;">No Image</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($item['category'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td>$<?= number_format((float) $item['price'], 2) ?></td>
                    <td>
                        <?php if ($item['is_available']): ?>
                            <span class="badge badge-confirmed">Yes</span>
                        <?php else: ?>
                            <span class="badge badge-cancelled">No</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="actions-cell">
                            <a href="edit.php?id=<?= (int) $item['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                            <form method="POST" action="delete.php" style="display:inline;">
                                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit" class="btn btn-danger btn-sm" data-confirm="Are you sure you want to delete &quot;<?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>&quot;? This action cannot be undone.">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="text-muted mt-2" style="font-size:0.85rem;">
        Showing <?= count($menuItems) ?> item<?= count($menuItems) !== 1 ? 's' : '' ?> total.
    </p>
    <?php endif; ?>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
