<?php


namespace CD\Core\Forms;


class Field
{
    public const INPUT_TYPE_TEXT = 'text';
    public const INPUT_TYPE_PASSWORD = 'password';
    public const INPUT_TYPE_AMOUNT = 'amount';
    public const INPUT_TYPE_SELECT = 'select';
    public const INPUT_TYPE_SELECT_BASIC = 'basic';
    public const INPUT_REQUIRED = 'required';
    public const DEFAULT_MONEY_MASK = '#,##0.00';

    public string $type;
    public string $select_type;
    public array $select_options = [];
    public string $amount_mask;
    public int $amount_min_length;
    public int $amount_max_length;
    public string $required_type = '';
    public string $error_message;
    public string $auto_focus = '';
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
        $html = '';
        switch ($this->type) {
            case '':
            case 'text':
            case 'password':
                $html = $this->input_html($id, $this->name, $this->value, $label, $this->type, $classes, $placeholder, $this->required_type, $this->error_message, $this->auto_focus);
                break;
            case 'select':
                $html =  $this->select_html($id, $this->name, $label, $classes, $this->required_type, $this->error_message);
                break;
            case 'amount':
                $html = $this->amount_html($id, $this->name, $this->value, $label, $classes, $placeholder, $this->required_type, $this->error_message, $this->auto_focus);
        }
        return $html;
        // PHP 8.0:
        // return match ($this->type) {
        //     '', 'text', 'password' => $this->input_html($id, $this->name, $this->value, $label, $this->type, $classes, $placeholder, $this->required_type, $this->error_message, $this->auto_focus),
        //     'select' => $this->select_html($id, $this->name, $label, $classes, $this->required_type, $this->error_message),
        //     'amount' => $this->amount_html($id, $this->name, $this->value, $label, $classes, $placeholder, $this->required_type, $this->error_message, $this->auto_focus)
        // };
    }

    public function password(): self
    {
        $this->type = self::INPUT_TYPE_PASSWORD;
        return $this;
    }

    public function amount(int $max_length = 22, int $min_length = 8, string $mask = self::DEFAULT_MONEY_MASK): self
    {
        $this->amount_mask = $mask;
        $this->amount_min_length = $min_length;
        $this->amount_max_length = $max_length;
        $this->type = self::INPUT_TYPE_AMOUNT;
        return $this;
    }

    public function select(array $options, string $select_type = self::INPUT_TYPE_SELECT_BASIC): self
    {
        $this->type = self::INPUT_TYPE_SELECT;
        $this->select_type = $select_type;
        $this->select_options = $options;
        return $this;
    }

    public function required(): self
    {
        $this->required_type = self::INPUT_REQUIRED;
        return $this;
    }

    public function autofocus(): self
    {
        $this->auto_focus = 'autofocus';
        return $this;
    }

    private function input_html($id, $name, $value, $label, $type, $classes, $placeholder, $required, $error_msg, $autofocus): string
    {
        return "<div class='form-group'>
                    <label class='form-label' for='$id'>$label:</label>
                    <input id='$id' type='$type' class='form-control $classes' placeholder='$placeholder' name='$name' value='$value' $required $autofocus>
                    <div class='invalid-feedback'>
                        $error_msg
                    </div>
                </div>";
    }

    private function select_html($id, $name, $label, $classes, $required, $error_msg): string
    {
        $html = "<div class='form-group'>
                   <label class='form-label' for='$id'>$label:</label>
                   <select id='$id' name='$name' data-toggle='select' class='form-control $classes' $required>";
        foreach ($this->select_options as $id => $v) {
            $selected = '';
            if ($error_msg && $this->value == $v) {
                $selected = 'selected';
            }
            $html .= "<option value='$id' $selected>$v</option>";
        }
        $html .= "</select>
                   <div class='invalid-feedback'>
                       $error_msg
                   </div>
               </div>";
        return $html;
    }

    private function amount_html($id, $name, $value, $label, $classes, $placeholder, $required, $error_msg, $autofocus): string
    {
        return "<div class='form-group'>
                    <label class='form-label' for='$id'>$label:</label>
                    <input id='$id' name='$name' type='text' class='form-control $classes' placeholder='$placeholder' name='$name' value='$value' $required data-mask='$this->amount_mask' minlength='$this->amount_min_length' maxlength='$this->amount_max_length' data-mask-reverse='true' $autofocus>
                    <div class='invalid-feedback'>
                        $error_msg
                    </div>
                </div>";
    }
}