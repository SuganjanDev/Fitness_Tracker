<?php

declare(strict_types=1);

require_once __DIR__ . '/../helpers/bootstrap.php';
require_once __DIR__ . '/../models/ProgressLog.php';

function validate_progress_input(): array
{
    $data = [
        'log_date' => sanitize_text($_POST['log_date'] ?? ''),
        'weight_kg' => sanitize_text($_POST['weight_kg'] ?? ''),
        'goal_note' => sanitize_text($_POST['goal_note'] ?? ''),
        'notes' => sanitize_text($_POST['notes'] ?? ''),
    ];

    $errors = [];

    foreach ([
        validate_date_value('Log date', $data['log_date']),
        validate_positive_number('Weight', $data['weight_kg']),
    ] as $error) {
        if ($error !== null) {
            $errors[] = $error;
        }
    }

    return [$data, $errors];
}

function handle_progress_request(int $userId): array
{
    $action = $_POST['action'] ?? 'create';
    $logId = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete' && $logId > 0) {
        delete_progress_log($userId, $logId);
        flash('success', 'Progress entry deleted.');
        redirect('progress.php');
    }

    [$data, $errors] = validate_progress_input();

    if ($errors !== []) {
        return [$errors, $data];
    }

    if ($action === 'update' && $logId > 0) {
        update_progress_log($userId, $logId, $data);
        flash('success', 'Progress entry updated.');
    } else {
        create_progress_log($userId, $data);
        flash('success', 'Progress entry added.');
    }

    redirect('progress.php');
}
