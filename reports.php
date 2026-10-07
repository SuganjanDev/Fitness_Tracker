<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers/bootstrap.php';
require_once __DIR__ . '/helpers/pdf.php';
require_once __DIR__ . '/models/Dashboard.php';

require_auth();

$authUser = current_user();
$periods = report_periods();
$period = (string) ($_GET['period'] ?? 'daily');
$period = array_key_exists($period, $periods) ? $period : 'daily';
$report = get_fitness_report_data((int) $authUser['id'], $period);

$filename = sprintf('fitness-%s-report-%s.pdf', $period, date('Y-m-d'));

stream_fitness_report_pdf($filename, $authUser, $report);
