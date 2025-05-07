<?php
namespace App\Middleware;

use Exception;

class RequestValidator {
    public function validate($rules) {
        if (empty($rules)) {
            return true;
        }
        
        $errors = [];
        
        // Validate GET parameters
        foreach ($rules['get'] ?? [] as $param => $rule) {
            if (!isset($_GET[$param]) && !$rule['optional']) {
                $errors[$param] = 'Parameter is required';
                continue;
            }
            
            if (isset($_GET[$param])) {
                $value = $_GET[$param];
                $this->validateValue($value, $rule, $param, $errors);
            }
        }
        
        // Validate POST parameters
        foreach ($rules['post'] ?? [] as $param => $rule) {
            if (!isset($_POST[$param]) && !$rule['optional']) {
                $errors[$param] = 'Parameter is required';
                continue;
            }
            
            if (isset($_POST[$param])) {
                $value = $_POST[$param];
                $this->validateValue($value, $rule, $param, $errors);
            }
        }
        
        // Validate JSON body
        $jsonData = json_decode(file_get_contents('php://input'), true);
        if ($jsonData) {
            foreach ($rules['json'] ?? [] as $param => $rule) {
                if (!isset($jsonData[$param]) && !$rule['optional']) {
                    $errors[$param] = 'Parameter is required';
                    continue;
                }
                
                if (isset($jsonData[$param])) {
                    $value = $jsonData[$param];
                    $this->validateValue($value, $rule, $param, $errors);
                }
            }
        }
        
        if (!empty($errors)) {
            throw new Exception(json_encode($errors), 400);
        }
        
        return true;
    }
    
    private function validateValue($value, $rule, $param, &$errors) {
        // Type validation
        if (isset($rule['type'])) {
            switch ($rule['type']) {
                case 'string':
                    if (!is_string($value)) {
                        $errors[$param] = 'Must be a string';
                    }
                    break;
                case 'integer':
                    if (!is_numeric($value)) {
                        $errors[$param] = 'Must be an integer';
                    }
                    break;
                case 'email':
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[$param] = 'Must be a valid email';
                    }
                    break;
                case 'date':
                    if (!strtotime($value)) {
                        $errors[$param] = 'Must be a valid date';
                    }
                    break;
            }
        }
        
        // Length validation
        if (isset($rule['min_length']) && strlen($value) < $rule['min_length']) {
            $errors[$param] = "Minimum length is {$rule['min_length']}";
        }
        
        if (isset($rule['max_length']) && strlen($value) > $rule['max_length']) {
            $errors[$param] = "Maximum length is {$rule['max_length']}";
        }
        
        // Range validation
        if (isset($rule['min']) && $value < $rule['min']) {
            $errors[$param] = "Minimum value is {$rule['min']}";
        }
        
        if (isset($rule['max']) && $value > $rule['max']) {
            $errors[$param] = "Maximum value is {$rule['max']}";
        }
        
        // Pattern validation
        if (isset($rule['pattern']) && !preg_match($rule['pattern'], $value)) {
            $errors[$param] = 'Invalid format';
        }
    }
} 