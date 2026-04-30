<?php
define('APP_RUNNING', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

requireGuest();

$errors    = [];
$formName  = '';
$formEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrf('register.php');

    $rawName            = trim($_POST['name']             ?? '');
    $rawEmail           = trim($_POST['email']            ?? '');
    $rawPassword        = $_POST['password']              ?? '';
    $rawConfirmPassword = $_POST['confirm_password']      ?? '';

    $formName  = htmlspecialchars($rawName,  ENT_QUOTES, 'UTF-8');
    $formEmail = htmlspecialchars($rawEmail, ENT_QUOTES, 'UTF-8');

    if ($rawName === '') {
        $errors['name'] = 'Full name is required.';
    } elseif (mb_strlen($rawName) > 100) {
        $errors['name'] = 'Name must not exceed 100 characters.';
    }

    if ($rawEmail === '') {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($rawEmail, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    } elseif (mb_strlen($rawEmail) > 150) {
        $errors['email'] = 'Email address must not exceed 150 characters.';
    }

    if ($rawPassword === '') {
        $errors['password'] = 'Password is required.';
    } elseif (mb_strlen($rawPassword) < 8) {
        $errors['password'] = 'Password must be at least 8 characters long.';
    }

    if (!isset($errors['password'])) {
        if ($rawConfirmPassword === '') {
            $errors['confirm_password'] = 'Please confirm your password.';
        } elseif ($rawConfirmPassword !== $rawPassword) {
            $errors['confirm_password'] = 'Passwords do not match.';
        }
    }

    if (empty($errors['email'])) {
        $stmtCheck = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmtCheck->bind_param('s', $rawEmail);
        $stmtCheck->execute();
        $stmtCheck->store_result();
        if ($stmtCheck->num_rows > 0) {
            $errors['email'] = 'This email address is already registered.';
        }
        $stmtCheck->close();
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($rawPassword, PASSWORD_DEFAULT);
        $role = 'user';
        $stmtInsert = $conn->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
        $stmtInsert->bind_param('ssss', $rawName, $rawEmail, $hashedPassword, $role);
        $stmtInsert->execute();
        $stmtInsert->close();
        setFlashMessage('success', 'Account created successfully. Please log in.');
        header('Location: login.php');
        exit;
    }
}

$pageTitle = 'Create Account';
require_once __DIR__ . '/../includes/header.php';
?>
    <div class="form-card">
        <h1>Create Account</h1>
        <p class="form-subtitle">Join us to browse the menu and place orders.</p>
        <form id="register-form" method="POST" action="register.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" value="<?= $formName ?>" maxlength="100" autocomplete="name" required>
                <?php if (!empty($errors['name'])): ?>
                    <span id="name-error" class="form-error" role="alert"><?= htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php else: ?>
                    <span id="name-error" class="form-error" role="alert"></span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?= $formEmail ?>" maxlength="150" autocomplete="email" required>
                <?php if (!empty($errors['email'])): ?>
                    <span id="email-error" class="form-error" role="alert"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php else: ?>
                    <span id="email-error" class="form-error" role="alert"></span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" autocomplete="new-password" required minlength="8">
                <span class="form-hint">Minimum 8 characters.</span>
                <?php if (!empty($errors['password'])): ?>
                    <span id="password-error" class="form-error" role="alert"><?= htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php else: ?>
                    <span id="password-error" class="form-error" role="alert"></span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" required>
                <?php if (!empty($errors['confirm_password'])): ?>
                    <span id="confirm-password-error" class="form-error" role="alert"><?= htmlspecialchars($errors['confirm_password'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php else: ?>
                    <span id="confirm-password-error" class="form-error" role="alert"></span>
                <?php endif; ?>
            </div>
            <div class="form-group mt-3">
                <button type="submit" class="btn btn-primary w-100">Create Account</button>
            </div>
        </form>
        <div class="form-footer-link">
            Already have an account? <a href="login.php">Login</a>
        </div>
    </div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
