<?php


namespace CD\Core\Forms;


class Form extends BaseForm
{
    public function field(string $attribute): Field
    {
        $field = new Form();
    }

    public function setFormClassList(string|array $class_list): void
    {
        $this->form_class_list = $class_list;
    }
}