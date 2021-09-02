<?php


namespace CD\Core;


class Response
{
    static public function redirect(string $location, int $code = 303)
    {
        header('Location: ' . URL_ROOT . trim_slashes($location), true, $code);
        exit;
    }

    static public function errorPage($code)
    {
        http_response_code($code);
        $error = '';
        $error_context = '';
        $icon = '';
        switch ($code) {
            case 404:
                $error = 'Bad Request';
                $error_context = 'Page does not exist.';
                $icon = 'error';
                break;
            case 500:
                $error = 'Internal server error';
                $error_context = 'Server is temporarily down.';
                $icon = 'block';
                break;
        }
        $context = [
            '_error' => [
                'code' => $code,
                'message' => $error,
                'context' => $error_context,
                'icon' => $icon
            ]
        ];
        View::renderTemplate("page_error.html.twig", $context);
    }
}