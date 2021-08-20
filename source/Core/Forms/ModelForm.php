<?php


namespace CD\Core\Forms;

use CD\Core\Models\DBFormModel;

abstract class ModelForm extends FormValidations
{
    protected $model;

    public function __construct(DBFormModel $model)
    {
        $this->model = $model;
    }

    public function field(string $attribute, string $id = '', string $label = ''): Field
    {
        if (!property_exists($this->model, $attribute)) {
            // TODO: Throw Exception
            exit("Attribute \"$attribute\" does not exist on Model" . get_class($this->model));
        }
        $opts = [
            'value' => $this->model->$attribute,
            'name' => $attribute,
            'id' => $id,
            'label' => $label,
            'extra_input_class' => $this->hasError($attribute) ? 'is-invalid' : '',
            'error_message' => $this->getFirstError($attribute)
        ];
        return Field::input($opts);
    }

    public function validate()
    {
        $result = parent::validate();
        $prop_values = array_map(fn($attr) => $this->$attr, $this->fields);
        $this->model->setPropertyValues(array_combine($this->fields, $prop_values));
        if (!$result) {
            return false;
        }
        return $result;
    }
}