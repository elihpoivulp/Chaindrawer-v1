<?php

namespace CD\App\Controllers;

use CD\Config\Config;
use CD\Core\Controller;
use CD\Core\CSRFToken;
use CD\Core\Request;
use CD\Core\Response;
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
        Response::redirect('auth/login');
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
        if ($this->request->isPost()) {
            $has_error = false;
            $p = $this->request->getBody();
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
                    $body = <<<EOF
<h1>Thank you for your interest in becoming one of our axie infinity co-managers.</h1>
To know more about the details you may click this link: <br>
https://drive.google.com/file/d/1U4KlbmdjuZRD2SqXuTQjpx1dc1GD66b9/view?usp=sharing
<br>
<br>
<h2>Procedure</h2>
<h3>Step 1:</h3>
Go to this link:
<br>
https://docs.google.com/spreadsheets/d/1-4ORedm015beevOK9mEOb-6xqGd6hVku7iZfKgBZh94/edit#gid=0
<br>
for current team details to be filled.
<h3>Step 2:</h3>
Reply to this email with the following information:
<ol>
<li>Name (First Name, Middle Name, Last Name)</li>
<li>Address (Complete Address)</li>
<li>Contact #</li>
<li>Payment</li>
</ol>
<p><strong>Payment methods:</strong></p>
&nbsp;&nbsp;GCASH/PAYMAYA/COINSPH:<br>
&nbsp;&nbsp;&nbsp;09970789953
<br>
<br>
&nbsp;&nbsp;Bank Transfer<br>
&nbsp;&nbsp;&nbsp;&nbsp;Acct Name: Jhovenell Manait<br>
&nbsp;&nbsp;&nbsp;&nbsp;BDO: 011280065743<br>
&nbsp;&nbsp;&nbsp;&nbsp;CIMB: 20860745941693<br>
&nbsp;&nbsp;&nbsp;&nbsp;UNIONBANK: 109485670903
<h3>Step 3:</h3>
We will send you an acknowledgement receipt together with your login details to our system:<br>
https://chaindrawer.com
<br><br>
<em>
<strong>Note</strong>: Your shared axie team will only become "active" once the full amount of the team was filled.
</em>
<br>
<br>
Thanks!
<br>
<br>
<hr>
<br>
<em>Chaindrawer is <strong>NOT</strong> affiliated with Axie Infinity.</em>
<br><br>
<table style="width: 100%; border-collapse: collapse;" border="0">
<tbody>
<tr>
<td style="width: 2.77973%;">
    <img src="https://chaindrawer.com/assets/chndrwr/images/cd-logo-only.png" alt="Chaindrawer logo" width="27" height="27" />
</td>
<td style="width: 97.2203%;">
<span style="color: #333333; font-size: 9pt;"><span style="font-family: arial, helvetica, sans-serif;"> &copy; 2021</span>, Chaindrawer</span>
</td>
</tr>
</tbody>
</table>
EOF;

                    $mail->setFrom('business@chaindrawer.com', 'Chaindrawer');
                    $mail->addReplyTo('business@chaindrawer.com');
                    $mail->addAddress($cleaned);
                    $mail->isHTML(true);
                    $mail->Subject = 'Chaindrawer Axie Infinity Co-Manager Membership';
                    $mail->Body = $body;
                    if ($mail->send()) {
                        $response['success'] = true;
                        $response['message'] = htmlspecialchars($cleaned);
                    } else {
                        $response['message'] = 'Please check your internet connection.';
                    }
                } catch (Exception $e) {
                }
            }
        }
        echo json_encode($response);
        exit;
    }
}