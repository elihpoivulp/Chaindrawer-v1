<?php

namespace CD\App\Controllers;

use CD\Config\Config;
use CD\Core\Controller;
use CD\Core\CSRFToken;
use CD\Core\Request;
use CD\Core\View;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Home extends Controller
{
    static protected string $template_namespace = 'home';

    public function __construct(array $params, View $view, Request $request)
    {
        parent::__construct($params, $view, $request);
        $this->registerPath(VIEWS_PATH . '/' . static::$template_namespace, static::$template_namespace);
    }

    public function indexAction()
    {
//        $this->render(
//            'coming_soon.html.twig',
//            [
//                'title' => 'Coming Soon!'
//            ]
//        );
    }

    public function become()
    {
        $response = [
            'success' => false,
            'message' => 'Your request cannot be processed right now. Please try again later.'
        ];
        if($this->request->isAJAX() && $this->request->requestIsSameDomain())
        {
            $has_error = false;
            $p = $this->request->getBody();
            if (!has_key_presence('_token', $p) || CSRFToken::verifyCSRFToken($p['_token'])) {
                $has_error = true;
            }
            if (!has_key_presence('email', $p) || !has_valid_email_format($p['email'])) {
                $response['message'] = 'Invalid email.';
                $has_error = true;
            }
            if (!$has_error) {
                $mail = new PHPMailer(true);
                try {
                    $cleaned = filter_var($p['email'], FILTER_SANITIZE_EMAIL);
                    $mail->isSMTP();
                    $mail->Host = Config::EMAIL_SMTP_HOST();
                    $mail->SMTPAuth = true;
                    $mail->Username = Config::EMAIL_NO_REPLY()[0];
                    $mail->Password = Config::EMAIL_NO_REPLY()[1];
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port = 465;
                    $mail->setFrom(Config::EMAIL_NO_REPLY()[0], 'Chaindrawer');
                    $mail->addAddress('social.npg@gmail.com');
                    $mail->addAddress('jhovenellm@gmail.com');
                    $mail->isHTML(true);
                    $mail->Subject = 'Membership Request';
                    $mail->Body = "<h1>Pasali!</h1><br><h2>$cleaned</h2>";
                    if ($mail->send()) {
                        $response['success'] = true;
                        $response['message'] = htmlspecialchars($cleaned);
                    } else {
                        $response['message'] = 'Please check your internet connection.';
                    }
                } catch (Exception $e) {}
            }
        }
        echo json_encode($response);

    }
}