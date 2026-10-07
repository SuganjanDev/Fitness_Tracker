<?php

declare(strict_types=1);

require_once __DIR__ . '/../helpers/bootstrap.php';

function handle_register_request(): array
{
    $errors = [];

    $data = [
        'name' => sanitize_text($_POST['name'] ?? ''),
        'email' => sanitize_text($_POST['email'] ?? ''),
        'password' => (string) ($_POST['password'] ?? ''),
        'age' => sanitize_text($_POST['age'] ?? ''),
        'gender' => sanitize_text($_POST['gender'] ?? ''),
        'height_cm' => sanitize_float($_POST['height_cm'] ?? ''),
        'weight_kg' => sanitize_float($_POST['weight_kg'] ?? ''),
        'fitness_goal' => sanitize_text($_POST['fitness_goal'] ?? ''),
    ];

    foreach ([
        validate_required('Name', $data['name']),
        validate_email($data['email']),
    ] as $error) {
        if ($error !== null) {
            $errors[] = $error;
        }
    }

    if (strlen($data['password']) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    if ($data['height_cm'] !== '' && validate_positive_number('Height', $data['height_cm']) !== null) {
        $errors[] = 'Height must be a valid positive number.';
    }

    if ($data['weight_kg'] !== '' && validate_positive_number('Weight', $data['weight_kg']) !== null) {
        $errors[] = 'Weight must be a valid positive number.';
    }

    if ($data['age'] !== '' && (!ctype_digit($data['age']) || (int) $data['age'] < 0)) {
        $errors[] = 'Age must be a valid positive whole number.';
    }

    if (find_user_by_email($data['email']) !== null) {
        $errors[] = 'That email address is already registered.';
    }

    if ($errors !== []) {
        store_old_input($data);
        return $errors;
    }

    $userId = create_user($data);
    $user = find_user_by_id($userId);

    clear_old_input();
    login_user($user);
    flash('success', 'Welcome to Fitness Tracker. Your account has been created.');
    redirect('dashboard.php');
}

function handle_login_request(): array
{
    $email = sanitize_text($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $errors = [];

    foreach ([validate_email($email), validate_required('Password', $password)] as $error) {
        if ($error !== null) {
            $errors[] = $error;
        }
    }

    $user = $email !== '' ? find_user_by_email($email) : null;

    if ($errors === [] && (!$user || !password_verify($password, $user['password']))) {
        $errors[] = 'The email and password combination is incorrect.';
    }

    if ($errors !== []) {
        store_old_input(['email' => $email]);
        return $errors;
    }

    clear_old_input();
    login_user($user);
    flash('success', 'You are now logged in.');
    redirect('dashboard.php');
}
