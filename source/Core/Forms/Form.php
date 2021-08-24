<?php


namespace CD\Core\Forms;

class Form extends FormValidations
{
    protected array $rules = [];

    public function field(string $attribute, string $id = '', string $label = '', $placeholder = '', array $classes = []): Field
    {
        if (!property_exists($this, $attribute)) {
            $this->$attribute = '';
        }
        $opts = [
            'value' => $this->$attribute,
            'name' => $attribute,
            'id' => $id,
            'label' => $label,
            'placeholder' => $placeholder,
            'extra_input_class' => $this->hasError($attribute) ? 'is-invalid' : '',
            'error_message' => $this->getFirstError($attribute)
        ];
        return Field::input($opts);
    }

    public function setFormClassList($class_list): void
    {
        $this->form_class_list = $class_list;
    }

    public function rules(): array
    {
        return $this->rules;
    }

    public function setRules(array $rules)
    {
        $this->rules = $rules;
    }
}