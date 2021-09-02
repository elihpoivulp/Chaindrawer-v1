<?php


namespace CD\Core;

use CD\Config\Config;
use ErrorException;
use Exception;

class Error
{
    /**
     * @throws ErrorException
     */
    static public function errorHandler(int $level, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $errno)) {
            return false;
        }
        throw new ErrorException($message, 0, $level, $file, $line);
    }

    /**
     * @throws Exception
     */
    static public function exceptionHandler($exception)
    {
        $code = $exception->getCode();
        if ($code != 404) {
            if ($code != 403) {
                $code = 500;
            }
        }
        if (Config::DEBUG()) {
            http_response_code($code);
            $msg = "<h1>Fatal error</h1>";
            $msg .= "<p>Uncaught Exception: " . get_class($exception) . "</p>";
            $msg .= "<p>Message: <strong>{$exception->getMessage()}</strong></p>";
            $msg .= "<p>Stack trace: <pre>Stack trace: {$exception->getTraceAsString()}</pre></p>";
            $msg .= "<p>Thrown in: {$exception->getFile()} on line {$exception->getLine()}</p>";
            echo $msg;
        } else {
            $log = BASE_PATH . '/logs/' . date('Y-m-d') . '.txt';
            ini_set('error_log', $log);
            $msg = "Uncaught Exception: " . get_class($exception);
            $msg .= " with message: '{$exception->getMessage()}'";
            $msg .= "\nStack trace: Stack trace: {$exception->getTraceAsString()}";
            $msg .= "\nThrown in: {$exception->getFile()} on line {$exception->getLine()}";
            $msg .= "\n";
            error_log($msg);
            Response::errorPage($code == 403 ? 404 : $code);
        }
    }
}