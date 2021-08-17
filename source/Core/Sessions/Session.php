<?php


namespace CD\Core\Sessions;

class Session
{
    protected const FLASH_KEY = 'flash_messages';

    public const FLASH_TYPE_WARNING = 'warning';
    public const FLASH_TYPE_SUCCESS = 'success';

    public const FLASH_TYPE_ICONS = [
        self::FLASH_TYPE_WARNING => 'warning',
        self::FLASH_TYPE_SUCCESS => 'check_circle',
        'default' => 'access_time'
    ];

    static public function start(): bool
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        return true;
    }

    static public function setFlash(string $key, string $message, array $options = []): void
    {
        self::initFlash();
        if (!has_key_presence('type', $options) || !has_key_presence($options['type'], self::FLASH_TYPE_ICONS)) {
            $options['type'] = self::FLASH_TYPE_SUCCESS;
        }
        if (!has_key_presence('dismissable', $options)) {
            $options['dismissable'] = false;
        }
        $_SESSION[self::FLASH_KEY][$key] = [
            'message' => $message,
            'options' => $options
        ];
    }

    static public function getFlashToast(string $key): ?string
    {
        self::initFlash();
        if (has_key_presence($key, $_SESSION[self::FLASH_KEY]) && !is_blank($_SESSION[self::FLASH_KEY][$key]['message'])) {

            $flash = $_SESSION[self::FLASH_KEY][$key];
            $title = $flash['options']['title'];
            $dismissable = $flash['options']['dismissable'] ? 'true' : 'false';
            $type = $flash['options']['type'];
            $msg = $flash['message'];
            $timeout = has_key_presence('timeout', $flash['options']) ? (string) $flash['options']['timeout'] : '';
            $html = 'msg("%s", "%s", %s, "%s", "%s")';
            unset($_SESSION[self::FLASH_KEY][$key]);
            return sprintf($html, $title, $msg, $dismissable, $type, $timeout);
        }
        return null;
    }

    static public function getFlashAlert(string $key)
    {
        self::initFlash();
        if (has_key_presence($key, $_SESSION[self::FLASH_KEY]) && !is_blank($_SESSION[self::FLASH_KEY][$key]['message'])) {
            $title = '';
            $dismiss_button = '';
            $dismiss_class = '';
            $flash = $_SESSION[self::FLASH_KEY][$key];
            $type = $flash['options']['type'];
            $msg = $flash['message'];
            $has_title = has_key_presence('title', $flash['options']);
            $icon = $flash['options']['icon'] ?? self::FLASH_TYPE_ICONS[$type] ?? self::FLASH_TYPE_ICONS['default'];
            if ($has_title) {
                $title = "<strong>{$flash['options']['title']}</strong><br>";
            }
            if ($flash['options']['dismissable']) {
                $dismiss_class = 'alert-dismissable';
                $dismiss_button = '<button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>';
            }
            $html = '<div class="alert %s alert-soft-%s mb-lg-32pt fade show">
                            %s
                            <div class="d-flex flex-wrap align-items-start">
                                <div class="mr-8pt">
                                    <i class="material-icons">%s</i>
                                </div>
                                <div class="flex">
                                    <small class="text-100">
                                        %s
                                        <span>%s</span>
                                    </small>
                                </div>
                            </div>
                        </div>';
            unset($_SESSION[self::FLASH_KEY][$key]);
            return sprintf($html, $dismiss_class, $type, $dismiss_button, $icon, $title, $msg);
        }
        return null;
    }

    static public function get(string $key)
    {
        if (self::exists($key)) {
            return $_SESSION[$key];
        }
        return null;
    }

    static public function set(string $key, string $value): bool
    {
        $_SESSION[$key] = $value;
        return true;
    }

    static public function exists(string $key): bool
    {
        return has_key_presence($key, $_SESSION);
    }

    static private function initFlash(): void {
        if (!isset($_SESSION[self::FLASH_KEY])) {
            $_SESSION[self::FLASH_KEY] = [];
        }
    }
}