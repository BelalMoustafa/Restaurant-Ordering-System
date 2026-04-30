<?php
define('APP_RUNNING', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

$errors    = [];
$formEmail = '';
$authError = '';

if (!isLoggedIn() && isset($_COOKIE['remember_user'])) {
    $cookieToken = $_COOKIE['remember_user'];
    if (strlen($cookieToken) === 64 && ctype_alnum($cookieToken)) {
        $tokenHash   = hash('sha256', $cookieToken);
        $stmtCookie  = $conn->prepare('SELECT id, name, email, role FROM users WHERE remember_token = ? LIMIT 1');
        $stmtCookie->bind_param('s', $tokenHash);
        $stmtCookie->execute();
        $resultCookie = $stmtCookie->get_result();
        $cookieUser   = $resultCookie->fetch_assoc();
        $stmtCookie->close();
        if ($cookieUser) {
            $newToken     = bin2hex(random_bytes(32));
            $newTokenHash = hash('sha256', $newToken);
            $stmtRotate   = $conn->prepare('UPDATE users SET remember_token = ? WHERE id = ?');
            $stmtRotate->bind_param('si', $newTokenHash, $cookieUser['id']);
            $stmtRotate->execute();
            $stmtRotate->close();
            session_regenerate_id(true);
            $_SESSION['user_id']   = $cookieUser['id'];
            $_SESSION['role']      = $cookieUser['role'];
            $_SESSION['user_name'] = $cookieUser['name'];
            setcookie('remember_user', $newToken, [
                'expires'  => time() + (30 * 24 * 60 * 60),
                'path'     => '/',
                'secure'   => false,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            if ($cookieUser['role'] === 'admin') {
                header('Location: ../admin/dashboard.php');
            } else {
                header('Location: ../user/dashboard.php');
            }
            exit;
        } else {
            setcookie('remember_user', '', time() - 3600, '/');
        }
    } else {
        setcookie('remember_user', '', time() - 3600, '/');
    }
}

requireGuest();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrf('login.php');

    $rawEmail    = trim($_POST['email']    ?? '');
    $rawPassword = $_POST['password']      ?? '';
    $rememberMe  = isset($_POST['remember_me']) && $_POST['remember_me'] === '1';

    $formEmail = htmlspecialchars($rawEmail, ENT_QUOTES, 'UTF-8');

    if ($rawEmail === '') {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($rawEmail, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if ($rawPassword === '') {
        $errors['password'] = 'Password is required.';
    }

    if (empty($errors)) {
        $stmtUser = $conn->prepare('SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1');
        $stmtUser->bind_param('s', $rawEmail);
        $stmtUser->execute();
        $resultUser = $stmtUser->get_result();
        $user       = $resultUser->fetch_assoc();
        $stmtUser->close();

        if (!$user || !password_verify($rawPassword, $user['password'])) {
            $authError = 'Invalid email or password.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['role']      = $user['role'];
            $_SESSION['user_name'] = $user['name'];

            if ($rememberMe) {
                $token     = bin2hex(random_bytes(32));
                $tokenHash = hash('sha256', $token);
                $stmtToken = $conn->prepare('UPDATE users SET remember_token = ? WHERE id = ?');
                $stmtToken->bind_param('si', $tokenHash, $user['id']);
                $stmtToken->execute();
                $stmtToken->close();
                setcookie('remember_user', $token, [
                    'expires'  => time() + (30 * 24 * 60 * 60),
                    'path'     => '/',
                    'secure'   => false,
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
            } else {
                $emptyToken = null;
                $stmtClear  = $conn->prepare('UPDATE users SET remember_token = ? WHERE id = ?');
                $stmtClear->bind_param('si', $emptyToken, $user['id']);
                $stmtClear->execute();
                $stmtClear->close();
                setcookie('remember_user', '', time() - 3600, '/');
            }

            if ($user['role'] === 'admin') {
                header('Location: ../admin/dashboard.php');
            } else {
                header('Location: ../user/dashboard.php');
            }
            exit;
        }
    }
}

$pageTitle = 'Login';
require_once __DIR__ . '/../includes/header.php';

$loggedOutMessage = '';
if (isset($_GET['logged_out']) && $_GET['logged_out'] === '1') {
    $loggedOutMessage = 'You have been logged out successfully.';
}
?>
    <div class="form-card">
        <h1>Login</h1>
        <p class="form-subtitle">Welcome back. Please sign in to continue.</p>
        <?php if ($loggedOutMessage !== ''): ?>
            <div class="alert alert-info" role="alert">
                <?= htmlspecialchars($loggedOutMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>
        <?php if ($authError !== ''): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($authError, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>
        <form id="login-form" method="POST" action="login.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
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
                <input type="password" id="password" name="password" autocomplete="current-password" required>
                <?php if (!empty($errors['password'])): ?>
                    <span id="password-error" class="form-error" role="alert"><?= htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php else: ?>
                    <span id="password-error" class="form-error" role="alert"></span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label class="checkbox-label" for="remember_me">
                    <input type="checkbox" id="remember_me" name="remember_me" value="1" <?= isset($_COOKIE['remember_user']) ? 'checked' : '' ?>>
                    Remember me for 30 days
                </label>
            </div>
            <div class="form-group mt-3">
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </div>
        </form>
        <div class="form-footer-link">
            Don't have an account? <a href="register.php">Register</a>
        </div>
    </div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
