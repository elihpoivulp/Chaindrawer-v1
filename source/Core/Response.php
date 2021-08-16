<?php


namespace CD\Core;


class Response
{
    static public function redirect(string $location, int $code = 303)
    {
        header('Location: ' . URL_ROOT . trim_slashes($location), true, $code);
        exit;
    }
}