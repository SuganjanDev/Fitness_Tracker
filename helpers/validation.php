<?php

declare(strict_types=1);

function sanitize_text(?string $value): string
{
    return trim((string) $value);
}

function sanitize_float(?string $value): string
{
    return trim((string) $value);
}

function validate_required(string $label, string $value): ?string
{
    return $value === '' ? $label . ' is required.' : null;
}

function validate_email(string $email): ?string
{
    if ($email === '') {
        return 'Email is required.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Enter a valid email address.';
    }

    return null;
}

function validate_positive_number(string $label, string $value): ?string
{
    if ($value === '') {
        return $label . ' is required.';
    }

    if (!is_numeric($value) || (float) $value < 0) {
        return $label . ' must be a valid positive number.';
    }

    return null;
}

function validate_date_value(string $label, string $value): ?string
{
    if ($value === '') {
        return $label . ' is required.';
    }

    $date = DateTime::createFromFormat('Y-m-d', $value);

    if (!$date || $date->format('Y-m-d') !== $value) {
        return $label . ' must be a valid date.';
    }

    return null;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
