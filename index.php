<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers/bootstrap.php';

if (current_user() !== null) {
    redirect('dashboard.php');
}

redirect('login.php');
