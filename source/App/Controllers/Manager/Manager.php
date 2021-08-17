<?php

namespace CD\App\Controllers\Manager;

use Codenixsv\CoinGeckoApi\CoinGeckoClient;
use CD\Config\Config;
use CD\Core\Request;
use CD\Core\Sessions\Session;
use CD\Core\View;
use CD\Core\ViewControllers\ManagerViewOnly;
use Exception;

class Manager extends ManagerViewOnly
{
    static protected string $namespace = 'manager';

    public function __construct(array $params, View $view, Request $request)
    {
        parent::__construct($params, $view, $request);
        $this->registerPath(VIEWS_PATH . "/" . static::$namespace, static::$namespace);
    }

    public function indexAction()
    {
        if ($this->user->manager) {
            $account = $this->user;
        } else {
            // TODO: Show error instead of exit
            exit('This user has a Manager role but has no Manager Account');
        }
        $monthly = $account->manager->getPayouts();
        $monthly_payouts = [];
        $labels = '';
        foreach ($monthly as $payout) {
            $y = $payout['ManagerPayoutAmount'];
            $labels .= date_format(date_create($payout['ManagerPayoutDateReceived']), 'M') . ', ';
            $x = date_format(date_create($payout['ManagerPayoutDateReceived']), 'Y-m-d');
            $monthly_payouts['data'][] = ['y' => $y, 'x' => $x];
        }
        $monthly_payouts[] = 23;
        $monthly_payouts[] = .5;
        $monthly = [
            'payouts' => str_replace(['"0"', '"1"'], ['"barThickness"', '"barPercentage"'], json_encode($monthly_payouts)),
            'labels' => rtrim($labels, ', ')
        ];
        $contract = strtolower(Config::SLP_CONTRACT_ADDRESS);
        $data['today'] = date('F j, o');
        $api = [];
        $data['current_rate'] = 0.00;
        try {
            // TODO: Add table for caching previous results
            // $client = new CoinGeckoClient();
            // $data['current_rate'] = $client->simple()->getTokenPrice('ethereum', $contract, 'php')[$contract]['php'];
            // $prices = $client->contract()->getMarketChart('ethereum', $contract, 'php', '3')['prices'];
            // foreach ($prices as $arr) {
            //     // ManagerPayoutAmount
            //     $date = date_format(date_create('@'. $arr[0] / 1000), 'c');
            //     $rate = round($arr[1], 2);
            //     $api['data'][] = ['y' => $rate, 'x' => "$date"];
            // }
        } catch (Exception $e) {
            // TODO: show cached data
            Session::setFlash('toastr', 'We\'ve encountered a minor problem, but do not fret, this is not your fault.', [
                'type' => Session::FLASH_TYPE_WARNING,
                'title' => 'Error',
                'dismissable' => true
            ]);
        }

        $api['data'][] = ['y' => $data['current_rate'], 'x' => date_format(date_create('@'. strtotime('now')), 'c')];
        $data['api'] = json_encode($api);
        $this->render('index.html.twig', [
            'title' => 'Dashboard',
            'data' => $data,
            'account' => $account,
            'monthly_payouts' => $monthly
        ]);
    }
}