<?php
namespace App\Middleware;

class ValidationMiddleware {
    private $rules;
    private $errors = [];

    public function __construct($rules) {
        $this->rules = $rules;
    }

    public function validate($data) {
        $this->errors = [];

        foreach ($this->rules as $field => $rule) {
            if (!isset($data[$field]) && strpos($rule, 'required') !== false) {
                $this->errors[$field] = "The $field field is required";
                continue;
            }

            if (isset($data[$field])) {
                $this->validateField($field, $data[$field], $rule);
            }
        }

        return empty($this->errors);
    }

    private function validateField($field, $value, $rules) {
        $rules = explode('|', $rules);

        foreach ($rules as $rule) {
            if ($rule === 'required' && empty($value)) {
                $this->errors[$field] = "The $field field is required";
                break;
            }

            if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $this->errors[$field] = "The $field must be a valid email address";
                break;
            }

            if (strpos($rule, 'min:') === 0) {
                $min = (int) substr($rule, 4);
                if (strlen($value) < $min) {
                    $this->errors[$field] = "The $field must be at least $min characters";
                    break;
                }
            }

            if (strpos($rule, 'max:') === 0) {
                $max = (int) substr($rule, 4);
                if (strlen($value) > $max) {
                    $this->errors[$field] = "The $field may not be greater than $max characters";
                    break;
                }
            }
        }
    }

    public function getErrors() {
        return $this->errors;
    }
}
?> 