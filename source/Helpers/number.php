<?php
function toShortFormat($num): string
{
    $suffixes = ["", "K", "M", "B","T"];
    $num = intval($num);
    $suffixNum = floor(strval(strlen($num)) / 3);
    $shortValue = round(($suffixNum != 0 ? ($num / pow(1000,$suffixNum)) : $num), 2);
    if ($shortValue % 1 != 0) {
        $shortValue = round($shortValue, 1);
    }
    return $shortValue . $suffixes[$suffixNum];
}