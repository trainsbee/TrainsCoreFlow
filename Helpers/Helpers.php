<?php
namespace Helpers;
require_once __DIR__ . '/../Exceptions/ValidationException.php';


use Exceptions\ValidationException;

class Helpers {
    public static function required(array $data, array $fields): void {
        $errors = [];
        foreach ($fields as $field) {
            if (!isset($data[$field]) || trim((string)$data[$field]) === '') {
                $errors[] = $field;
            }
        }
        if (!empty($errors)) {
            throw new ValidationException("Algunos campos son requeridos", $errors);
        }
    }
     // Validar email con opciones de dominio y caracteres
     public static function email(string $email, array $allowedDomains = ['gmail.com']): void {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException("El correo electrónico no es válido", ['user_email']);
        }
        if (!preg_match('/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/', $email)) {
            throw new ValidationException("El correo contiene caracteres no permitidos", ['user_email']);
        }
        $domain = substr(strrchr($email, "@"), 1);
        if (!in_array($domain, $allowedDomains)) {
            throw new ValidationException("Dominio de correo no permitido", ['user_email']);
        }
    }

    // Validar longitud mínima y máxima de un campo
    public static function length(string $field, string $value, int $min = 0, int $max = 255): void {
        $len = mb_strlen($value);
        if ($len < $min || $len > $max) {
            throw new ValidationException("El campo $field debe tener entre $min y $max caracteres", [$field]);
        }
    }

    // Comparar dos campos (ej: password y confirm_password)
    public static function match(string $value1, string $value2, string $field1, string $field2, string $message = null): void {
        if ($value1 !== $value2) {
            throw new ValidationException($message ?? "Los campos no coinciden", [$field1, $field2]);
        }
    }
 
}