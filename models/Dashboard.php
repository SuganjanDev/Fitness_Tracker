<?php

declare(strict_types=1);

require_once __DIR__ . '/../helpers/database.php';

function get_dashboard_summary(int $userId): array
{
    $summary = [
        'total_workouts' => 0,
        'total_meals' => 0,
        'latest_weight' => null,
        'starting_weight' => null,
    ];

    $statement = db()->prepare('SELECT COUNT(*) AS total FROM workouts WHERE user_id = ?');
    $statement->bind_param('i', $userId);
    $statement->execute();
    $summary['total_workouts'] = (int) $statement->get_result()->fetch_assoc()['total'];
    $statement->close();

    $statement = db()->prepare('SELECT COUNT(*) AS total FROM meals WHERE user_id = ?');
    $statement->bind_param('i', $userId);
    $statement->execute();
    $summary['total_meals'] = (int) $statement->get_result()->fetch_assoc()['total'];
    $statement->close();

    $statement = db()->prepare('SELECT weight_kg FROM progress_logs WHERE user_id = ? ORDER BY log_date DESC, id DESC LIMIT 1');
    $statement->bind_param('i', $userId);
    $statement->execute();
    $latest = $statement->get_result()->fetch_assoc();
    $summary['latest_weight'] = $latest['weight_kg'] ?? null;
    $statement->close();

    $statement = db()->prepare('SELECT weight_kg FROM progress_logs WHERE user_id = ? ORDER BY log_date ASC, id ASC LIMIT 1');
    $statement->bind_param('i', $userId);
    $statement->execute();
    $start = $statement->get_result()->fetch_assoc();
    $summary['starting_weight'] = $start['weight_kg'] ?? null;
    $statement->close();

    return $summary;
}

function get_recent_activity(int $userId, int $limit = 6): array
{
    $statement = db()->prepare(
        "SELECT activity_type, activity_date, title, detail
         FROM (
            SELECT 'Workout' AS activity_type, workout_date AS activity_date, workout_type AS title, CONCAT(duration_minutes, ' mins') AS detail, id
            FROM workouts WHERE user_id = ?
            UNION ALL
            SELECT 'Meal' AS activity_type, meal_date AS activity_date, food_name AS title, meal_type AS detail, id
            FROM meals WHERE user_id = ?
            UNION ALL
            SELECT 'Progress' AS activity_type, log_date AS activity_date, goal_note AS title, CONCAT(weight_kg, ' kg') AS detail, id
            FROM progress_logs WHERE user_id = ?
         ) AS activities
         ORDER BY activity_date DESC, id DESC
         LIMIT ?"
    );
    $statement->bind_param('iiii', $userId, $userId, $userId, $limit);
    $statement->execute();
    $activities = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    $statement->close();

    return $activities;
}

function report_periods(): array
{
    return [
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
        'yearly' => 'Yearly',
    ];
}

function get_report_period_range(string $period, ?DateTimeImmutable $today = null): array
{
    $today ??= new DateTimeImmutable('today');

    return match ($period) {
        'weekly' => [
            $today->modify('monday this week')->format('Y-m-d'),
            $today->modify('sunday this week')->format('Y-m-d'),
            'Weekly report',
        ],
        'monthly' => [
            $today->modify('first day of this month')->format('Y-m-d'),
            $today->modify('last day of this month')->format('Y-m-d'),
            'Monthly report',
        ],
        'yearly' => [
            $today->setDate((int) $today->format('Y'), 1, 1)->format('Y-m-d'),
            $today->setDate((int) $today->format('Y'), 12, 31)->format('Y-m-d'),
            'Yearly report',
        ],
        default => [
            $today->format('Y-m-d'),
            $today->format('Y-m-d'),
            'Daily report',
        ],
    };
}

function get_fitness_report_data(int $userId, string $period): array
{
    $periods = report_periods();
    $period = array_key_exists($period, $periods) ? $period : 'daily';
    [$startDate, $endDate, $title] = get_report_period_range($period);

    $workoutSummary = fetch_single_report_row(
        'SELECT COUNT(*) AS total_count, COALESCE(SUM(duration_minutes), 0) AS total_duration, COALESCE(SUM(calories_burned), 0) AS total_calories
         FROM workouts
         WHERE user_id = ? AND workout_date BETWEEN ? AND ?',
        $userId,
        $startDate,
        $endDate
    );

    $mealSummary = fetch_single_report_row(
        'SELECT COUNT(*) AS total_count, COALESCE(SUM(calories), 0) AS total_calories
         FROM meals
         WHERE user_id = ? AND meal_date BETWEEN ? AND ?',
        $userId,
        $startDate,
        $endDate
    );

    $progressSummary = fetch_single_report_row(
        'SELECT COUNT(*) AS total_count, MIN(weight_kg) AS min_weight, MAX(weight_kg) AS max_weight, AVG(weight_kg) AS avg_weight
         FROM progress_logs
         WHERE user_id = ? AND log_date BETWEEN ? AND ?',
        $userId,
        $startDate,
        $endDate
    );

    return [
        'period' => $period,
        'title' => $title,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'workouts' => $workoutSummary,
        'meals' => $mealSummary,
        'progress' => $progressSummary,
        'activity' => get_report_activity($userId, $startDate, $endDate),
    ];
}

function fetch_single_report_row(string $sql, int $userId, string $startDate, string $endDate): array
{
    $statement = db()->prepare($sql);
    $statement->bind_param('iss', $userId, $startDate, $endDate);
    $statement->execute();
    $row = $statement->get_result()->fetch_assoc() ?: [];
    $statement->close();

    return $row;
}

function get_report_activity(int $userId, string $startDate, string $endDate, int $limit = 14): array
{
    $statement = db()->prepare(
        "SELECT activity_type, activity_date, title, detail
         FROM (
            SELECT 'Workout' AS activity_type, workout_date AS activity_date, workout_type AS title, CONCAT(duration_minutes, ' mins') AS detail, id
            FROM workouts WHERE user_id = ? AND workout_date BETWEEN ? AND ?
            UNION ALL
            SELECT 'Meal' AS activity_type, meal_date AS activity_date, food_name AS title, CONCAT(meal_type, ' - ', calories, ' cal') AS detail, id
            FROM meals WHERE user_id = ? AND meal_date BETWEEN ? AND ?
            UNION ALL
            SELECT 'Progress' AS activity_type, log_date AS activity_date, COALESCE(goal_note, 'Progress update') AS title, CONCAT(weight_kg, ' kg') AS detail, id
            FROM progress_logs WHERE user_id = ? AND log_date BETWEEN ? AND ?
         ) AS activities
         ORDER BY activity_date DESC, id DESC
         LIMIT ?"
    );
    $statement->bind_param(
        'issississi',
        $userId,
        $startDate,
        $endDate,
        $userId,
        $startDate,
        $endDate,
        $userId,
        $startDate,
        $endDate,
        $limit
    );
    $statement->execute();
    $activities = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    $statement->close();

    return $activities;
}
