<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers/bootstrap.php';
require_once __DIR__ . '/models/Dashboard.php';

require_auth();

$authUser = current_user();
$pageTitle = 'Dashboard';
$summary = get_dashboard_summary((int) $authUser['id']);
$recentActivity = get_recent_activity((int) $authUser['id']);
$reportPeriods = report_periods();
$weightChange = null;
$heightCm = isset($authUser['height_cm']) ? (float) $authUser['height_cm'] : null;
$profileWeight = isset($authUser['weight_kg']) ? (float) $authUser['weight_kg'] : null;
$bmi = calculate_bmi($heightCm, $profileWeight);
$bmiCategory = bmi_category($bmi);
$bmiValue = $bmi !== null ? number_format($bmi, 1) : null;
$bmiAngle = bmi_meter_angle($bmi);

if ($summary['latest_weight'] !== null && $summary['starting_weight'] !== null) {
    $weightChange = (float) $summary['latest_weight'] - (float) $summary['starting_weight'];
}

require __DIR__ . '/views/partials/header.php';
require __DIR__ . '/views/partials/alerts.php';
?>

<section class="dashboard-hero">
    <div>
        <p class="dashboard-kicker">Personal fitness snapshot</p>
        <h2>Track the habits that move your goal forward.</h2>
        <p class="muted">Your dashboard brings profile data, progress trends, and daily logging into one simple view.</p>
    </div>
    <div class="dashboard-hero-badge">
        <span>Goal</span>
        <strong><?= e($authUser['fitness_goal'] ?: 'Build a new goal') ?></strong>
    </div>
</section>

<section class="grid grid-3 dashboard-metrics">
    <article class="metric metric-workouts">
        <span class="metric-label">Total workouts</span>
        <strong><?= e((string) $summary['total_workouts']) ?></strong>
        <small>Sessions you have logged so far</small>
    </article>
    <article class="metric metric-meals">
        <span class="metric-label">Total meals logged</span>
        <strong><?= e((string) $summary['total_meals']) ?></strong>
        <small>Nutrition records saved to your plan</small>
    </article>
    <article class="metric metric-weight">
        <span class="metric-label">Latest weight</span>
        <strong><?= $summary['latest_weight'] !== null ? e((string) $summary['latest_weight']) . ' kg' : 'No entries yet' ?></strong>
        <small><?= $weightChange !== null ? e(number_format($weightChange, 1)) . ' kg overall change' : 'Add progress logs to see movement' ?></small>
    </article>
</section>

<section class="grid grid-2 dashboard-main">
    <article class="card profile-card">
        <h2>Profile snapshot</h2>
        <p class="muted">Keep your personal details updated so your dashboard stays meaningful.</p>
        <div class="profile-grid">
            <div class="profile-pill">
                <span>Goal</span>
                <strong><?= e($authUser['fitness_goal'] ?: 'Not set yet') ?></strong>
            </div>
            <div class="profile-pill">
                <span>Height</span>
                <strong><?= e($authUser['height_cm'] !== null ? (string) $authUser['height_cm'] . ' cm' : 'Not set yet') ?></strong>
            </div>
            <div class="profile-pill">
                <span>Profile weight</span>
                <strong><?= e($authUser['weight_kg'] !== null ? (string) $authUser['weight_kg'] . ' kg' : 'Not set yet') ?></strong>
            </div>
            <div class="profile-pill">
                <span>Weight change</span>
                <strong><?= $weightChange !== null ? e(number_format($weightChange, 1)) . ' kg' : 'Need more logs' ?></strong>
            </div>
        </div>
        <p class="profile-action"><a class="button-link" href="<?= e(url('profile.php')) ?>">Edit Profile</a></p>
    </article>

    <article class="card bmi-card">
        <h2>BMI calculator</h2>
        <?php if ($bmi === null): ?>
            <p class="empty-state">Add both height and weight in your profile, or use the BMI calculator.</p>
        <?php else: ?>
            <div class="bmi-meter-card compact" style="--bmi-angle: <?= e(number_format($bmiAngle, 1, '.', '')) ?>deg;">
                <div class="bmi-meter">
                    <div class="bmi-scale">
                        <span class="bmi-segment segment-low"></span>
                        <span class="bmi-segment segment-normal"></span>
                        <span class="bmi-segment segment-high"></span>
                        <span class="bmi-segment segment-obese"></span>
                    </div>
                    <div class="bmi-needle"></div>
                    <div class="bmi-center"></div>
                </div>
                <div class="bmi-meter-readout">
                    <span>Your BMI</span>
                    <strong><?= e($bmiValue) ?></strong>
                    <small><?= e($bmiCategory) ?></small>
                </div>
            </div>
        <?php endif; ?>
        <p class="profile-action"><a class="button-link" href="<?= e(url('bmi.php')) ?>">Open Calculator</a></p>
    </article>
</section>

<section class="grid grid-2 dashboard-lower">
    <article class="card report-card">
        <div class="section-heading">
            <div>
                <h2>PDF reports</h2>
                <p class="muted">Download a clean summary for the current day, week, month, or year.</p>
            </div>
        </div>
        <div class="report-actions">
            <?php foreach ($reportPeriods as $periodKey => $periodLabel): ?>
                <a class="button-link button-secondary report-button" href="<?= e(url('reports.php?period=' . $periodKey)) ?>">
                    <?= e($periodLabel) ?>
                    <span>PDF</span>
                </a>
            <?php endforeach; ?>
        </div>
    </article>

    <article class="card activity-card">
        <h2>Recent activity</h2>
        <?php if ($recentActivity === []): ?>
            <p class="empty-state">Start by adding a workout, meal, or progress log.</p>
        <?php else: ?>
            <ul class="activity-list">
                <?php foreach ($recentActivity as $activity): ?>
                    <li class="activity-item">
                        <strong><?= e($activity['activity_type']) ?></strong>
                        <div><?= e($activity['title'] ?: 'Progress update') ?></div>
                        <span class="muted"><?= e($activity['activity_date']) ?><?php if (!empty($activity['detail'])): ?> - <?= e($activity['detail']) ?><?php endif; ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </article>
</section>

<?php require __DIR__ . '/views/partials/footer.php'; ?>
