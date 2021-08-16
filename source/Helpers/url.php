<?php

function trim_slashes(string $string): string
{
    return strlen($string) > 0 ? trim($string, '/') : $string;
}