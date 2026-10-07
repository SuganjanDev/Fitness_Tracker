<?php

declare(strict_types=1);

require_once __DIR__ . '/controllers/AuthController.php';

require_guest();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = handle_register_request();
}

$pageTitle = 'Register';
$authUser = current_user();

require __DIR__ . '/views/partials/header.php';
require __DIR__ . '/views/partials/alerts.php';
?>

<section class="auth-layout auth-layout-wide">
    <div class="auth-copy">
        <p class="eyebrow">Start strong</p>
        <h2>Build a profile that makes every log more useful.</h2>
        <p>Add your basic fitness details now, then use the dashboard to track workouts, meals, BMI, and progress from one focused place.</p>
        <div class="auth-stats">
            <div>
                <strong>Profile</strong>
                <span>fitness details</span>
            </div>
            <div>
                <strong>BMI</strong>
                <span>quick insight</span>
            </div>
            <div>
                <strong>Goals</strong>
                <span>daily focus</span>
            </div>
        </div>
    </div>

    <div class="auth-panel">
        <div class="auth-panel-heading">
            <span>New account</span>
            <h2>Create account</h2>
        </div>
        <form method="POST" action="<?= e(url('register.php')) ?>">
            <div class="form-grid">
                <label>
                    Name
                    <input type="text" name="name" value="<?= e(old('name')) ?>" placeholder="Your full name" required>
                </label>
                <label>
                    Email
                    <input type="email" name="email" value="<?= e(old('email')) ?>" placeholder="you@example.com" required>
                </label>
                <label>
                    Password
                    <input type="password" name="password" placeholder="Create a password" required>
                </label>
                <label>
                    Age
                    <input type="number" name="age" min="0" value="<?= e(old('age')) ?>" placeholder="25">
                </label>
                <label>
                    Gender
                    <select name="gender">
                        <option value="">Select</option>
                        <option value="Male" <?= old('gender') === 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= old('gender') === 'Female' ? 'selected' : '' ?>>Female</option>
                        <option value="Other" <?= old('gender') === 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </label>
                <label>
                    Height (cm)
                    <input type="number" step="0.1" min="0" name="height_cm" value="<?= e(old('height_cm')) ?>" placeholder="170">
                </label>
                <label>
                    Weight (kg)
                    <input type="number" step="0.1" min="0" name="weight_kg" value="<?= e(old('weight_kg')) ?>" placeholder="68">
                </label>
                <label>
                    Fitness Goal
                    <input type="text" name="fitness_goal" value="<?= e(old('fitness_goal')) ?>" placeholder="Lose weight, gain strength, stay active">
                </label>
            </div>
            <div class="actions auth-actions">
                <button type="submit">Create Account</button>
                <a class="button-link button-secondary" href="<?= e(url('login.php')) ?>">Back to Login</a>
            </div>
        </form>
    </div>
</section>

<?php require __DIR__ . '/views/partials/footer.php'; ?>
