<?php


namespace CD\Core\Forms;


abstract class FormValidations extends Form
{
    public const RULE_REQUIRED = 'required';
    public const RULE_EMAIL = 'email';
    public const RULE_MIN = 'min';
    public const RULE_MAX = 'max';
    public const RULE_MATCH = 'match';

    public array $errors = [];

    protected CONST ERROR_MESSAGES = [
        self::RULE_REQUIRED => 'This field is required',
        self::RULE_EMAIL => 'This field must be a valid email address',
        self::RULE_MIN => 'Min length of this field must be {min}',
        self::RULE_MAX => 'Max length of this field must be {max}',
        self::RULE_MATCH => 'This field must be the same as {match}'
    ];

    protected array $fields = [];

    // static protected ?string $table_name = null;

    // final public function __construct()
    // {
    //     $this->setTableName();
    //     if (is_null(static::$table_name)) {
    //         // TODO: Throw Exception
    //         exit('Table name missing in ' . get_called_class());
    //     }
    //     // $this->query = new QueryLib();
    // }

    abstract public function rules(): array;

    public function loadData(array $data)
    {
        foreach ($data as $key => $value) {
            // echo $parent_class = get_parent_class(get_called_class()) . '<br>'; exit;
            // if (property_exists(ModelForm::class, $$key)) {
            // }
            $this->$key = $value;
        }
    }

    public function validate()
    {
        foreach ($this->rules() as $attribute => $rules) {
            $value = $this->$attribute;
            foreach ($rules as $rule) {
                $ruleName = $rule;
                if (!is_string($ruleName)) {
                    $ruleName = $rule[0];
                }
                if ($ruleName === self::RULE_REQUIRED && is_blank($value)) {
                    $this->addError($attribute, self::RULE_REQUIRED);
                }
                if ($ruleName === self::RULE_EMAIL && has_valid_email_format($value)) {
                    $this->addError($attribute, self::RULE_EMAIL);
                }
                if ($ruleName === self::RULE_MIN && has_length_less_than($value, $rule['min'])) {
                    $this->addError($attribute, self::RULE_MIN, $rule);
                }
                if ($ruleName === self::RULE_MAX && has_length_greater_than($value, $rule['max'])) {
                    $this->addError($attribute, self::RULE_MAX, $rule);
                }
                if ($ruleName === self::RULE_MATCH && has_value_exactly($value, $this->{$rule['match']})) {
                    $this->addError($attribute, self::RULE_MATCH, $rule);
                }
            }
            $this->fields[] = $attribute;
        }
        return empty($this->errors);
    }

    private function addError($attribute, string $rule, $params = [])
    {
        $message = $this->errorMessages()[$rule] ?? '';
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $message = str_replace("{{$key}}", $value, $message);
            }
        }
        $this->errors[$attribute][] = $message;
    }

    public function errorMessages(): array
    {
        return self::ERROR_MESSAGES;
    }

    public function hasError(string $attribute): bool
    {
        return has_key_presence($attribute, $this->errors);
    }

    public function getFirstError(string $attribute)
    {
        return $this->errors[$attribute][0] ?? false;
    }
}