<?php
define('APP_RUNNING', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

requireUser();

$stmtMenu = $conn->prepare(
    'SELECT id, name, description, price, category, image_path
     FROM menu_items
     WHERE is_available = 1
     ORDER BY category ASC, name ASC'
);
$stmtMenu->execute();
$resultMenu = $stmtMenu->get_result();
$allItems   = $resultMenu->fetch_all(MYSQLI_ASSOC);
$stmtMenu->close();

$itemsByCategory = [];
foreach ($allItems as $item) {
    $itemsByCategory[$item['category']][] = $item;
}

$pdfPath   = __DIR__ . '/../uploads/pdfs/menu.pdf';
$pdfExists = file_exists($pdfPath);

$pageTitle = 'Our Menu';
require_once __DIR__ . '/../includes/header.php';
?>
    <div class="page-header">
        <h1>Our Menu</h1>
        <?php if ($pdfExists): ?>
            <a href="<?= htmlspecialchars($basePath . 'uploads/pdfs/menu.pdf', ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="btn btn-secondary btn-sm">
                Download Full Menu (PDF)
            </a>
        <?php endif; ?>
    </div>
    <?php if (empty($allItems)): ?>
    <div class="empty-state">
        <p>Our menu is currently being updated. Please check back soon.</p>
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
    <?php else: ?>
    <?php foreach ($itemsByCategory as $category => $items): ?>
    <section class="menu-category-section" aria-labelledby="cat-<?= htmlspecialchars(preg_replace('/[^a-z0-9]+/', '-', strtolower($category)), ENT_QUOTES, 'UTF-8') ?>">
        <h2 id="cat-<?= htmlspecialchars(preg_replace('/[^a-z0-9]+/', '-', strtolower($category)), ENT_QUOTES, 'UTF-8') ?>" class="menu-category-heading">
            <?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>
        </h2>
        <div class="menu-grid">
            <?php foreach ($items as $item): ?>
            <article class="menu-card">
                <?php if (!empty($item['image_path'])): ?>
                    <img src="<?= htmlspecialchars($basePath . $item['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>">
                <?php else: ?>
                    <div class="menu-card-img-placeholder" aria-hidden="true">No Image</div>
                <?php endif; ?>
                <div class="menu-card-body">
                    <span class="menu-card-category"><?= htmlspecialchars($item['category'], ENT_QUOTES, 'UTF-8') ?></span>
                    <h3 class="menu-card-title"><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <?php if (!empty($item['description'])): ?>
                        <?php
                        $desc = $item['description'];
                        $displayDesc = mb_strlen($desc) > 100
                            ? htmlspecialchars(mb_substr($desc, 0, 100), ENT_QUOTES, 'UTF-8') . '&hellip;'
                            : htmlspecialchars($desc, ENT_QUOTES, 'UTF-8');
                        ?>
                        <p class="menu-card-description"><?= $displayDesc ?></p>
                    <?php endif; ?>
                    <div class="menu-card-footer">
                        <span class="menu-card-price">$<?= number_format((float) $item['price'], 2) ?></span>
                        <a href="place_order.php?item_id=<?= (int) $item['id'] ?>" class="btn btn-primary btn-sm">Order This</a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endforeach; ?>
    <?php endif; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
