<?php

declare(strict_types=1);

function stream_fitness_report_pdf(string $filename, array $user, array $report): never
{
    $activityPages = array_chunk($report['activity'], 12);

    if ($activityPages === []) {
        $activityPages = [[]];
    }

    $pageStreams = [];
    $pageCount = count($activityPages);

    foreach ($activityPages as $pageIndex => $activity) {
        $pageStreams[] = build_report_page_stream($user, $report, $activity, $pageIndex + 1, $pageCount);
    }

    stream_pdf_document($filename, $pageStreams);
}

function build_report_page_stream(array $user, array $report, array $activity, int $pageNumber, int $pageCount): string
{
    $stream = '';
    $stream .= pdf_rect(0, 0, 612, 792, 'F7FAFC');
    $stream .= pdf_rect(0, 700, 612, 92, '174D49');
    $stream .= pdf_rect(34, 724, 54, 38, 'D8F3ED');
    $stream .= pdf_text('FT', 51, 737, 18, true, '174D49');
    $stream .= pdf_text('Fitness Tracker', 106, 750, 19, true, 'FFFFFF');
    $stream .= pdf_text((string) $report['title'], 106, 728, 12, false, 'D8F3ED');
    $stream .= pdf_text('Generated ' . date('Y-m-d H:i'), 430, 750, 9, false, 'D8F3ED');
    $stream .= pdf_text('Page ' . $pageNumber . ' of ' . $pageCount, 482, 34, 9, false, '64748B');

    if ($pageNumber === 1) {
        $stream .= pdf_meta_panel($user, $report);
        $stream .= pdf_summary_cards($report);
        $stream .= pdf_section_title('Activity details', 54, 416);
        $tableTop = 386;
    } else {
        $stream .= pdf_section_title('Activity details continued', 54, 656);
        $tableTop = 626;
    }

    $stream .= pdf_activity_table($activity, $tableTop);

    return $stream;
}

function pdf_meta_panel(array $user, array $report): string
{
    $goal = trim((string) ($user['fitness_goal'] ?? '')) !== '' ? (string) $user['fitness_goal'] : 'Not set';
    $stream = '';
    $stream .= pdf_rect(54, 618, 504, 58, 'FFFFFF');
    $stream .= pdf_stroke_rect(54, 618, 504, 58, 'DBE3EC');
    $stream .= pdf_text('Member', 76, 652, 8, true, '64748B');
    $stream .= pdf_text((string) $user['name'], 76, 634, 14, true, '172033');
    $stream .= pdf_text('Goal', 260, 652, 8, true, '64748B');
    $stream .= pdf_text(fit_pdf_text($goal, 28), 260, 634, 11, false, '172033');
    $stream .= pdf_text('Report period', 430, 652, 8, true, '64748B');
    $stream .= pdf_text($report['start_date'] . ' to ' . $report['end_date'], 430, 634, 10, false, '172033');

    return $stream;
}

function pdf_summary_cards(array $report): string
{
    $cards = [
        ['Workouts', (string) (int) $report['workouts']['total_count'], (int) $report['workouts']['total_duration'] . ' minutes'],
        ['Burned', (string) (int) $report['workouts']['total_calories'], 'calories'],
        ['Meals', (string) (int) $report['meals']['total_count'], (int) $report['meals']['total_calories'] . ' calories'],
        ['Avg weight', format_pdf_weight($report['progress']['avg_weight'] ?? null), 'progress summary'],
    ];

    $stream = pdf_section_title('Summary', 54, 574);
    $x = 54;

    foreach ($cards as $card) {
        $stream .= pdf_rect($x, 466, 116, 86, 'FFFFFF');
        $stream .= pdf_stroke_rect($x, 466, 116, 86, 'DBE3EC');
        $stream .= pdf_text($card[0], $x + 14, 526, 8, true, '64748B');
        $stream .= pdf_text($card[1], $x + 14, 500, 18, true, '174D49');
        $stream .= pdf_text($card[2], $x + 14, 482, 9, false, '64748B');
        $x += 129;
    }

    return $stream;
}

function pdf_section_title(string $title, int $x, int $y): string
{
    return pdf_text($title, $x, $y, 13, true, '172033') . pdf_rect($x, $y - 10, 52, 3, '17A589');
}

