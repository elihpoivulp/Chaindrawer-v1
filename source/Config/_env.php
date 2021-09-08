<?php

use Dotenv\Dotenv;

$env = Dotenv::createImmutable(BASE_PATH);
$env->load();
$env->required(['DB_DSN', 'DB_USER', 'DB_PASS', 'EMAIL_NOREPLY', 'EMAIL_NOREPLY_PASS', 'EMAIL_SMTP_HOST']);