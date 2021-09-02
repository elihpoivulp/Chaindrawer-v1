<?php


namespace CD\Core\Forms;

use CD\Core\DB\BaseDBModel;
use Exception;
use ReflectionProperty;

abstract class ModelForm extends FormValidations
{
    protected BaseDBModel $model;

    public function __construct(BaseDBModel $model)
    {
        $this->model = $model;
    }

    /**
     * @throws Exception
     */
    public function field(string $attribute, string $id = '', string $label = '', $placeholder = ''): Field
    {
        if (!property_exists($this->model, $attribute)) {
            throw new Exception("Attribute \"$attribute\" does not exist on Model" . get_class($this->model));
        }
        $r_model = new ReflectionProperty($this->model, $attribute);
        if ($r_model->isPrivate() || $r_model->isProtected()) {
            $method = "get$attribute";
            $value = $this->model->$method();
        } else {
            $value = $this->model->$attribute;
        }
        $opts = [
            'value' =>  $value,
            'name' => $attribute,
            'id' => $id,
            'label' => $label,
            'placeholder' => $placeholder,
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