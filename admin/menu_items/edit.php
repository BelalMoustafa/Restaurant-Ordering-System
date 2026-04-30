<?php
define('APP_RUNNING', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';

requireLogin('../../auth/login.php');
requireAdmin('../../user/dashboard.php');

define('MAX_IMAGE_SIZE',    2 * 1024 * 1024);
define('ALLOWED_MIME_TYPES', ['image/jpeg', 'image/png']);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png']);
define('UPLOAD_DIR',         __DIR__ . '/../../uploads/images/');

$errors           = [];
$formName         = '';
$formDescription  = '';
$formPrice        = '';
$formCategory     = '';
$formAvailable    = true;
$currentImagePath = null;
$itemId           = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = (int) ($_POST['id'] ?? 0);
    requireValidCsrf($itemId > 0 ? 'edit.php?id=' . $itemId : 'index.php');
} else {
    $itemId = (int) ($_GET['id'] ?? 0);
}

if ($itemId <= 0) {
    setFlashMessage('danger', 'Invalid item ID. Please select an item to edit.');
    header('Location: index.php');
    exit;
}

$stmtFetch = $conn->prepare(
    'SELECT id, name, description, price, category, image_path, is_available
     FROM menu_items WHERE id = ? LIMIT 1'
);
$stmtFetch->bind_param('i', $itemId);
$stmtFetch->execute();
$resultFetch = $stmtFetch->get_result();
$item        = $resultFetch->fetch_assoc();
$stmtFetch->close();

if (!$item) {
    setFlashMessage('danger', 'Menu item not found.');
    header('Location: index.php');
    exit;
}

