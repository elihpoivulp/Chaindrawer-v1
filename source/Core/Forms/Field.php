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
    public string $error_message;
    /**
     * @var array|mixed
     */
    public $extra_input_class;
    public string $value;
    public string $id;
    public string $label;
    public string $placeholder;
    public string $name;

    public function __construct(string $name, string $id = '', string $label = '', string $placeholder = '', string $value = '', $extra_input_class = [], string $error_message = '', string $type = '')
    {
        $this->name = $name;
        $this->id = $id;
        $this->label = $label;
        $this->placeholder = $placeholder;
        $this->value = $value;
        $this->extra_input_class = $extra_input_class;
        $this->error_message = $error_message;
        $this->type = $type ?? self::INPUT_TYPE_TEXT;
    }

    static public function input(array $options): self
    {
        $name = $options['name'];
        $id = $options['id'] ?? '';
        $label = $options['label'] ?? '';
        $placeholder = $options['placeholder'] ?? '';
        $value = $options['value'] ?? '';
        $extra_input_class = $options['extra_input_class'] ?? [];
        $error_message = $options['error_message'] ?? '';
        $type = $options['type'] ?? '';
        return new Field($name, $id, $label, $placeholder, $value, $extra_input_class, $error_message, $type);
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