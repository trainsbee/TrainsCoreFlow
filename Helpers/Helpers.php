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
    
    public static function cleanInjection(string $value): string {
        // 1) Normalizar encoding
        if (!mb_check_encoding($value, 'UTF-8')) {
            $value = mb_convert_encoding($value, 'UTF-8', 'auto');
        }

        // 2) Quitar caracteres de control/invisibles
        $value = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $value);

        // 3) Eliminar etiquetas HTML (evita scripts simples)
        $value = strip_tags($value);

        // 4) Eliminar secuencias comunes de escape y barras
        $value = str_replace(["\\", "\0"], '', $value);

        // 5) Remover patrones peligrosos SQL / JS comunes
        //    - Comentarios SQL (--, #, /* */)
        //    - Puntos y comas terminadores
        //    - UNION SELECT, OR 1=1, DROP TABLE, etc.
        $patterns = [
            '/\bUNION\b\s+\bSELECT\b/i',
            '/\bSELECT\b/i',
            '/\bINSERT\b/i',
            '/\bDELETE\b/i',
            '/\bUPDATE\b/i',
            '/\bDROP\b/i',
            '/\bTRUNCATE\b/i',
            '/\bALTER\b/i',
            '/\bCREATE\b/i',
            '/\bREPLACE\b/i',
            '/\bEXEC\b/i',
            '/\bEXECUTE\b/i',
            '/(\-\-|\#|\/\*|\*\/)/',   // comentarios
            '/;/',                     // punto y coma
            '/\bOR\b\s+1\s*=\s*1\b/i',
            '/\bAND\b\s+1\s*=\s*1\b/i',
            '/\bWHERE\b/i',
            '/\bFROM\b/i'
        ];
        $value = preg_replace($patterns, ' ', $value);

        // 6) Quitar operadores lógicos o comparadores sueltos
        $value = preg_replace('/([\'"`=<>]|\/|\\\\)/', ' ', $value);

        // 7) Quitar cualquier secuencia "OR 1=1" alternativa (insensible a espacios)
        $value = preg_replace('/\bOR\b\s*\d+\s*=\s*\d+/i', ' ', $value);

        // 8) Normalizar espacios
        $value = preg_replace('/\s+/', ' ', trim($value));

        // 9) Finalmente, stripslashes (por si quedó)
        $value = stripslashes($value);

        return $value;
    }
      public static function numeric($value, bool $integerOnly = false): bool {
        if ($integerOnly) {
            return filter_var($value, FILTER_VALIDATE_INT) !== false;
        }
        return is_numeric($value);
    }
 
}