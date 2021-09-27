<?php


namespace CD\App\Controllers\Admin;

use CD\App\Controllers\Error;
use CD\Core\Request;
use CD\Core\Response;
use CD\Core\Sessions\Session;
use CD\Core\View;
use CD\Core\ViewControllers\AdminViewOnly;

class Withdrawals extends AdminViewOnly
{
    static protected string $template_namespace = 'admin';

    public function __construct(array $params, View $view, Request $request)
    {
        parent::__construct($params, $view, $request);
        $this->registerPath(VIEWS_PATH . "/"  . static::$template_namespace, static::$template_namespace);
        $this->loadModel('Withdrawals');
    }

    public function indexAction()
    {
//        $manager = new Manager();
//        $this->render('index.html.twig', [
//            'title' => 'Admin',
//            'withdrawals' => $manager->getAllWithdrawals(5)
//        ]);
    }

    public function getWithdrawalDetailsAction()
    {
        if (has_key_presence('w_id', $_GET) && (has_key_presence('b', $_GET) && has_inclusion_of($_GET['b'], ['confirm', 'cancel'])))
        {
            $id = $_GET['w_id'];
            $action = $_GET['b'];
            $response = [
                'success' => false,
                'message' => '',
            ];
            if (!preg_match('/\d+/', $id)) {
                $response = [
                    'success' => false,
                    'message' => 'Invalid ID',
                ];
            } else {
                switch ($action) {
                    case 'confirm':
                        $record = $this->model->getWithdrawalDetails($id);
                        if (!$record) {
                            $response = [
                                'success' => false,
                                'message' => 'There\'s no such data exists.'
                            ];
                        } else {
                            $response = [
                                'success' => true,
                                'body' => $record,
                            ];
                        }
                        break;
                    case 'cancel':
                        $response = [
                            'success' => true,
                            'body' => [
                                'WithdrawalRequestID' => $id
                            ]
                        ];
                }
            }
            $response['type'] = $action;
            $this->render('fragments/withdraw-modal.html.twig', [
                'response' => $response
            ]);
        } else {
            Response::errorPage(404);
        }
    }

    public function processAction()
    {
        $b = $this->request->getBody();
        if ($this->request->isPost() && has_key_presence('w_id', $b) && preg_match('/\d+/', $b['w_id']) && has_inclusion_of($b['action'], ['complete', 'cancel'])) {
            $response = [
                'message' => '',
                'title' => '',
                'type' => 'warning'
            ];
            switch ($b['action']) {
                case 'complete':
                    $data = [
                        'WithdrawalDateProcessed' => date('Y-m-d H:i:s'),
                        'WithdrawalSLPAmount' => $b['wsa'],
                        'WithdrawalAXSAmount' => $b['waa']
                    ];
                    if (has_key_presence('slp_rate', $b)) {
                        $data['WithdrawalSLPinPHP'] = $b['amt'];
                        $data['WithdrawalSLPRate'] = $b['slp_rate'];
                    }
                    $complete = $this->model->completeWithdrawal($b['w_id'], $b['mid'], $data);
                    if ($complete) {
                        $response = [
                            'message' => '#' . $b['w_id'] . ' has been completed.',
                            'title' => 'Withdraw Process Complete',
                            'type' => 'success'
                        ];
                    } else {
                        $response = [
                            'message' => 'Cannot process withdrawal right now. Please try again later.',
                            'title' => 'Withdraw Process Failed',
                        ];
                    }
                    break;
                case 'cancel':
                    $cancelled = $this->model->cancelWithdrawal($b['w_id']);
                    if ($cancelled) {
                        $response = [
                            'message' => '#' . $b['w_id'] . ' has been successfully cancelled.',
                            'title' => 'Withdraw Cancellation Complete',
                            'type' => 'success'
                        ];
                    } else {
                        $response = [
                            'message' => 'Cannot cancel withdrawal right now. Please try again later.',
                            'title' => 'Withdraw Cancellation Failed',
                        ];
                    }
                    break;
            }
            switch ($_GET['ref']) {
                default:
                    $ref = 'admin';
                    break;
            }
            Session::setFlash('msg', $response['message'], [
                'type' => $response['type'] === 'success' ? Session::FLASH_TYPE_SUCCESS : Session::FLASH_TYPE_WARNING,
                'title' => $response['title'],
                'dismissable' => true
            ]);
            Response::redirect($ref);
        } else {
            Response::errorPage(404);
        }
    }
}