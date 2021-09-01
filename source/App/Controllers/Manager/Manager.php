<?php

namespace CD\App\Controllers\Manager;

use CD\Core\Request;
use CD\Core\Sessions\Session;
use CD\Core\View;
use CD\Core\ViewControllers\ManagerViewOnly;
use CD\Models\SLP;
use Exception;

class Manager extends ManagerViewOnly
{
    static protected string $template_namespace = 'manager';

    public function __construct(array $params, View $view, Request $request)
    {
        parent::__construct($params, $view, $request);
        $this->registerPath(VIEWS_PATH . "/" . static::$template_namespace, static::$template_namespace);
    }

    public function indexAction()
    {
        $api = [];
        $slp = new SLP();
        $slp_data = $slp->getData();
        // $prices = $this->makeInterval(array_slice($slp_data['data'], round(count($slp_data['data']) / 2)));
        $prices = array_slice($slp_data['data'], round(count($slp_data['data']) / 2));
        $last_row = $prices[count($prices) - 1];
        $last_known_rate = round($last_row[1], 2);
        $cache_date = date('h:i A', ($last_row[0] / 1000));
        foreach ($prices as $arr) {
            // $date = date_format(date_create('@' . $arr[0] / 1000), 'c');
            // $api['data'][] = ['y' => $rate, 'x' => "$date"];
            $api['labels'][] = date('h:i A', $arr[0] / 1000);
            $api['data'][] = round($arr[1], 2);
        }
        $this->render('index.html.twig.draft', [
            'title' => 'Dashboard',
            'api' => [
                'has_data' => isset($api['labels']),
                'labels' => implode(', ', $api['labels']),
                'dataset' => implode(', ', $api['data']),
                'last_known_rate' => $last_known_rate,
                'cache_date' => $cache_date
            ],
            'account' => $this->account
        ]);
    }

    // private function makeInterval(array $data): array
    // {
    //     $prev = 0;
    //     $i = 0;
    //     $x = [];
    //     $now = date('Y-m-d');
    //     while (isset($data[$i])) {
    //         $curr = $data[$i][0] / 1000;
    //         if ($now === date('Y-m-d', $curr) && $curr - $prev > (60 * 5)) {
    //             $x[] = $data[$i];
    //             $prev = $curr;
    //         }
    //         $i++;
    //     }
    //     return $x;
    // }
}