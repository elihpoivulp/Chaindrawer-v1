<?php

namespace CD\App\Cron;

use CD\Models\CronJobs;

class Cron
{
    private CronJobs $model;

    public function __construct()
    {
        $this->model = new CronJobs();
    }

    public function test()
    {
        try {
            print_r($_ENV);
            if (!is_cli()) {
                echo 'This page does not exist' . PHP_EOL;
                http_response_code(404);
                exit;
            }
            $s = $this->model->test();
        } catch (\PDOException | \Exception $e) {
            echo $e->getMessage();
            exit;
        }
        if ($s) {
            echo 'nice';
        } else {
            echo 'woah';
        }
    }
}