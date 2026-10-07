<?php

declare(strict_types=1);

require_once __DIR__ . '/../helpers/database.php';

function get_workouts_for_user(int $userId): array
{
    $statement = db()->prepare('SELECT id, workout_date, workout_type, duration_minutes, calories_burned, notes, created_at FROM workouts WHERE user_id = ? ORDER BY workout_date DESC, id DESC');
    $statement->bind_param('i', $userId);
    $statement->execute();
    $result = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    $statement->close();

    return $result;
}

function create_workout(int $userId, array $attributes): void
{
    $statement = db()->prepare(
        'INSERT INTO workouts (user_id, workout_date, workout_type, duration_minutes, calories_burned, notes) VALUES (?, ?, ?, ?, ?, ?)'
    );
    $duration = (int) $attributes['duration_minutes'];
    $calories = $attributes['calories_burned'] === '' ? null : (int) $attributes['calories_burned'];
    $notes = $attributes['notes'] === '' ? null : $attributes['notes'];

    $statement->bind_param(
        'ississ',
        $userId,
        $attributes['workout_date'],
        $attributes['workout_type'],
        $duration,
        $calories,
        $notes
    );
    $statement->execute();
    $statement->close();
}

function update_workout(int $userId, int $workoutId, array $attributes): void
{
    $statement = db()->prepare(
        'UPDATE workouts SET workout_date = ?, workout_type = ?, duration_minutes = ?, calories_burned = ?, notes = ? WHERE id = ? AND user_id = ?'
    );
    $duration = (int) $attributes['duration_minutes'];
    $calories = $attributes['calories_burned'] === '' ? null : (int) $attributes['calories_burned'];
    $notes = $attributes['notes'] === '' ? null : $attributes['notes'];

    $statement->bind_param(
        'ssiisii',
        $attributes['workout_date'],
        $attributes['workout_type'],
        $duration,
        $calories,
        $notes,
        $workoutId,
        $userId
    );
    $statement->execute();
    $statement->close();
}

function delete_workout(int $userId, int $workoutId): void
{
    $statement = db()->prepare('DELETE FROM workouts WHERE id = ? AND user_id = ?');
    $statement->bind_param('ii', $workoutId, $userId);
    $statement->execute();
    $statement->close();
}
