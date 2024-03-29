<?php

namespace CD\Core;

use CD\Config\Config;
use CD\Core\Sessions\Session;
use CD\Core\Sessions\SessionsUserAuth;
use CD\Models\ActiveAccount;
use CD\Models\SLP;
use Exception;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class View
{
    static private ?Environment $twig = null;
    static private ?FilesystemLoader $loader = null;

    public function __construct()
    {
        if (is_null(self::$twig)) {
            self::$loader = new FilesystemLoader(VIEWS_PATH);
            $twig = new Environment(self::$loader, [
                'debug' => true,
                'strict_variables' => true
            ]);
            $twig->addExtension(new DebugExtension());

            $_url_func = function (string $uri = ''): string {
                return $this->siteURL(trim_slashes($uri));
            };
            $twig->addFunction(new TwigFunction('load_static', $_url_func));
            $twig->addFunction(new TwigFunction('site_url', $_url_func));
            $twig->addFunction(new TwigFunction('getFlashAlert', function (string $key = ''): ?string {
                return Session::getFlashAlert($key);
            }));
            $twig->addFunction(new TwigFunction('getFlashToast', function (string $key = ''): ?string {
                return Session::getFlashToast($key);
            }));
            $twig->addFunction(new TwigFunction('toShortFormat', function ($num): string {
                return toShortFormat($num);
            }));
            $twig->addFunction(new TwigFunction('toMoneyFormat', function ($num, bool $with_decimal = true): string {
                return number_format($num, $with_decimal ? 2 : 0);
            }));
            // $twig->addFunction(new TwigFunction('round', function ($num, int $precision = 2): float {
            //     return round($num, $precision);
            // }));
            $twig->addFunction(new TwigFunction('get_uri', function ($pos = null) {
                return get_uri($pos);
            }));
            $twig->addFunction(new TwigFunction('replace', function ($source, $pattern, $replacement = '') {
                return preg_replace($pattern, $replacement, $source);
            }));
            $twig->addFunction(new TwigFunction('time_ago', function ($time) {
                return get_time_ago($time);
            }));
            $role = 'Guest';
            $user = null;
            if (SessionsUserAuth::isLoggedIn()) {
                $active = new ActiveAccount();
                $user = $active->getRelatedUser(SessionsUserAuth::getToken());
                if (!$user) {
                    SessionsUserAuth::logout();
                } else {
                    $role = array_column($user->getRoles(), 'RoleName');
                }
            }
            $last_known_rate = 0;
//            if ($user) {
//                $slp = new SLP();
//                try {
//                    $last_known_rate = $slp->getData()['last_known_rate'];
//                } catch (Exception $e) {
//                    $last_known_rate = 0;
//                }
//            }
            $twig->addGlobal('app', Config::WEBSITE_NAME);
            $twig->addGlobal('user', $user);
            $twig->addGlobal('user_type', $role);
            $twig->addGlobal('peso_symbol', '₱');
            $twig->addGlobal('can_withdraw', can_withdraw());
            $twig->addGlobal('slp_rate', $last_known_rate);
            $twig->addGlobal('colors', [
                'avatar' => [
                    'bg-primary',
                    'bg-secondary',
                    'bg-success',
                    'bg-info',
                    'bg-warning',
                    'bg-danger',
                    'bg-light',
                    'bg-dark',
                    'bg-black',
                    'bg-accent',
                    'bg-accent-red',
                    'bg-accent-yellow',
                    'bg-accent-dodger-blue',
                    'bg-accent-pickled-bluewood',
                    'bg-accent-electric-violet',
                    'bg-primary-purple',
                    'bg-primary-red',
                    'bg-primary-yellow',
                    'bg-primary-light',
                    'bg-primary-dodger-blue',
                    'bg-primary-pickled-bluewood',
                    'bg-transparent',
                    'bg-white',
                    'bg-alt',
                    'bg-body',
                    'bg-darker',
                    'bg-gradient-purple',
                    'bg-gradient-primary',
                    'bg-dark-blue',
                    'bg-dark-purple',
                    'bg-purple-gradient',
                    'bg-black-100',
                    'bg-black-50',
                    'bg-black-20',
                    'bg-white-25',
                    'bg-white-35',
                    'bg-white-45',
                    'bg-white-90',
                    'bg-white-95',
                ]

            ]);

            self::$twig = $twig;
        }
    }

    private function siteURL(string $append_uri = ''): string
    {
        return Request::getSiteURL() . $append_uri;
    }

    /**
     * @throws Exception
     */
    static public function addPath(string $directory, string $namespace = ''): void
    {
        try {
            self::$loader->addPath($directory, $namespace);
        } catch (LoaderError $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    static public function renderTemplate(string $template, array $context = []): void
    {
        echo self::_getTemplate($template, $context);
    }

    static public function getTemplate(string $template, array $context = [])
    {
        return self::_getTemplate($template, $context);
    }

    static private function _getTemplate(string $template, array $context)
    {
        try {
            if (is_null(self::$twig)) {
                include(VIEWS_PATH . '/dberror_error_page.php');
            } else {
                return self::$twig->render($template, $context);
            }
        } catch (LoaderError | RuntimeError | SyntaxError $e) {
            throw new Exception($e->getMessage());
        }
    }
}