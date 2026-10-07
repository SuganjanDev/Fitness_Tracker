<?php

declare(strict_types=1);

require_once __DIR__ . '/../helpers/bootstrap.php';
require_once __DIR__ . '/../models/Workout.php';

function validate_workout_input(): array
{
    $data = [
        'workout_date' => sanitize_text($_POST['workout_date'] ?? ''),
        'workout_type' => sanitize_text($_POST['workout_type'] ?? ''),
        'duration_minutes' => sanitize_text($_POST['duration_minutes'] ?? ''),
        'calories_burned' => sanitize_text($_POST['calories_burned'] ?? ''),
        'notes' => sanitize_text($_POST['notes'] ?? ''),
    ];

    $errors = [];

    foreach ([
        validate_date_value('Workout date', $data['workout_date']),
        validate_required('Workout type', $data['workout_type']),
        validate_positive_number('Duration', $data['duration_minutes']),
    ] as $error) {
        if ($error !== null) {
            $errors[] = $error;
        }
    }

    if ($data['calories_burned'] !== '' && validate_positive_number('Calories burned', $data['calories_burned']) !== null) {
        $errors[] = 'Calories burned must be a valid positive number.';
    }

    return [$data, $errors];
}

function handle_workout_request(int $userId): array
{
    $action = $_POST['action'] ?? 'create';
    $workoutId = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete' && $workoutId > 0) {
        delete_workout($userId, $workoutId);
        flash('success', 'Workout deleted.');
        redirect('workouts.php');
    }

    [$data, $errors] = validate_workout_input();

    if ($errors !== []) {
        return [$errors, $data];
    }

    if ($action === 'update' && $workoutId > 0) {
        update_workout($userId, $workoutId, $data);
        flash('success', 'Workout updated.');
    } else {
        create_workout($userId, $data);
        flash('success', 'Workout added.');
    }

    redirect('workouts.php');
}
