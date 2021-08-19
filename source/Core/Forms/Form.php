<?php


namespace CD\Core\Forms;


class Form extends BaseForm
{
    public function field(string $attribute): Field
    {
        $opts = [
            'value' => $this->$attribute,
            'name' => $attribute,
            'extra_input_class' => $this->hasError($attribute) ? 'is-invalid' : '',
            'error_message' => $this->getFirstError($attribute)
        ];
        return Field::input($opts);
    }

    public function setFormClassList($class_list): void
    {
        $this->form_class_list = $class_list;
    }
}