<?php

declare(strict_types=1);

function app_config(): array
{
    static $config = null;

    if ($config === null) {
        $config = require __DIR__ . '/../config/app.php';
    }

    return $config;
}

function url(string $path = ''): string
{
    $configuredBaseUrl = trim((string) (app_config()['base_url'] ?? ''));
    $baseUrl = $configuredBaseUrl !== '' ? rtrim($configuredBaseUrl, '/') : rtrim(detect_base_url(), '/');
    $path = ltrim($path, '/');

    return $path === '' ? $baseUrl . '/' : $baseUrl . '/' . $path;
}

function detect_base_url(): string
{
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

    if ($scriptName === '') {
        return '';
    }

    $directory = str_replace('\\', '/', dirname($scriptName));

    return $directory === '/' || $directory === '.' ? '' : $directory;
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}
