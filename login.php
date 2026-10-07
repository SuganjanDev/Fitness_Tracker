<?php

declare(strict_types=1);

require_once __DIR__ . '/controllers/AuthController.php';

require_guest();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = handle_login_request();
}

$pageTitle = 'Login';
$authUser = current_user();

require __DIR__ . '/views/partials/header.php';
require __DIR__ . '/views/partials/alerts.php';
?>

<section class="auth-layout">
    <div class="auth-copy">
        <p class="eyebrow">Welcome back</p>
        <h2>Keep your training, meals, and progress moving.</h2>
        <p>Sign in to open your personal dashboard and continue tracking the habits that matter.</p>
        <div class="auth-stats">
            <div>
                <strong>Daily</strong>
                <span>workout logs</span>
            </div>
            <div>
                <strong>Smart</strong>
                <span>meal tracking</span>
            </div>
            <div>
                <strong>Clear</strong>
                <span>progress trends</span>
            </div>
        </div>
    </div>

    <div class="auth-panel">
        <div class="auth-panel-heading">
            <span>Secure access</span>
            <h2>Log in</h2>
        </div>
        <form method="POST" action="<?= e(url('login.php')) ?>">
            <div class="form-grid form-grid-single">
                <label>
                    Email
                    <input type="email" name="email" value="<?= e(old('email')) ?>" placeholder="you@example.com" required>
                </label>
                <label>
                    Password
                    <input type="password" name="password" placeholder="Enter your password" required>
                </label>
            </div>
            <div class="actions auth-actions">
                <button type="submit">Log In</button>
                <a class="button-link button-secondary" href="<?= e(url('register.php')) ?>">Create Account</a>
            </div>
        </form>
    </div>
</section>

<?php require __DIR__ . '/views/partials/footer.php'; ?>
