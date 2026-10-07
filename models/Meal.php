<?php

declare(strict_types=1);

require_once __DIR__ . '/../helpers/database.php';

function get_meals_for_user(int $userId): array
{
    $statement = db()->prepare('SELECT id, meal_date, meal_type, food_name, calories, notes, created_at FROM meals WHERE user_id = ? ORDER BY meal_date DESC, id DESC');
    $statement->bind_param('i', $userId);
    $statement->execute();
    $result = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    $statement->close();

    return $result;
}

function create_meal(int $userId, array $attributes): void
{
    $statement = db()->prepare(
        'INSERT INTO meals (user_id, meal_date, meal_type, food_name, calories, notes) VALUES (?, ?, ?, ?, ?, ?)'
    );
    $calories = (int) $attributes['calories'];
    $notes = $attributes['notes'] === '' ? null : $attributes['notes'];

    $statement->bind_param(
        'isssis',
        $userId,
        $attributes['meal_date'],
        $attributes['meal_type'],
        $attributes['food_name'],
        $calories,
        $notes
    );
    $statement->execute();
    $statement->close();
}

function update_meal(int $userId, int $mealId, array $attributes): void
{
    $statement = db()->prepare(
        'UPDATE meals SET meal_date = ?, meal_type = ?, food_name = ?, calories = ?, notes = ? WHERE id = ? AND user_id = ?'
    );
    $calories = (int) $attributes['calories'];
    $notes = $attributes['notes'] === '' ? null : $attributes['notes'];

    $statement->bind_param(
        'sssisii',
        $attributes['meal_date'],
        $attributes['meal_type'],
        $attributes['food_name'],
        $calories,
        $notes,
        $mealId,
        $userId
    );
    $statement->execute();
    $statement->close();
}

function delete_meal(int $userId, int $mealId): void
{
    $statement = db()->prepare('DELETE FROM meals WHERE id = ? AND user_id = ?');
    $statement->bind_param('ii', $mealId, $userId);
    $statement->execute();
    $statement->close();
}
