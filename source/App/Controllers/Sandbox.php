<?php


namespace CD\App\Controllers;


use CD\Core\Controller;
use CD\Core\Sessions\SessionsUserAuth;

class Sandbox extends Controller
{
    public function index()
    {
        echo '<h1>Sandbox</h1><br>';
        echo '<pre>';
        // $cols = ['id' => 2, 'name' => 'wow'];
        //
        // $result = '';
        // foreach ($cols as $key => $value) {
        //     $result .= "$key = :$key AND ";
        // }
        // print_r($result);
        $money = '1000000.0';
        $a = new Haha();
        if (property_exists($a, 'a')) {
            echo 'a yes';
        } else {
            echo 'a no';
        }
        if (property_exists($a, 'b')) {
            echo 'b yes';
        } else {
            echo 'b no';
        }
        if (property_exists($a, 'd')) {
            echo 'd yes';
        } else {
            echo 'd no';
        }
        echo preg_match('/^\d+\.\d{2}/', $money);
        echo '</pre>';
    }
}

class Haha
{
    private $a = '';
    protected $b = '';
    public $d = '';
}