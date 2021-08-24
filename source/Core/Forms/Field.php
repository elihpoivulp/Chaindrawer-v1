<?php


namespace CD\Core\Forms;


class Field
{
    public const INPUT_TYPE_TEXT = 'text';
    public const INPUT_TYPE_PASSWORD = 'password';
    public const INPUT_TYPE_EMAIL = 'email';
    public const INPUT_TYPE_AMOUNT = 'amount';
    public const INPUT_TYPE_PHONE = 'phone';
    public const INPUT_TYPE_SELECT = 'select';
    public const INPUT_TYPE_SELECT_BASIC = 'basic';
    public const INPUT_REQUIRED = 'required';
    public const DEFAULT_MONEY_MASK = '#,##0.00';
    public const DEFAULT_PHONE_MASK = '00000000000';

    public string $type;
    public string $select_type;
    public array $select_options = [];
    public string $mask;
    public int $mask_min_length;
    public int $mask_max_length;
    public string $required_type = '';
    public bool $no_label = false;
    public string $error_message;
    public string $auto_focus = '';
    public string $flush_class = '';
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
        $placeholder = !empty($this->placeholder) ? $this->placeholder : (!empty($this->label) ? $this->label : $this->name);
        $label = ucfirst($label);
        $classes = is_array($this->extra_input_class) ? join(', ', $this->extra_input_class) : $this->extra_input_class;
        $classes .= " $this->flush_class";
        $html = '';
        switch ($this->type) {
            case '':
            case 'text':
            case 'password':
            case 'email':
                $html = $this->input_html($id, $this->name, $this->value, $this->type, $classes, $placeholder, $this->required_type, $this->error_message, $this->auto_focus);
                break;
            case 'select':
                $html =  $this->select_html($id, $this->name, $classes, $this->required_type, $this->error_message);
                break;
            case 'amount':
            case 'phone':
                $html = $this->masked_input($id, $this->name, $this->value, $classes, $placeholder, $this->required_type, $this->error_message, $this->auto_focus);
                break;
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
        $this->mask = $mask;
        $this->mask_min_length = $min_length;
        $this->mask_max_length = $max_length;
        $this->type = self::INPUT_TYPE_AMOUNT;
        return $this;
    }

    public function phone(int $max_length = 11, int $min_length = 11, string $mask = self::DEFAULT_PHONE_MASK): self
    {
        $this->mask = $mask;
        $this->mask_min_length = $min_length;
        $this->mask_max_length = $max_length;
        $this->type = self::INPUT_TYPE_PHONE;
        return $this;
    }

    public function email(): self
    {
        $this->type = self::INPUT_TYPE_EMAIL;
        return $this;
    }

    public function noLabel(): self
    {
        $this->no_label = true;
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

    public function flush(): self
    {
        $this->flush_class = 'form-control-flush';
        return $this;
    }

    public function getLabel(): string
    {
        $id = !empty($this->id) ? $this->id : $this->name;
        $label = !empty($this->label) ? $this->label : $this->name;
        return "<label class='form-label' for='$id'>$label:</label>";
    }

    protected function input_html($id, $name, $value, $type, $classes, $placeholder, $required, $error_msg, $autofocus): string
    {
        $lbl = '';
        if (!$this->no_label) {
            $lbl = $this->getLabel();
        }
        return $lbl . "
                    <input id='$id' type='$type' class='form-control $classes' placeholder='$placeholder' name='$name' value='$value' $required $autofocus>
                    <div class='invalid-feedback'>
                        $error_msg
                    </div>
                ";
    }

    protected function select_html($id, $name, $classes, $required, $error_msg): string
    {
        $lbl = '';
        if (!$this->no_label) {
            $lbl = $this->getLabel();
        }
        $html = $lbl . "
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
                   </div>";
        return $html;
    }

    protected function masked_input($id, $name, $value, $classes, $placeholder, $required, $error_msg, $autofocus): string
    {
        $lbl = '';
        if (!$this->no_label) {
            $lbl = $this->getLabel();
        }
        return  $lbl . "
                    <input id='$id' name='$name' type='text' class='form-control $classes' placeholder='$placeholder' name='$name' value='$value' $required data-mask='$this->mask' minlength='$this->mask_min_length' maxlength='$this->mask_max_length' data-mask-reverse='true' $autofocus>
                    <div class='invalid-feedback'>
                        $error_msg
                    </div>
                ";
    }
}