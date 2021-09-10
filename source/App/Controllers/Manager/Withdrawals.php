<?php

namespace CD\App\Controllers\Manager;

use CD\Config\Config;
use CD\Core\CSRFToken;
use CD\Core\Request;
use CD\Core\Response;
use CD\Core\Sessions\Session;
use CD\Core\View;
use CD\Core\ViewControllers\ManagerViewOnly;
use Exception;
use PHPMailer\PHPMailer\PHPMailer;
use TypeError;

class Withdrawals extends ManagerViewOnly
{
    public const ALLOWED_WITHDRAWAL_METHODS = ['binance', 'bank', 'emoney'];

    static protected string $template_namespace = 'manager';

    public function __construct(array $params, View $view, Request $request)
    {
        parent::__construct($params, $view, $request);
        $this->registerPath(VIEWS_PATH . "/" . static::$template_namespace, static::$template_namespace);
        $this->loadModel('Withdrawals');
    }

    public function indexAction()
    {

        // $file = fopen(BASE_PATH . '/source/withdrawal.csv', 'r');
        // $header = null;
        // $data = [];
        // while (!feof($file)) {
        //     $row = fgetcsv($file);
        //     array_splice($row, 6);
        //     if ($row == [null] || $row === false) continue;
        //     if (!$header) {
        //         $header = $row;
        //     } else {
        //         $data[] = array_combine($header, $row);
        //     }
        // }
        // fclose($file);
        //
        // echo '<pre>';
        // print_r($data);
        // echo '</pre>';
        // exit;

        $slp_bal = Session::get('_w_s') ?? 0;
        $axs_bal = Session::get('_w_a') ?? 0;
        $banks = [];
        $emoneys = [];
        $next = Session::get('_w_n') ?? false;
        $err = [
            's' => false,
            'a' => false
        ];
        if ($this->request->isPost()) {
            $p = $this->request->getBody();
            try {
                CSRFToken::verifyCSRFToken($p['_token'] ?? null);
            } catch (TypeError | Exception $e) {
                Response::errorPage(404);
            }
            if (has_key_presence('reset', $p)) {
                $slp_bal = 0;
                $axs_bal = 0;
                $banks = [];
                $emoneys = [];
                $next = false;
                $err = [
                    's' => false,
                    'a' => false
                ];
                if (Session::exists('_w_s')) {
                    unset($_SESSION['_w_s']);
                }
                if (Session::exists('_w_a')) {
                    unset($_SESSION['_w_a']);
                }
                if (Session::exists('_w_n')) {
                    unset($_SESSION['_w_n']);
                }
            }
            if (has_key_presence('s', $p) && $p['s']) {
                $s_post = intval(preg_replace('/\D/', '', $_POST['s']));
                if ($s_post > 0 && $s_post <= $this->account->manager->ManagerAccountCurrentSLPBalance) {
                    Session::set('_w_s', $s_post);
                    $next = true;
                } else {
                    $next = false;
                    $err['s'] = true;
                }
                Session::set('_w_n', $next);
                $slp_bal = $s_post;
            }
            if (has_key_presence('a', $p) && $p['a']) {
                $a_post = intval(preg_replace('/\D/', '', $_POST['a']));
                if ($a_post > 0 && $a_post <= $this->account->manager->ManagerAccountCurrentAXSBalance) {
                    Session::set('_w_a', $a_post);;
                    $next = true;
                } else {
                    if ($a_post !== 0) {
                        $err['a'] = true;
                        $next = false;
                    }
                }
                Session::set('_w_n', $next);
                $axs_bal = $a_post;
            }
        }
        if ($next) {
            $banks = $this->model->getAllBanks();
            $emoneys = $this->model->getAllEMoneys();
        }
        $status = false;
        $message = false;
        if (Session::exists('_w_status')) {
            $status = Session::get('_w_status');
            $message = true;
            unset($_SESSION['_w_status']);
        }
        $template = 'withdraw.html.twig';
        if ($this->account->manager->hasPendingWithdrawal()) {
            $template = 'has_pending.html.twig';
        }
        $this->render('withdrawals/'.$template, [
            'account' => $this->account,
            'slp_amt' => $slp_bal,
            'axs_amt' => $axs_bal,
            'err' => $err,
            'next' => $next,
            'banks' => $banks,
            'emoneys' => $emoneys,
            'token' => CSRFToken::generateToken(),
            'status' => $status,
            'message' => $message
        ]);
    }

