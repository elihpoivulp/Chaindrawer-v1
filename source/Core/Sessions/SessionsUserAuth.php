<?php


namespace CD\Core\Sessions;

use CD\Config\Config;

class SessionsUserAuth
{
    static public function login(string $token): bool
    {
        if (!self::isLoggedIn()) {
            session_regenerate_id();
            Session::set('active_user_token', $token);
            Session::set('last_login', time());
            return true;
        }
        return false;
    }

    static public function isLoggedIn(): bool
    {
        return !is_null(self::getToken()) && Session::exists('active_user_token') && self::getToken() === Session::get('active_user_token') && self::lastLoginIsRecent();
    }

    static private function lastLoginIsRecent(): bool
    {
        if (!Session::exists('last_login') || Session::get('last_login') + Config::MAX_LOGIN_AGE < time()) {
            return false;
        }
        return true;
    }

    static public function logout(): bool
    {
        if (self::isLoggedIn()) {
            unset($_SESSION['active_user_token']);
            unset($_SESSION['last_login']);
        }
        return true;
    }

    static public function getToken(): ?string
    {
        if (Session::exists('active_user_token')) {
            return Session::get('active_user_token');
        }
        return null;
    }
}