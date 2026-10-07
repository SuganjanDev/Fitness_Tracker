<?php

declare(strict_types=1);

require_once __DIR__ . '/url.php';

function ensure_session_path(): void
{
    $config = app_config();
    $sessionPath = $config['session_path'] ?? null;

    if (!is_string($sessionPath) || $sessionPath === '') {
        return;
    }

    if (!is_dir($sessionPath)) {
        mkdir($sessionPath, 0775, true);
    }

    if (is_dir($sessionPath) && is_writable($sessionPath)) {
        session_save_path($sessionPath);
    }
}

function start_session_once(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        ensure_session_path();
        session_start();
    }
}

function flash(string $key, ?string $message = null): ?string
{
    start_session_once();

    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    if (!isset($_SESSION['flash'][$key])) {
        return null;
    }

    $value = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);

    return $value;
}

function old(string $key, string $default = ''): string
{
    start_session_once();
    return $_SESSION['old'][$key] ?? $default;
}

function store_old_input(array $input): void
{
    start_session_once();
    $_SESSION['old'] = $input;
}

function clear_old_input(): void
{
    start_session_once();
    unset($_SESSION['old']);
}
