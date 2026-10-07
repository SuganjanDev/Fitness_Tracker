<?php

declare(strict_types=1);

require_once __DIR__ . '/controllers/MealController.php';

require_auth();

$authUser = current_user();
$errors = [];
$formData = [
    'meal_date' => date('Y-m-d'),
    'meal_type' => '',
    'food_name' => '',
    'calories' => '',
    'notes' => '',
];
$editId = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$errors, $formData] = handle_meal_request((int) $authUser['id']);
}

$meals = get_meals_for_user((int) $authUser['id']);

if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    foreach ($meals as $meal) {
        if ((int) $meal['id'] === $editId) {
            $formData = [
                'meal_date' => $meal['meal_date'],
                'meal_type' => $meal['meal_type'],
                'food_name' => $meal['food_name'],
                'calories' => (string) $meal['calories'],
                'notes' => (string) ($meal['notes'] ?? ''),
            ];
            break;
        }
    }
}

$pageTitle = 'Meals';

require __DIR__ . '/views/partials/header.php';
require __DIR__ . '/views/partials/alerts.php';
?>

<section class="grid grid-2">
    <article class="card">
        <h2><?= $editId > 0 ? 'Edit meal' : 'Add meal' ?></h2>
        <form method="POST" action="<?= e(url('meals.php')) ?>">
            <input type="hidden" name="action" value="<?= $editId > 0 ? 'update' : 'create' ?>">
            <input type="hidden" name="id" value="<?= e((string) $editId) ?>">
            <label>
                Meal date
                <input type="date" name="meal_date" value="<?= e($formData['meal_date']) ?>" required>
            </label>
            <label>
                Meal type
                <select name="meal_type" required>
                    <option value="">Select meal</option>
                    <?php foreach (['Breakfast', 'Lunch', 'Dinner', 'Snack'] as $mealType): ?>
                        <option value="<?= e($mealType) ?>" <?= $formData['meal_type'] === $mealType ? 'selected' : '' ?>><?= e($mealType) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div class="form-grid">
                <label>
                    Food name
                    <input type="text" name="food_name" value="<?= e($formData['food_name']) ?>" required>
                </label>
                <label>
                    Calories
                    <input type="number" name="calories" min="0" value="<?= e($formData['calories']) ?>" required>
                </label>
            </div>
            <label>
                Notes
                <textarea name="notes" placeholder="Portions, ingredients, or anything worth remembering"><?= e($formData['notes']) ?></textarea>
            </label>
            <div class="actions">
                <button type="submit"><?= $editId > 0 ? 'Update Meal' : 'Add Meal' ?></button>
                <?php if ($editId > 0): ?>
                    <a class="button-link button-secondary" href="<?= e(url('meals.php')) ?>">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </article>

    <article class="card">
        <h2>Meal history</h2>
        <?php if ($meals === []): ?>
            <p class="empty-state">No meals logged yet.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Meal</th>
                        <th>Food</th>
                        <th>Calories</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($meals as $meal): ?>
                        <tr>
                            <td><?= e($meal['meal_date']) ?></td>
                            <td><?= e($meal['meal_type']) ?></td>
                            <td><?= e($meal['food_name']) ?></td>
                            <td><?= e((string) $meal['calories']) ?></td>
                            <td class="actions">
                                <a class="button-link button-secondary" href="<?= e(url('meals.php?edit=' . $meal['id'])) ?>">Edit</a>
                                <form class="inline-form" method="POST" action="<?= e(url('meals.php')) ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= e((string) $meal['id']) ?>">
                                    <button class="button-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </article>
</section>

<?php require __DIR__ . '/views/partials/footer.php'; ?>
