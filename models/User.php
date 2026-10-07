<?php

declare(strict_types=1);

require_once __DIR__ . '/../helpers/database.php';

function find_user_by_id(int $id): ?array
{
    $statement = db()->prepare('SELECT id, name, email, age, gender, height_cm, weight_kg, fitness_goal, password, created_at, updated_at FROM users WHERE id = ? LIMIT 1');
    $statement->bind_param('i', $id);
    $statement->execute();
    $result = $statement->get_result()->fetch_assoc();
    $statement->close();

    return $result ?: null;
}

function find_user_by_email(string $email): ?array
{
    $statement = db()->prepare('SELECT id, name, email, age, gender, height_cm, weight_kg, fitness_goal, password, created_at, updated_at FROM users WHERE email = ? LIMIT 1');
    $statement->bind_param('s', $email);
    $statement->execute();
    $result = $statement->get_result()->fetch_assoc();
    $statement->close();

    return $result ?: null;
}

function create_user(array $attributes): int
{
    $statement = db()->prepare(
        'INSERT INTO users (name, email, password, age, gender, height_cm, weight_kg, fitness_goal) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );

    $passwordHash = password_hash($attributes['password'], PASSWORD_DEFAULT);
    $age = $attributes['age'] === '' ? null : (int) $attributes['age'];
    $gender = $attributes['gender'] === '' ? null : $attributes['gender'];
    $height = $attributes['height_cm'] === '' ? null : (float) $attributes['height_cm'];
    $weight = $attributes['weight_kg'] === '' ? null : (float) $attributes['weight_kg'];
    $goal = $attributes['fitness_goal'] === '' ? null : $attributes['fitness_goal'];

    $statement->bind_param(
        'sssisdds',
        $attributes['name'],
        $attributes['email'],
        $passwordHash,
        $age,
        $gender,
        $height,
        $weight,
        $goal
    );
    $statement->execute();
    $userId = db()->insert_id;
    $statement->close();

    return $userId;
}

function update_user_profile(int $userId, array $attributes): void
{
    $statement = db()->prepare(
        'UPDATE users SET name = ?, email = ?, age = ?, gender = ?, height_cm = ?, weight_kg = ?, fitness_goal = ? WHERE id = ?'
    );

    $age = $attributes['age'] === '' ? null : (int) $attributes['age'];
    $gender = $attributes['gender'] === '' ? null : $attributes['gender'];
    $height = $attributes['height_cm'] === '' ? null : (float) $attributes['height_cm'];
    $weight = $attributes['weight_kg'] === '' ? null : (float) $attributes['weight_kg'];
    $goal = $attributes['fitness_goal'] === '' ? null : $attributes['fitness_goal'];

    $statement->bind_param(
        'ssisddsi',
        $attributes['name'],
        $attributes['email'],
        $age,
        $gender,
        $height,
        $weight,
        $goal,
        $userId
    );
    $statement->execute();
    $statement->close();
}

function calculate_bmi(?float $heightCm, ?float $weightKg): ?float
{
    if ($heightCm === null || $weightKg === null || $heightCm <= 0 || $weightKg <= 0) {
        return null;
    }

    $heightM = $heightCm / 100;

    return $weightKg / ($heightM * $heightM);
}

function bmi_category(?float $bmi): string
{
    if ($bmi === null) {
        return 'Not available';
    }

    if ($bmi < 18.5) {
        return 'Underweight';
    }

    if ($bmi < 25) {
        return 'Normal';
    }

    if ($bmi < 30) {
        return 'Overweight';
    }

    if ($bmi < 35) {
        return 'Obese';
    }

    return 'Extremely obese';
}

function bmi_meter_angle(?float $bmi): float
{
    if ($bmi === null) {
        return -120.0;
    }

    if ($bmi < 18.5) {
        return bmi_map_range($bmi, 10.0, 18.5, -120.0, -72.0);
    }

    if ($bmi < 25.0) {
        return bmi_map_range($bmi, 18.5, 25.0, -72.0, -24.0);
    }

    if ($bmi < 30.0) {
        return bmi_map_range($bmi, 25.0, 30.0, -24.0, 24.0);
    }

    if ($bmi < 35.0) {
        return bmi_map_range($bmi, 30.0, 35.0, 24.0, 72.0);
    }

    return bmi_map_range(min($bmi, 45.0), 35.0, 45.0, 72.0, 120.0);
}

function bmi_map_range(float $value, float $inputMin, float $inputMax, float $outputMin, float $outputMax): float
{
    if ($inputMax <= $inputMin) {
        return $outputMin;
    }

    $clamped = max($inputMin, min($inputMax, $value));
    $ratio = ($clamped - $inputMin) / ($inputMax - $inputMin);

    return $outputMin + ($ratio * ($outputMax - $outputMin));
}
