<?php
define('APP_RUNNING', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

requireLogin();
requireAdmin();

define('MAX_PDF_SIZE',   5 * 1024 * 1024);
define('PDF_UPLOAD_DIR', __DIR__ . '/../uploads/pdfs/');
define('PDF_FIXED_NAME', 'menu.pdf');
define('PDF_FULL_PATH',  PDF_UPLOAD_DIR . PDF_FIXED_NAME);

$pdfError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrf('upload_menu_pdf.php');

    $fileError = $_FILES['menu_pdf']['error'] ?? UPLOAD_ERR_NO_FILE;

    if ($fileError === UPLOAD_ERR_NO_FILE) {
        $pdfError = 'Please select a PDF file to upload.';
    } elseif ($fileError !== UPLOAD_ERR_OK) {
        $pdfError = 'Upload failed. Please try again (error code: ' . $fileError . ').';
    } else {
        $tmpPath      = $_FILES['menu_pdf']['tmp_name'];
        $originalName = $_FILES['menu_pdf']['name'];
        $fileSize     = $_FILES['menu_pdf']['size'];
        $extension    = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if ($fileSize > MAX_PDF_SIZE) {
            $pdfError = 'File is too large. Maximum allowed size is 5MB.';
        }

        if ($pdfError === '') {
            $detectedMime = mime_content_type($tmpPath);
            if ($detectedMime !== 'application/pdf') {
                $pdfError = 'Invalid file type. Only PDF files are accepted.';
            }
        }

        if ($pdfError === '') {
            if ($extension !== 'pdf') {
                $pdfError = 'Invalid file extension. Only .pdf files are accepted.';
            }
        }

        if ($pdfError === '') {
            if (file_exists(PDF_FULL_PATH)) {
                unlink(PDF_FULL_PATH);
            }
            if (!move_uploaded_file($tmpPath, PDF_FULL_PATH)) {
                $pdfError = 'Failed to save the PDF. Please check directory permissions on uploads/pdfs/.';
            } else {
                setFlashMessage('success', 'PDF menu uploaded successfully.');
                header('Location: upload_menu_pdf.php');
                exit;
            }
        }
    }
}

$pdfExists       = file_exists(PDF_FULL_PATH);
$pdfLastModified = $pdfExists ? date('d M Y, H:i', filemtime(PDF_FULL_PATH)) : null;

$pageTitle = 'Upload PDF Menu';
require_once __DIR__ . '/../includes/header.php';
?>
    <div class="page-header">
        <h1>Upload PDF Menu</h1>
        <a href="dashboard.php" class="back-link">&larr; Back to Dashboard</a>
    </div>
    <?php if ($pdfExists): ?>
    <div class="card">
        <div class="card-title">Current Menu PDF</div>
        <div class="detail-row">
            <span class="detail-label">File</span>
            <span class="detail-value">
                <a href="<?= htmlspecialchars($basePath . 'uploads/pdfs/' . PDF_FIXED_NAME, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">View PDF &rarr;</a>
            </span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Last Updated</span>
            <span class="detail-value"><?= htmlspecialchars($pdfLastModified, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Filename</span>
            <span class="detail-value">
                <code><?= htmlspecialchars(PDF_FIXED_NAME, ENT_QUOTES, 'UTF-8') ?></code>
                (fixed &mdash; always overwritten on upload)
            </span>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-info" role="alert">
        No PDF menu has been uploaded yet. Use the form below to upload one.
    </div>
    <?php endif; ?>
    <div class="card">
        <div class="card-title"><?= $pdfExists ? 'Replace PDF Menu' : 'Upload PDF Menu' ?></div>
        <?php if ($pdfError !== ''): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($pdfError, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>
        <form id="pdf-upload-form" method="POST" action="upload_menu_pdf.php" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-group">
                <label for="menu_pdf">Select PDF File <span class="text-danger">*</span></label>
                <input type="file" id="menu_pdf" name="menu_pdf" accept=".pdf,application/pdf" required>
                <span class="form-hint">PDF format only. Maximum file size: 5MB.</span>
                <span id="pdf-error" class="form-error" role="alert"></span>
            </div>
            <?php if ($pdfExists): ?>
            <div class="alert alert-info" role="alert">
                <strong>Note:</strong> Uploading a new PDF will permanently replace the current one.
            </div>
            <?php endif; ?>
            <hr class="divider">
            <div class="d-flex gap-2 align-center">
                <button type="submit" class="btn btn-primary">Upload PDF</button>
                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
