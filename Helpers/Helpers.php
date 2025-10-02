<?php
function emptyFields(array $fields): array
{
    $emptyFields = [];
    foreach ($fields as $name => $value)
    {
        if (empty($value))
        {
            $emptyFields[] = $name;
        }
    }
    return $emptyFields;
}

/**
 * Verificar un correo electrónico valido
 */
function validateEmail(string $email): bool
{
    $email = trim($email);

    // Primero validar el original
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    // Opcional: comprobar que no haya caracteres raros (más estricto)
    if (!preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", $email)) {
        return false;
    }

    return true;
}


/**
 * Verificar que las contraseñas coincidan
 */
function validatePasswords(string $password, string $confirmPassword): bool
{
    $password = trim($password);
    $confirmPassword = trim($confirmPassword);
    return $password === $confirmPassword;
}
