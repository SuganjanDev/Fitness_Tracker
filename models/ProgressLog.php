<?php

declare(strict_types=1);

require_once __DIR__ . '/../helpers/database.php';

function get_progress_logs_for_user(int $userId): array
{
    $statement = db()->prepare('SELECT id, log_date, weight_kg, goal_note, notes, created_at FROM progress_logs WHERE user_id = ? ORDER BY log_date DESC, id DESC');
    $statement->bind_param('i', $userId);
    $statement->execute();
    $result = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    $statement->close();

    return $result;
}

function create_progress_log(int $userId, array $attributes): void
{
    $statement = db()->prepare(
        'INSERT INTO progress_logs (user_id, log_date, weight_kg, goal_note, notes) VALUES (?, ?, ?, ?, ?)'
    );
    $weight = (float) $attributes['weight_kg'];
    $goalNote = $attributes['goal_note'] === '' ? null : $attributes['goal_note'];
    $notes = $attributes['notes'] === '' ? null : $attributes['notes'];

    $statement->bind_param(
        'isdss',
        $userId,
        $attributes['log_date'],
        $weight,
        $goalNote,
        $notes
    );
    $statement->execute();
    $statement->close();
}

function update_progress_log(int $userId, int $logId, array $attributes): void
{
    $statement = db()->prepare(
        'UPDATE progress_logs SET log_date = ?, weight_kg = ?, goal_note = ?, notes = ? WHERE id = ? AND user_id = ?'
    );
    $weight = (float) $attributes['weight_kg'];
    $goalNote = $attributes['goal_note'] === '' ? null : $attributes['goal_note'];
    $notes = $attributes['notes'] === '' ? null : $attributes['notes'];

    $statement->bind_param(
        'sdssii',
        $attributes['log_date'],
        $weight,
        $goalNote,
        $notes,
        $logId,
        $userId
    );
    $statement->execute();
    $statement->close();
}

function delete_progress_log(int $userId, int $logId): void
{
    $statement = db()->prepare('DELETE FROM progress_logs WHERE id = ? AND user_id = ?');
    $statement->bind_param('ii', $logId, $userId);
    $statement->execute();
    $statement->close();
}
