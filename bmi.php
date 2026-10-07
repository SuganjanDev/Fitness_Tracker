<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers/bootstrap.php';

require_auth();

$authUser = current_user();
$pageTitle = 'BMI Calculator';
$heightValue = sanitize_float($_GET['height_cm'] ?? (string) ($authUser['height_cm'] ?? ''));
$weightValue = sanitize_float($_GET['weight_kg'] ?? (string) ($authUser['weight_kg'] ?? ''));
$errors = [];
$bmi = null;
$bmiValue = null;
$bmiCategory = null;
$bmiAngle = bmi_meter_angle(null);

if ($heightValue !== '' || $weightValue !== '') {
    $heightError = validate_positive_number('Height', $heightValue);
    $weightError = validate_positive_number('Weight', $weightValue);

    if ($heightError !== null) {
        $errors[] = $heightError;
    } elseif ((float) $heightValue <= 0) {
        $errors[] = 'Height must be greater than zero.';
    }

    if ($weightError !== null) {
        $errors[] = $weightError;
    } elseif ((float) $weightValue <= 0) {
        $errors[] = 'Weight must be greater than zero.';
    }

    if ($errors === []) {
        $bmi = calculate_bmi((float) $heightValue, (float) $weightValue);
        $bmiValue = $bmi !== null ? number_format($bmi, 1) : null;
        $bmiCategory = bmi_category($bmi);
        $bmiAngle = bmi_meter_angle($bmi);
    }
}

require __DIR__ . '/views/partials/header.php';
?>

<?php if ($errors !== []): ?>
    <div class="alert alert-error">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<section class="card bmi-calculator-card">
    <div class="section-heading">
        <div>
            <h2>Body mass meter</h2>
            <p class="muted">Enter your height and weight to calculate your Body Mass Index.</p>
        </div>
    </div>
    <form method="GET" action="<?= e(url('bmi.php')) ?>">
        <div class="form-grid">
            <label>
                Height (cm)
                <input type="number" step="0.1" min="0.1" name="height_cm" value="<?= e($heightValue) ?>" required>
            </label>
            <label>
                Weight (kg)
                <input type="number" step="0.1" min="0.1" name="weight_kg" value="<?= e($weightValue) ?>" required>
            </label>
        </div>
        <button type="submit">Calculate BMI</button>
    </form>

    <div class="bmi-meter-card bmi-calculator-result" style="--bmi-angle: <?= e(number_format($bmiAngle, 1, '.', '')) ?>deg;">
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
            <strong><?= e($bmiValue ?? '--') ?></strong>
            <small><?= e($bmiCategory ?? 'Add your measurements') ?></small>
        </div>
    </div>
</section>

<section class="card bmi-guide-card">
    <h2>BMI guide</h2>
    <div class="bmi-guide">
        <div><span>Underweight</span><strong>Below 18.5</strong></div>
        <div><span>Normal</span><strong>18.5 - 24.9</strong></div>
        <div><span>Overweight</span><strong>25.0 - 29.9</strong></div>
        <div><span>Obese</span><strong>30.0 and above</strong></div>
    </div>
</section>

<?php require __DIR__ . '/views/partials/footer.php'; ?>
