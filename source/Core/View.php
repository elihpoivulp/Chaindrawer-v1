<?php

namespace CD\Core;

use CD\Config\Config;
use CD\Core\Sessions\Session;
use CD\Core\Sessions\SessionsUserAuth;
use CD\Models\ActiveAccount;
use Twig\Environment;
use Twig\TwigFunction;
use Twig\Error\SyntaxError;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Loader\FilesystemLoader;

class View
{
    static private ?Environment $twig = null;
    static private ?FilesystemLoader $loader = null;

    public function __construct()
    {
        if (is_null(self::$twig)) {
            self::$loader = new FilesystemLoader(VIEWS_PATH);
            $twig = new Environment(self::$loader, ['strict_variables' => true]);

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
            $twig->addFunction(new TwigFunction('round', function ($num, int $precision = 2): float {
                return round($num, $precision);
            }));
            $twig->addFunction(new TwigFunction('get_uri', function ($pos = null) {
                return get_uri($pos);
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
            get_uri();
            $twig->addGlobal('app', Config::WEBSITE_NAME);
            $twig->addGlobal('user', $user);
            $twig->addGlobal('user_type', $role);
            $twig->addGlobal('peso_symbol', '₱');

            self::$twig = $twig;
        }
    }

    private function siteURL(string $append_uri = ''): string
    {
        return Request::getSiteURL() . $append_uri;
    }

    static public function addPath(string $directory, string $namespace = ''): void
    {
        try {
            self::$loader->addPath($directory, $namespace);
        } catch (LoaderError $e) {
            // TODO: Throw Exception
            exit($e->getMessage());
        }
    }

    static public function renderTemplate(string $template, array $context): void
    {
        try {
            echo self::$twig->render($template, $context);
        } catch (LoaderError | RuntimeError | SyntaxError $e) {
            // TODO: Throw Exception
            exit($e->getMessage());
        }
    }
}