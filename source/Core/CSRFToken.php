<?php

namespace CD\Core;

use CD\Core\Sessions\Session;
use CD\Core\Utils\Token;
use Exception;

class CSRFToken
{
    /**
     * @throws Exception
     */
    static public function generateToken()
    {

        if (!Session::exists('CDProtectionToken')) {
            $token = new Token(bin2hex(random_bytes(32)));
            Session::set('CDProtectionToken', $token->getHash());
        }
        return Session::get('CDProtectionToken');
    }

    static public function verifyCSRFToken(string $request_token): bool
    {
        if (Session::exists('CDProtectionToken') && Session::get('CDProtectionToken') === $request_token) {
            unset($_SESSION['CDProtectionToken']);
            return true;
        }
        return false;
    }
}