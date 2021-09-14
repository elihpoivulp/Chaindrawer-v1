<?php

namespace CD\App\Controllers\Manager;

use CD\Core\CSRFToken;
use CD\Core\Request;
use CD\Core\Response;
use CD\Core\View;
use CD\Core\ViewControllers\ManagerViewOnly;
use Exception;
use PDOException;
use TypeError;

class Account extends ManagerViewOnly
{
    static protected string $template_namespace = 'manager';
    public function __construct(array $params, View $view, Request $request)
    {
        parent::__construct($params, $view, $request);
        $this->registerPath(VIEWS_PATH . '/' . static::$template_namespace, static::$template_namespace);
    }

    public function editAction()
    {
        $errors = [];
        $success = [];
        $active_tab = 'email';
        $once = 'false';
        if ($this->request->isPost() && !$errors) {
            $submit_buttons = ['password-submit', 'username-submit', 'email-submit'];
            $body = $this->request->getBody();
            try {
                CSRFToken::verifyCSRFToken($body['_token'] ?? null);
            } catch (TypeError | Exception $e) {
                Response::errorPage(404);
            }
            if (!has_inclusion_of($body['submit'] ?? '', $submit_buttons)) {
                Response::errorPage(404);
            }
            $once = 'true';
            switch ($body['submit']) {
                case $submit_buttons[0]:
                    $active_tab = 'password';
                    $cleaned = filter_var_array($body, [
                       'old-password' => FILTER_SANITIZE_STRING,
                       'new-password' => FILTER_SANITIZE_STRING,
                       'confirm-new-password' => FILTER_SANITIZE_STRING,
                    ]);
                    if (
                        !(has_key_presence('old-password', $cleaned) || is_blank($cleaned['old-password'])) ||
                        !(has_key_presence('new-password', $cleaned) || is_blank($cleaned['new-password'])) ||
                        !(has_key_presence('confirm-new-password', $cleaned) || is_blank($cleaned['confirm-new-password']))
                    ) {
                        $errors['danger'] = 'Cannot update your password. Please try again later.';
                        break;
                    }
                    if (has_length_greater_than($cleaned['new-password'], 30)) {
                        $errors['password']['warning'] = 'Password cannot exceed 30 characters.';
                        break;
                    }
                    if (has_length_less_than($cleaned['new-password'], 8) || has_length_less_than($cleaned['confirm-new-password'], 8)) {
                        $errors['username']['warning'] = 'Your password must have at least 4 characters';
                        break;
                    }
                    if ($cleaned['new-password'] !== $cleaned['confirm-new-password']) {
                        $errors['password']['warning'] = 'Passwords do not match.';
                        break;
                    }
                    $result = $this->updatePassword($cleaned['old-password'], $cleaned['new-password']);
                    if (!is_bool($result)) {
                        $errors['password']['warning'] = $result;
                        break;
                    } else {
                        $success['password'] = 'Your password has been successfully updated.';
                    }
                    break;
                case $submit_buttons[1]:
                    $active_tab = 'username';
                    $cleaned = filter_var_array($body, ['username' => FILTER_SANITIZE_STRING]);
                    if (!(has_key_presence('username', $cleaned))) {
                        $errors['danger'] = 'Cannot update your username. Please try again later.';
                        break;
                    }
                    if (is_blank($cleaned['username'])) {
                        $errors['username']['warning'] = 'Username cannot be blank';
                        break;
                    }
                    if (has_length_greater_than($cleaned['username'], 20)) {
                        $errors['username']['warning'] = 'Username cannot exceed 20 characters';
                        break;
                    }
                    if (has_length_less_than($cleaned['username'], 4)) {
                        $errors['username']['warning'] = 'Username must have at least 4 characters';
                        break;
                    }
                    if ($cleaned['username'] === $this->account->LoginUsername) {
                        $success['username'] = 'You did not change your username; username not updated.';
                        break;
                    }
                    $result = $this->updateUsername($cleaned['username']);
                    if (!is_bool($result)) {
                        $errors['username']['warning'] = $result;
                        break;
                    } else {
                        $success['username'] = 'Your username has been successfully updated.';
                    }
                    break;
                case $submit_buttons[2]:
                    $active_tab = 'email';
                    $cleaned = filter_var_array($body, ['email' => FILTER_SANITIZE_EMAIL]);
                    if (!(has_key_presence('email', $cleaned))) {
                        $errors['danger'] = 'Cannot update your password. Please try again later.';
                        break;
                    }
                    if (is_blank($cleaned['email'])) {
                        $errors['email']['warning'] = 'Email cannot be blank';
                        break;
                    }
                    if (has_length_greater_than($cleaned['email'], 50)) {
                        $errors['email']['warning'] = 'Username cannot exceed 50 characters';
                        break;
                    }
                    if (has_length_less_than($cleaned['email'], 8)) {
                        $errors['email']['warning'] = 'Email must have at least 8 characters';
                        break;
                    }
                    if ($cleaned['email'] === $this->account->LoginEmail) {
                        $success['email'] = 'You did not change your email; email not updated.';
                        break;
                    }
                    $result = $this->updateEmail($cleaned['email']);
                    if (!is_bool($result)) {
                        $errors['email']['warning'] = $result;
                        break;
                    } else {
                        $success['email'] = 'Your email has been successfully updated.';
                    }
                    break;
            }
        }
        $this->render('account/edit.html.twig', [
            'account' => $this->account,
            'csrf' => CSRFToken::generateToken(),
            'errors' => $errors,
            'success' => $success,
            'active_tab' => $active_tab,
            'once' => $once
        ]);
    }

    private function updatePassword($old_password, $new_password)
    {
        if (!($new_password && $old_password) || !password_verify($old_password, $this->account->LoginHashedPassword)) {
            return 'Incorrect password';
        }
        $hashed = password_hash($new_password, 1);
        return $this->account->updatePassword($hashed) ? true : 'An error has occurred while trying to update your password. Please try again later.';
    }

    private function updateUsername($username)
    {
        if (!$username) {
            return 'Username cannot be blank';
        }
        $result = false;
        try {
            $result = $this->account->updateUsername(strip_tags($username));
        } catch (PDOException $e) {
            if (intval($e->errorInfo[1]) === 1062) {
                $result = 'Username is already taken.';
            }
        }
        return $result;
    }

    private function updateEmail($email)
    {
        if (!$email) {
            return 'Email cannot be blank';
        }
        $result = false;
        try {
            $result = $this->account->updateEmail(strip_tags($email));
        } catch (PDOException $e) {
            if (intval($e->errorInfo[1]) === 1062) {
                $result = 'Email is already taken.';
            }
        }
        return $result;
    }
}