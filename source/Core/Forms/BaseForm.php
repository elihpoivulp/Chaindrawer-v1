<?php


namespace CD\Core\Forms;


abstract class BaseForm
{
    public function begin(string $method, string $action, $form_class_list = [], bool $enctype = false): void
    {
        $enctype = $enctype ? 'multipart/form-data' : '';
        printf('<form method="%s" action="%s" enctype="%s" class="%s">',
            $method,
            $action,
            $enctype,
            is_array($form_class_list) ? join(' ', $form_class_list) : $form_class_list
        );
    }

    public function end(): void
    {
        echo '</form>';
    }

    abstract public function field(string $attribute): Field;
}