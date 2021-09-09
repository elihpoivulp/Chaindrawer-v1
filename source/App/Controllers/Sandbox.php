<?php


namespace CD\App\Controllers;


use CD\Core\Controller;

class Sandbox extends Controller
{
    public function index()
    {
        // echo '<h1>Sandbox</h1><br>';
        // echo '<pre>';
        // // $cols = ['id' => 2, 'name' => 'wow'];
        // //
        // // $result = '';
        // // foreach ($cols as $key => $value) {
        // //     $result .= "$key = :$key AND ";
        // // }
        // // print_r($result);
        // $money = '1000000.0';
        // echo preg_match('/^\d+\.\d{2}/', $money);
        // echo '</pre>';
        $this->render('withdraw_request_email_template.html.twig', [
            'name' => 'Nicko Gamba',
            'slp_balance' => 100,
            'axs_balance' => 100,
            'method' => 'emoney',
            '_type' => 'emoney',
            'phone_number' => 1,
            'slp_amt' => 1,
            'axs_amt' => 1,
            'request' => 1
        ]);
    }
}
