<?php
define('APP_RUNNING', true);
$pageTitle = 'Welcome';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/db.php';

$stmtPreview = $conn->prepare(
    'SELECT id, name, description, price, category, image_path
     FROM menu_items
     WHERE is_available = 1
     ORDER BY created_at DESC
     LIMIT 6'
);
$stmtPreview->execute();
$resultPreview = $stmtPreview->get_result();
$previewItems  = $resultPreview->fetch_all(MYSQLI_ASSOC);
$stmtPreview->close();

$pdfPath      = __DIR__ . '/uploads/pdfs/menu.pdf';
$pdfExists    = file_exists($pdfPath);
$dashboardUrl = isAdmin() ? 'admin/dashboard.php' : 'user/dashboard.php';
?>
    <section class="hero" aria-labelledby="hero-heading">
        <div class="container">
            <h1 id="hero-heading">The Restaurant</h1>
            <p>A curated dining experience.</p>
            <div>
                <?php if (isLoggedIn()): ?>
                    <p style="font-size:1rem; color:#333333; margin-bottom:20px;">
                        Welcome back, <strong><?= htmlspecialchars($_SESSION['user_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>.
                    </p>
                    <a href="<?= htmlspecialchars($dashboardUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary">Go to Dashboard</a>
                    <a href="#menu" class="btn btn-secondary">View Full Menu</a>
                <?php else: ?>
                    <a href="#menu" class="btn btn-primary">View Full Menu</a>
                    <a href="auth/login.php" class="btn btn-secondary">Login / Register</a>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <div id="menu" style="padding-top:48px; margin-bottom:48px;">
        <div class="page-header">
            <h2>Our Menu</h2>
            <?php if ($pdfExists): ?>
                <a href="<?= htmlspecialchars($basePath . 'uploads/pdfs/menu.pdf', ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="btn btn-secondary btn-sm">Download Full Menu (PDF)</a>
            <?php endif; ?>
        </div>
        <?php if (empty($previewItems)): ?>
            <div class="empty-state">
                <p>Our menu is currently being updated. Please check back soon.</p>
            </div>
        <?php else: ?>
            <div class="menu-grid">
                <?php foreach ($previewItems as $item): ?>
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
                            <?php if (isLoggedIn()): ?>
                                <a href="user/place_order.php?item_id=<?= (int) $item['id'] ?>" class="btn btn-primary btn-sm">Order This</a>
                            <?php else: ?>
                                <a href="auth/login.php" class="btn btn-secondary btn-sm">Login to Order</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <?php if (isLoggedIn()): ?>
                    <a href="user/menu.php" class="btn btn-primary">See Full Menu</a>
                <?php else: ?>
                    <a href="auth/login.php" class="btn btn-primary">Login to See Full Menu</a>
                    <a href="auth/register.php" class="btn btn-secondary" style="margin-left:12px;">Create an Account</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php if ($pdfExists): ?>
    <div class="card text-center" style="padding:32px;">
        <div class="card-title" style="border-bottom:none; text-align:center;">Download Our Full Menu</div>
        <p class="text-muted" style="margin-bottom:20px;">Get a complete copy of our menu as a PDF — perfect for browsing offline.</p>
        <a href="<?= htmlspecialchars($basePath . 'uploads/pdfs/menu.pdf', ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary">Download PDF Menu</a>
    </div>
    <?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
