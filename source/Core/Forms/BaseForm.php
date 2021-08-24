<?php


namespace CD\Core\Forms;


use CD\Core\CSRFToken;

abstract class BaseForm
{
    public function begin(string $method, string $action, $form_class_list = [], string $id = '', bool $enctype = false): void
    {
        $enctype = $enctype ? 'multipart/form-data' : '';
        $csrf = '';
        if (strtolower($method) === 'post') {
            $csrf = '<input type="hidden" name="csrf_token" value="' . CSRFToken::generateToken() . '">';
        }
        printf('<form method="%s" id="%s" action="%s" enctype="%s" class="%s">%s',
            $method,
            $id,
            $action,
            $enctype,
            is_array($form_class_list) ? join(' ', $form_class_list) : $form_class_list,
            $csrf
        );
    }

    public function setInitial(string $attribute, $value)
    {
        $this->$attribute = $value;
    }

    public function end(): void
    {
        echo '</form>';
    }

    abstract public function field(string $attribute): Field;
}