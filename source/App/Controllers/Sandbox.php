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

        echo '</pre>';
    }
}