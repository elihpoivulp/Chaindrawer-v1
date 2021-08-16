<?php
const BASE_PATH = __DIR__;

require_once 'vendor/autoload.php';
require_once 'source/bootstrap.php';

use CD\Core\DB\DB;

$db = new DB();
$db->applyMigrations();