<?php


namespace CD\Core;


class Response
{
    static public function redirect(string $location, int $code = 303)
    {
        header('Location: ' . URL_ROOT . trim_slashes($location), true, $code);
        exit;
    }

    static public function errorPage($code, $return_url = '')
    {
        $error = '';
        $return_url = '';
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
            default:
                $code = 500;
                $error = 'An error has occurred.';
                $error_context = 'Error processing request. Please try again later.';
                $icon = 'error';
                break;
        }
        http_response_code($code);
        $context = [
            '_error' => [
                'code' => $code,
                'message' => $error,
                'context' => $error_context,
                'icon' => $icon
            ],
            'return_url' => $return_url
        ];
        View::renderTemplate("page_error.html.twig", $context);
        exit;
    }
}