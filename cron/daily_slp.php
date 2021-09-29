<?php

use CD\App\Cron\Cron;

require_once 'cron.php';

$cron = new Cron();
try {
    $cron->update_teams_data();
    $cron->update_daily_slp();
    $cron->update_team_claims();
} catch (Exception $e) {
    echo $e->getMessage();
    exit;
}
