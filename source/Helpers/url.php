<?php

use CD\Core\Request;

function trim_slashes(string $string): string
{
    return strlen($string) > 0 ? trim($string, '/') : $string;
}
function get_uri($pos = null)
{
    $r = new Request();
    $uri = $r->getPath();
    if (!is_null($pos)) {
        return explode('/', $uri)[$pos] ?? null;
    }
    return $uri;
}