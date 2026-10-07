<?php

declare(strict_types=1);

require_once __DIR__ . '/../helpers/bootstrap.php';
require_once __DIR__ . '/../models/Meal.php';

function validate_meal_input(): array
{
    $data = [
        'meal_date' => sanitize_text($_POST['meal_date'] ?? ''),
        'meal_type' => sanitize_text($_POST['meal_type'] ?? ''),
        'food_name' => sanitize_text($_POST['food_name'] ?? ''),
        'calories' => sanitize_text($_POST['calories'] ?? ''),
        'notes' => sanitize_text($_POST['notes'] ?? ''),
    ];

    $errors = [];

    foreach ([
        validate_date_value('Meal date', $data['meal_date']),
        validate_required('Meal type', $data['meal_type']),
        validate_required('Food name', $data['food_name']),
        validate_positive_number('Calories', $data['calories']),
    ] as $error) {
        if ($error !== null) {
            $errors[] = $error;
        }
    }

    return [$data, $errors];
}

function handle_meal_request(int $userId): array
{
    $action = $_POST['action'] ?? 'create';
    $mealId = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete' && $mealId > 0) {
        delete_meal($userId, $mealId);
        flash('success', 'Meal deleted.');
        redirect('meals.php');
    }

    [$data, $errors] = validate_meal_input();

    if ($errors !== []) {
        return [$errors, $data];
    }

    if ($action === 'update' && $mealId > 0) {
        update_meal($userId, $mealId, $data);
        flash('success', 'Meal updated.');
    } else {
        create_meal($userId, $data);
        flash('success', 'Meal added.');
    }

    redirect('meals.php');
}
