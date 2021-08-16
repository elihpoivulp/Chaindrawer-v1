<?php

use CD\Core\Request;

$public_folder = 'www';

if (!is_cli()) {
    define('URL_ROOT', sprintf(
        '%s%s',
        Request::getBaseURL(),
        substr($_SERVER['SCRIPT_NAME'], 0, (strpos($_SERVER['SCRIPT_NAME'], "/$public_folder") + 1))
    ));
}
const SOURCE_PATH = BASE_PATH . '/source';
const APP_PATH = SOURCE_PATH . '/App';
const VIEWS_PATH = APP_PATH . '/Views';
const MIGRATIONS_PATH = APP_PATH . '/Migrations';
const PUBLIC_PATH = BASE_PATH . '/www';