function pdf_activity_table(array $activity, int $top): string
{
    $stream = '';
    $stream .= pdf_rect(54, $top, 504, 24, '174D49');
    $stream .= pdf_text('Date', 68, $top + 8, 8, true, 'FFFFFF');
    $stream .= pdf_text('Type', 148, $top + 8, 8, true, 'FFFFFF');
    $stream .= pdf_text('Activity', 238, $top + 8, 8, true, 'FFFFFF');
    $stream .= pdf_text('Detail', 442, $top + 8, 8, true, 'FFFFFF');

    if ($activity === []) {
        $stream .= pdf_rect(54, $top - 42, 504, 42, 'FFFFFF');
        $stream .= pdf_stroke_rect(54, $top - 42, 504, 42, 'DBE3EC');
        $stream .= pdf_text('No workouts, meals, or progress logs were recorded in this period.', 70, $top - 18, 10, false, '64748B');

        return $stream;
    }

    $y = $top - 28;

    foreach ($activity as $index => $row) {
        $fill = $index % 2 === 0 ? 'FFFFFF' : 'F0F5F7';
        $stream .= pdf_rect(54, $y - 8, 504, 28, $fill);
        $stream .= pdf_stroke_rect(54, $y - 8, 504, 28, 'DBE3EC');
        $stream .= pdf_text((string) $row['activity_date'], 68, $y + 2, 9, false, '172033');
        $stream .= pdf_text((string) $row['activity_type'], 148, $y + 2, 9, true, '174D49');
        $stream .= pdf_text(fit_pdf_text((string) ($row['title'] ?: 'Untitled'), 34), 238, $y + 2, 9, false, '172033');
        $stream .= pdf_text(fit_pdf_text((string) ($row['detail'] ?: '-'), 18), 442, $y + 2, 9, false, '64748B');
        $y -= 28;
    }

    return $stream;
}

function stream_pdf_document(string $filename, array $pageStreams): never
{
    $objects = [];
    $objects[] = '<< /Type /Catalog /Pages 2 0 R >>';
    $objects[] = '';
    $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
    $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>';

    $pageObjectIds = [];

    foreach ($pageStreams as $pageStream) {
        $pageObjectIds[] = count($objects) + 1;
        $objects[] = sprintf(
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents %d 0 R >>',
            count($objects) + 2
        );
        $objects[] = "<< /Length " . strlen($pageStream) . " >>\nstream\n" . $pageStream . "\nendstream";
    }

    $objects[1] = sprintf(
        '<< /Type /Pages /Kids [%s] /Count %d >>',
        implode(' ', array_map(static fn (int $id): string => $id . ' 0 R', $pageObjectIds)),
        count($pageObjectIds)
    );

    $pdf = "%PDF-1.4\n";
    $offsets = [0];

    foreach ($objects as $index => $object) {
        $offsets[] = strlen($pdf);
        $pdf .= ($index + 1) . " 0 obj\n" . $object . "\nendobj\n";
    }

    $xrefOffset = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";

    for ($i = 1; $i <= count($objects); $i++) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
    }

    $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
    $pdf .= "startxref\n" . $xrefOffset . "\n%%EOF";

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . safe_pdf_filename($filename) . '"');
    header('Content-Length: ' . strlen($pdf));
    echo $pdf;
    exit;
}

function pdf_text(string $text, int $x, int $y, int $size = 10, bool $bold = false, string $color = '172033'): string
{
    [$r, $g, $b] = pdf_rgb($color);
    $font = $bold ? '/F2' : '/F1';

    return sprintf(
        "BT %.3F %.3F %.3F rg %s %d Tf 1 0 0 1 %d %d Tm (%s) Tj ET\n",
        $r,
        $g,
        $b,
        $font,
        $size,
        $x,
        $y,
        escape_pdf_text($text)
    );
}

function pdf_rect(int $x, int $y, int $width, int $height, string $color): string
{
    [$r, $g, $b] = pdf_rgb($color);

    return sprintf("%.3F %.3F %.3F rg %d %d %d %d re f\n", $r, $g, $b, $x, $y, $width, $height);
}

function pdf_stroke_rect(int $x, int $y, int $width, int $height, string $color): string
{
    [$r, $g, $b] = pdf_rgb($color);

    return sprintf("%.3F %.3F %.3F RG 0.8 w %d %d %d %d re S\n", $r, $g, $b, $x, $y, $width, $height);
}

function pdf_rgb(string $hex): array
{
    $hex = ltrim($hex, '#');

    return [
        hexdec(substr($hex, 0, 2)) / 255,
        hexdec(substr($hex, 2, 2)) / 255,
        hexdec(substr($hex, 4, 2)) / 255,
    ];
}

function fit_pdf_text(string $text, int $limit): string
{
    $text = trim($text);

    if (strlen($text) <= $limit) {
        return $text;
    }

    return substr($text, 0, max(0, $limit - 3)) . '...';
}

function format_pdf_weight(mixed $value): string
{
    if ($value === null) {
        return 'No data';
    }

    return number_format((float) $value, 1) . ' kg';
}

function escape_pdf_text(string $text): string
{
    $text = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
    $text = $text === false ? '' : $text;

    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
}

function safe_pdf_filename(string $filename): string
{
    $filename = preg_replace('/[^A-Za-z0-9._-]/', '-', $filename) ?: 'fitness-report.pdf';

    return str_ends_with($filename, '.pdf') ? $filename : $filename . '.pdf';
}
