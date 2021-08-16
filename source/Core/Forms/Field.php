<?php


namespace CD\Core\Forms;


class Field
{
    public const INPUT_TYPE_TEXT = 'text';
    public const INPUT_TYPE_PASSWORD = 'password';
    public const INPUT_TYPE_NUMBER = 'number';

    public const INPUT_REQUIRED = 'required';

    public string $type;
    public string $required_type = '';

    public function __construct(public string $name, public string $id = '', public string $label = '', public string $placeholder = '', public string $value = '', public string|array $extra_input_class = [], public string $error_message = '', string $type = '')
    {
        $this->type = $type ?? self::INPUT_TYPE_TEXT;
    }

    static public function input(array $options): self
    {
        return new Field(...$options);
    }

    public function __toString()
    {
        $id = !empty($this->id) ? $this->id : $this->name;
        $label = !empty($this->label) ? $this->label : $this->name;
        $placeholder = !empty($this->placeholder) ? $this->placeholder : $label;
        $label = ucfirst($label);
        $classes = is_array($this->extra_input_class) ? join(', ', $this->extra_input_class) : $this->extra_input_class;
        return "<div class='form-group'>
                    <label class='form-label' for='$id'>$label:</label>
                    <input id='$id' type='$this->type' class='form-control $classes' placeholder='$placeholder' name='$this->name' value='$this->value' $this->required_type>
                    <div class='invalid-feedback'>
                        $this->error_message
                    </div>
                </div>";
    }

    public function password(): self
    {
        $this->type = self::INPUT_TYPE_PASSWORD;
        return $this;
    }

    public function required(): self
    {
        $this->required_type = self::INPUT_REQUIRED;
        return $this;
    }
}