    public function processAction()
    {
        // if ($this->request->isPost() && !$this->account->manager->hasPendingWithdrawal()) {
        if ($this->request->isPost()) {
            $p = $_POST;
            $message = [];
            $keys = [];
            $method = '';
            $error = false;
            try {
                CSRFToken::verifyCSRFToken($p['_token'] ?? null);
            } catch (TypeError | Exception $e) {
                Response::errorPage(404);
            }
            if (has_key_presence('_method', $p) && has_inclusion_of($p['_method'], self::ALLOWED_WITHDRAWAL_METHODS)) {
                $m = $p['_method'];
                $selection = $p[$m];
                if ($m !== 'binance') {
                    if (!has_key_presence('payment', $selection) || !preg_match('/\d+/', $selection['payment'])) {
                        $error = true;
                    }
                }
                if (!$error) {
                    $message['slp_balance'] = $this->account->manager->ManagerAccountCurrentSLPBalance;
                    $message['axs_balance'] = $this->account->manager->ManagerAccountCurrentAXSBalance;
                    switch (strtolower($p['_method'])) {
                        case self::ALLOWED_WITHDRAWAL_METHODS[0]:
                            $keys = ['email'];
                            $method = self::ALLOWED_WITHDRAWAL_METHODS[0];
                            break;
                        case self::ALLOWED_WITHDRAWAL_METHODS[1]:
                            $keys = ['payment', 'phone_number', 'account_name', 'account_number'];
                            $bank = $this->model->getBankByID(intval($selection['payment']))['BankName'];
                            $method = $bank . ' (' . self::ALLOWED_WITHDRAWAL_METHODS[1] . ')';
                            break;
                        case self::ALLOWED_WITHDRAWAL_METHODS[2]:
                            $keys = ['payment', 'phone_number', 'name'];
                            $provider = $this->model->getEmoneyByID(intval($selection['payment']))['EMoneyName'];
                            $method = $provider . ' (' . self::ALLOWED_WITHDRAWAL_METHODS[2] . ')';
                            break;

                    }
                    $message['name'] = $this->account->getUserFullName();
                    $message['method'] = strtoupper($method);
                    $message['_type'] = strtolower($m);
                    $all_exist = $this->allFieldsExist($selection, $keys);
                    $s = 0;
                    $a = 0;
                    if ($all_exist) {
                        if (Session::exists('_w_s')) {
                            if (!has_key_presence('_slp_amt', $p) || !preg_match('/\d+/', $p['_slp_amt'])) {
                                $error = true;
                            } else {
                                $s = $p['_slp_amt'];
                                if ($s != Session::get('_w_s') || $s > $this->account->manager->ManagerAccountCurrentSLPBalance) {
                                    $error = true;
                                }
                            }
                        }
                        if (Session::exists('_w_a')) {
                            if (!has_key_presence('_axs_amt', $p) || !preg_match('/\d+/', $p['_axs_amt'])) {
                                $error = true;
                            } else {
                                $a = $p['_axs_amt'];
                                if ($a != Session::get('_w_a') || $a > $this->account->manager->ManagerAccountCurrentAXSBalance) {
                                    $error = true;
                                }
                            }
                        }
                        if (!$error) {
                            $msg = '';
                            Session::set('_w_status', false);
                            foreach ($selection as $key => $data) {
                                if ($key !== 'payment') {
                                    $message[$key] = htmlspecialchars($data);
                                    $msg .= strtoupper($key) . ':' . htmlspecialchars($data) . '<br>';
                                }
                            }
                            $message['slp_amt'] = $s;
                            $message['axs_amt'] = $a;
                            $mail = new PHPMailer(true);
                            try {
                                $mail->isSMTP();
                                $mail->Host = Config::EMAIL_SMTP_HOST();
                                $mail->SMTPAuth = true;
                                $mail->Username = Config::EMAIL_NO_REPLY()[0];
                                $mail->Password = Config::EMAIL_NO_REPLY()[1];
                                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                                $mail->Port = 465;
                                $mail->setFrom(Config::EMAIL_NO_REPLY()[0], 'Chaindrawer');
                                $mail->addAddress('social.npg@gmail.com');
                                // $mail->addAddress('jhovenellm@gmail.com');
                                $mail->isHTML(true);
                                $mail->Subject = 'Withdrawal Request';
                                $rem_slp = $this->account->manager->ManagerAccountCurrentSLPBalance - $s;
                                $rem_axs = $this->account->manager->ManagerAccountCurrentAXSBalance - $a;
                                $withdraw_data = [
                                    'slp_amt' => $s,
                                    'axs_amt' => $a,
                                    'method' => strtolower($m),
                                    'rem_slp_bal' => $rem_slp,
                                    'rem_axs_bal' => $rem_axs,
                                    'manager' => $this->account->manager->getManagerAccountID()
                                ];
                                $id = $this->model->addToWithdrawHistory($withdraw_data);
                                if ($id) {
                                    $message['request_id'] = $id;
                                    $body = $this->getRender('withdraw_request_email_template.html.twig', $message, '');
                                    $mail->Body = $body;
                                    $mail->send();
                                    Session::set('_w_status', true);
                                    unset($_SESSION['_w_s']);
                                    unset($_SESSION['_w_a']);
                                    unset($_SESSION['_w_n']);;
                                }
                            } catch (\PHPMailer\PHPMailer\Exception $e) {
                                Session::set('_w_status', false);
                            }
                            Response::redirect('/manager/withdraw');
                        }
                    }
                }
            }
        }
        Response::errorPage(404);
    }

    private function allFieldsExist(array $fields, array $keys): bool
    {
        foreach ($fields as $key => $field) {
            if (!has_inclusion_of($key, $keys) || $field === '') {
                return false;
            }
        }
        return true;
    }

    private function getIntVal($num): int
    {
        return intval(preg_replace('/\D/', '', $num));
    }
}