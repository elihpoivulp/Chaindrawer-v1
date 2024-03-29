<?php

function can_withdraw(): bool
{
    $time_now = date('H');
    $day_now = date('D');
    return (($time_now > 8 && $time_now < 17) && !has_inclusion_of($day_now, ['Sat', 'Sun']));
}

function is_blank(string $value): bool
{
    return !isset($value) || trim($value) === '';
}

function has_length_greater_than(string $value, int $min): bool
{
    $length = strlen($value);
    return $length > $min;
}

function has_length_less_than(string $value, int $max): bool
{
    $length = strlen($value);
    return $length < $max;
}

function has_length_exactly(string $value, int $exact): bool
{
    $length = strlen($value);
    return $length === $exact;
}

function has_value_exactly(string $value, string $compare): bool
{
    return $value === $compare;
}

function has_length(string $value, array $options): bool
{
    if (isset($options['min']) && !has_length_greater_than($value, $options['min'] - 1)) {
        return false;
    } elseif (isset($options['max']) && !has_length_less_than($value, $options['max'] + 1)) {
        return false;
    } elseif (isset($options['exact']) && !has_length_exactly($value, $options['exact'])) {
        return false;
    }
    return true;
}

function has_inclusion_of(string $value, array $set): bool
{
    return in_array($value, $set);
}

function has_exclusion_of(string $value, array $set): bool
{
    return !in_array($value, $set);
}

function has_key_presence(string $key, array $set): bool
{
    return array_key_exists($key, $set);
}

function has_string(string $value, string $required_string): bool
{
    return str_contains($value, $required_string);
}

function has_valid_email_format(string $value): bool
{
    // $email_regex = '/\A[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\Z/i';
    // return preg_match($email_regex, $value) === 1;
    return filter_var($value, FILTER_VALIDATE_EMAIL);
}

function is_cli(): bool
{
    // if (defined('STDIN') || (empty($_SERVER['REMOTE_ADDR'] && !isset($_SERVER['HTTP_USER_AGENT']))) || (php_sapi_name() === 'cli')) {
    //     return true;
    // }
    if (php_sapi_name() === 'cli') {
        return true;
    }
    return false;
}

function valid_slug(string $text): bool
{
    return preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $text);
}