$currentImagePath = $item['image_path'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $formName        = htmlspecialchars($item['name'],              ENT_QUOTES, 'UTF-8');
    $formDescription = htmlspecialchars($item['description'] ?? '', ENT_QUOTES, 'UTF-8');
    $formPrice       = htmlspecialchars((string) $item['price'],    ENT_QUOTES, 'UTF-8');
    $formCategory    = htmlspecialchars($item['category'],          ENT_QUOTES, 'UTF-8');
    $formAvailable   = (bool) $item['is_available'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawName        = trim($_POST['name']        ?? '');
    $rawDescription = trim($_POST['description'] ?? '');
    $rawPrice       = trim($_POST['price']       ?? '');
    $rawCategory    = trim($_POST['category']    ?? '');
    $rawAvailable   = isset($_POST['is_available']) ? 1 : 0;

    $formName        = htmlspecialchars($rawName,        ENT_QUOTES, 'UTF-8');
    $formDescription = htmlspecialchars($rawDescription, ENT_QUOTES, 'UTF-8');
    $formPrice       = htmlspecialchars($rawPrice,       ENT_QUOTES, 'UTF-8');
    $formCategory    = htmlspecialchars($rawCategory,    ENT_QUOTES, 'UTF-8');
    $formAvailable   = (bool) $rawAvailable;

    if ($rawName === '') {
        $errors['name'] = 'Item name is required.';
    } elseif (mb_strlen($rawName) > 150) {
        $errors['name'] = 'Item name must not exceed 150 characters.';
    }

    if ($rawPrice === '') {
        $errors['price'] = 'Price is required.';
    } elseif (!is_numeric($rawPrice)) {
        $errors['price'] = 'Price must be a valid number.';
    } elseif ((float) $rawPrice < 0) {
        $errors['price'] = 'Price cannot be negative.';
    }

    if ($rawCategory === '') {
        $errors['category'] = 'Category is required.';
    } elseif (mb_strlen($rawCategory) > 100) {
        $errors['category'] = 'Category must not exceed 100 characters.';
    }

    $newImagePath = $currentImagePath;
    $fileError    = $_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE;

    if ($fileError !== UPLOAD_ERR_NO_FILE) {
        if ($fileError !== UPLOAD_ERR_OK) {
            $errors['image'] = 'File upload failed. Please try again (error code: ' . $fileError . ').';
        } else {
            $tmpPath      = $_FILES['image']['tmp_name'];
            $originalName = $_FILES['image']['name'];
            $fileSize     = $_FILES['image']['size'];
            $extension    = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            if ($fileSize > MAX_IMAGE_SIZE) {
                $errors['image'] = 'Image is too large. Maximum allowed size is 2MB.';
            }

            if (empty($errors['image'])) {
                $detectedMime = mime_content_type($tmpPath);
                if (!in_array($detectedMime, ALLOWED_MIME_TYPES, true)) {
                    $errors['image'] = 'Invalid file type. Only JPG and PNG images are allowed.';
                }
            }

            if (empty($errors['image'])) {
                if (!in_array($extension, ALLOWED_EXTENSIONS, true)) {
                    $errors['image'] = 'Invalid file extension. Only .jpg, .jpeg, and .png are allowed.';
                }
            }

            if (empty($errors['image'])) {
                $newFilename = uniqid('item_', true) . '.' . $extension;
                $destination = UPLOAD_DIR . $newFilename;
                if (!move_uploaded_file($tmpPath, $destination)) {
                    $errors['image'] = 'Failed to save the uploaded image. Please check directory permissions.';
                } else {
                    if (!empty($currentImagePath)) {
                        $oldFilePath = __DIR__ . '/../../' . $currentImagePath;
                        if (file_exists($oldFilePath)) {
                            unlink($oldFilePath);
                        }
                    }
                    $newImagePath = 'uploads/images/' . $newFilename;
                }
            }
        }
    }

    if (empty($errors)) {
        $descValue  = $rawDescription !== '' ? $rawDescription : '';
        $imageValue = $newImagePath !== null ? $newImagePath : '';
        $priceValue = (float) $rawPrice;
        $stmtUpdate = $conn->prepare(
            'UPDATE menu_items
             SET name = ?, description = ?, price = ?,
                 category = ?, image_path = ?, is_available = ?
             WHERE id = ?'
        );
        $stmtUpdate->bind_param('ssdssii', $rawName, $descValue, $priceValue, $rawCategory, $imageValue, $rawAvailable, $itemId);
        $stmtUpdate->execute();
        $stmtUpdate->close();
        setFlashMessage('success', 'Menu item "' . $rawName . '" updated successfully.');
        header('Location: index.php');
        exit;
    }
}

$pageTitle = 'Edit Menu Item';
require_once __DIR__ . '/../../includes/header.php';
?>
    <div class="page-header">
        <h2>Edit Menu Item</h2>
        <a href="index.php" class="back-link">&larr; Back to Menu Items</a>
    </div>
    <div class="card">
        <div class="card-title">Editing: <?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></div>
        <form id="menu-item-form" method="POST" action="edit.php" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="id" value="<?= $itemId ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-group">
                <label for="name">Item Name <span class="text-danger">*</span></label>
                <input type="text" id="name" name="name" value="<?= $formName ?>" maxlength="150" required autocomplete="off">
                <?php if (!empty($errors['name'])): ?>
                    <span id="name-error" class="form-error" role="alert"><?= htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php else: ?>
                    <span id="name-error" class="form-error" role="alert"></span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="description">Description <span class="text-muted">(optional)</span></label>
                <textarea id="description" name="description" rows="3" placeholder="Describe the dish &mdash; ingredients, preparation style, etc."><?= $formDescription ?></textarea>
            </div>
            <div class="d-flex gap-3" style="flex-wrap:wrap;">
                <div class="form-group" style="flex:1;min-width:180px;">
                    <label for="price">Price ($) <span class="text-danger">*</span></label>
                    <input type="number" id="price" name="price" value="<?= $formPrice ?>" min="0" step="0.01" placeholder="0.00" required>
                    <?php if (!empty($errors['price'])): ?>
                        <span id="price-error" class="form-error" role="alert"><?= htmlspecialchars($errors['price'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php else: ?>
                        <span id="price-error" class="form-error" role="alert"></span>
                    <?php endif; ?>
                </div>
                <div class="form-group" style="flex:1;min-width:180px;">
                    <label for="category">Category <span class="text-danger">*</span></label>
                    <input type="text" id="category" name="category" value="<?= $formCategory ?>" maxlength="100" placeholder="e.g. Starters, Mains, Desserts, Drinks" required autocomplete="off">
                    <?php if (!empty($errors['category'])): ?>
                        <span id="category-error" class="form-error" role="alert"><?= htmlspecialchars($errors['category'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php else: ?>
                        <span id="category-error" class="form-error" role="alert"></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="form-group">
                <label class="checkbox-label" for="is_available">
                    <input type="checkbox" id="is_available" name="is_available" value="1" <?= $formAvailable ? 'checked' : '' ?>>
                    Available on menu (visible to customers)
                </label>
                <span class="form-hint">Uncheck to hide this item from the public menu without deleting it.</span>
            </div>
            <div class="form-group">
                <label>Current Image</label>
                <?php if (!empty($currentImagePath)): ?>
                    <div style="margin-bottom:12px;">
                        <img src="<?= htmlspecialchars($basePath . $currentImagePath, ENT_QUOTES, 'UTF-8') ?>" alt="Current image for <?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>" style="max-width:160px; height:120px; object-fit:cover; border:1px solid #000; display:block; filter:grayscale(100%);">
                        <span class="form-hint" style="margin-top:6px;">Current image. Upload a new one below to replace it.</span>
                    </div>
                <?php else: ?>
                    <p class="text-muted" style="font-size:0.9rem; margin-bottom:12px;">No current image.</p>
                <?php endif; ?>
                <label for="image">Replace Image <span class="text-muted">(leave blank to keep current &mdash; JPG or PNG, max 2MB)</span></label>
                <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                <?php if (!empty($errors['image'])): ?>
                    <span id="image-error" class="form-error" role="alert"><?= htmlspecialchars($errors['image'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php else: ?>
                    <span id="image-error" class="form-error" role="alert"></span>
                <?php endif; ?>
                <img id="image-preview" src="" alt="New image preview" style="display:none; max-width:200px; margin-top:10px; border:1px solid #000; filter:grayscale(100%);">
            </div>
            <hr class="divider">
            <div class="d-flex gap-2 align-center">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
