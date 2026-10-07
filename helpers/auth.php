<?php

declare(strict_types=1);

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/url.php';
require_once __DIR__ . '/../models/User.php';

function current_user(): ?array
{
    start_session_once();

    if (empty($_SESSION['user_id'])) {
        return null;
    }

    return find_user_by_id((int) $_SESSION['user_id']);
}

function auth_user_id(): ?int
{
    $user = current_user();
    return $user['id'] ?? null;
}

function is_guest(): bool
{
    return current_user() === null;
}

function login_user(array $user): void
{
    start_session_once();
    $_SESSION['user_id'] = (int) $user['id'];
}

function logout_user(): void
{
    start_session_once();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
    }

    session_destroy();
}

function require_guest(): void
{
    if (!is_guest()) {
        redirect('dashboard.php');
    }
}

function require_auth(): void
{
    if (is_guest()) {
        flash('error', 'Please log in to continue.');
        redirect('login.php');
    }
}
