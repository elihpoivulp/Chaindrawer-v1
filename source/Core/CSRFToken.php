<?php

namespace CD\Core;

use CD\Core\Sessions\Session;

class CSRFToken
{
    static public function generateToken()
    {
        if (!Session::exists('CDProtectionToken')) {
            Session::set('CDProtectionToken', base64_encode(openssl_random_pseudo_bytes(32)));
        }
        return Session::get('CDProtectionToken');
    }

    static public function verifyCSRFToken(string $request_token)
    {
        if (Session::exists('CDProtectionToken') && Session::get('CDProtectionToken') === $request_token) {
            unset($_SESSION['CDProtectionToken']);
            return true;
        }
        return false;
    }
}