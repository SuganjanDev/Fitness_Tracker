<?php

declare(strict_types=1);

require_once __DIR__ . '/../helpers/bootstrap.php';

function handle_profile_update(int $userId): array
{
    $errors = [];

    $data = [
        'name' => sanitize_text($_POST['name'] ?? ''),
        'email' => sanitize_text($_POST['email'] ?? ''),
        'age' => sanitize_text($_POST['age'] ?? ''),
        'gender' => sanitize_text($_POST['gender'] ?? ''),
        'height_cm' => sanitize_float($_POST['height_cm'] ?? ''),
        'weight_kg' => sanitize_float($_POST['weight_kg'] ?? ''),
        'fitness_goal' => sanitize_text($_POST['fitness_goal'] ?? ''),
    ];

    foreach ([validate_required('Name', $data['name']), validate_email($data['email'])] as $error) {
        if ($error !== null) {
            $errors[] = $error;
        }
    }

    if ($data['age'] !== '' && (!ctype_digit($data['age']) || (int) $data['age'] < 0)) {
        $errors[] = 'Age must be a valid positive whole number.';
    }

    if ($data['height_cm'] !== '' && validate_positive_number('Height', $data['height_cm']) !== null) {
        $errors[] = 'Height must be a valid positive number.';
    }

    if ($data['weight_kg'] !== '' && validate_positive_number('Weight', $data['weight_kg']) !== null) {
        $errors[] = 'Weight must be a valid positive number.';
    }

    $existingUser = find_user_by_email($data['email']);
    if ($existingUser !== null && (int) $existingUser['id'] !== $userId) {
        $errors[] = 'That email address is already in use by another account.';
    }

    if ($errors !== []) {
        return $errors;
    }

    update_user_profile($userId, $data);
    flash('success', 'Your profile has been updated.');

    return [];
}
