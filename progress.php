<?php

declare(strict_types=1);

require_once __DIR__ . '/controllers/ProgressController.php';

require_auth();

$authUser = current_user();
$errors = [];
$formData = [
    'log_date' => date('Y-m-d'),
    'weight_kg' => '',
    'goal_note' => '',
    'notes' => '',
];
$editId = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$errors, $formData] = handle_progress_request((int) $authUser['id']);
}

$progressLogs = get_progress_logs_for_user((int) $authUser['id']);

if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    foreach ($progressLogs as $log) {
        if ((int) $log['id'] === $editId) {
            $formData = [
                'log_date' => $log['log_date'],
                'weight_kg' => (string) $log['weight_kg'],
                'goal_note' => (string) ($log['goal_note'] ?? ''),
                'notes' => (string) ($log['notes'] ?? ''),
            ];
            break;
        }
    }
}

$pageTitle = 'Progress';

require __DIR__ . '/views/partials/header.php';
require __DIR__ . '/views/partials/alerts.php';
?>

<section class="grid grid-2">
    <article class="card">
        <h2><?= $editId > 0 ? 'Edit progress entry' : 'Add progress entry' ?></h2>
        <form method="POST" action="<?= e(url('progress.php')) ?>">
            <input type="hidden" name="action" value="<?= $editId > 0 ? 'update' : 'create' ?>">
            <input type="hidden" name="id" value="<?= e((string) $editId) ?>">
            <div class="form-grid">
                <label>
                    Log date
                    <input type="date" name="log_date" value="<?= e($formData['log_date']) ?>" required>
                </label>
                <label>
                    Weight (kg)
                    <input type="number" step="0.1" min="0" name="weight_kg" value="<?= e($formData['weight_kg']) ?>" required>
                </label>
            </div>
            <label>
                Goal note
                <input type="text" name="goal_note" value="<?= e($formData['goal_note']) ?>" placeholder="Weekly target, milestone, or reflection">
            </label>
            <label>
                Notes
                <textarea name="notes" placeholder="How are you feeling, what changed, what helped?"><?= e($formData['notes']) ?></textarea>
            </label>
            <div class="actions">
                <button type="submit"><?= $editId > 0 ? 'Update Entry' : 'Add Entry' ?></button>
                <?php if ($editId > 0): ?>
                    <a class="button-link button-secondary" href="<?= e(url('progress.php')) ?>">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </article>

    <article class="card">
        <h2>Progress history</h2>
        <?php if ($progressLogs === []): ?>
            <p class="empty-state">No progress entries logged yet.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Weight</th>
                        <th>Goal note</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($progressLogs as $log): ?>
                        <tr>
                            <td><?= e($log['log_date']) ?></td>
                            <td><?= e((string) $log['weight_kg']) ?> kg</td>
                            <td><?= e($log['goal_note'] ?: '-') ?></td>
                            <td class="actions">
                                <a class="button-link button-secondary" href="<?= e(url('progress.php?edit=' . $log['id'])) ?>">Edit</a>
                                <form class="inline-form" method="POST" action="<?= e(url('progress.php')) ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= e((string) $log['id']) ?>">
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
