<?php

declare(strict_types=1);

require_once __DIR__ . '/controllers/ProfileController.php';

require_auth();

$authUser = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = handle_profile_update((int) $authUser['id']);
    $authUser = current_user();
}

$pageTitle = 'Profile';

require __DIR__ . '/views/partials/header.php';
require __DIR__ . '/views/partials/alerts.php';
?>

<section class="card">
    <p class="muted">Update the details that shape your goals and progress tracking.</p>
    <form method="POST" action="<?= e(url('profile.php')) ?>">
        <div class="form-grid">
            <label>
                Name
                <input type="text" name="name" value="<?= e((string) $authUser['name']) ?>" required>
            </label>
            <label>
                Email
                <input type="email" name="email" value="<?= e((string) $authUser['email']) ?>" required>
            </label>
            <label>
                Age
                <input type="number" name="age" min="0" value="<?= e((string) ($authUser['age'] ?? '')) ?>">
            </label>
            <label>
                Gender
                <select name="gender">
                    <option value="">Select</option>
                    <option value="Male" <?= ($authUser['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                    <option value="Female" <?= ($authUser['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                    <option value="Other" <?= ($authUser['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                </select>
            </label>
            <label>
                Height (cm)
                <input type="number" step="0.1" min="0" name="height_cm" value="<?= e((string) ($authUser['height_cm'] ?? '')) ?>">
            </label>
            <label>
                Weight (kg)
                <input type="number" step="0.1" min="0" name="weight_kg" value="<?= e((string) ($authUser['weight_kg'] ?? '')) ?>">
            </label>
        </div>
        <label>
            Fitness Goal
            <textarea name="fitness_goal" placeholder="Describe what success looks like for you"><?= e((string) ($authUser['fitness_goal'] ?? '')) ?></textarea>
        </label>
        <button type="submit">Save Profile</button>
    </form>
</section>

<?php require __DIR__ . '/views/partials/footer.php'; ?>
