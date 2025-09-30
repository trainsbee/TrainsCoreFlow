<?php
// Cargar variables de entorno desde .env
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) continue;
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        // Remover comillas si existen
        $firstChar = $value[0];
        if (($firstChar === '"' || $firstChar === "'") && $firstChar === substr($value, -1)) {
            $value = substr($value, 1, -1);
        }
        
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
        }
        if (!array_key_exists($name, $_SERVER)) {
            $_SERVER[$name] = $value;
        }
        putenv("$name=$value");
    }
}

// Configuración de la aplicación
define('APP_DEBUG', true); // Cambiar a false en producción

// Configuración de Supabase
define('SUPABASE_URL', $_ENV['SUPABASE_URL'] ?? '');
define('SUPABASE_KEY', $_ENV['SUPABASE_KEY'] ?? '');

// Configuración de la aplicación
define('APP_NAME', 'Sistema de Gestión de Usuarios');
define('APP_URL', 'http://localhost/trainscoreflow');

// Configuración de la base de datos (si es necesario)
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');

// Configuración de seguridad SSL
define('SSL_VERIFY', false); // Cambiar a true en producción

// Configuración de sesión
if (session_status() === PHP_SESSION_NONE) {
    // Solo configurar la sesión si no está activa
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Cambiar a 1 si usas HTTPS
    
    // Iniciar sesión después de configurarla
    session_start();
}

// Zona horaria
date_default_timezone_set('America/Mexico_City'); // Ajusta según tu zona horaria

// Manejo de errores
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Constantes para rutas
define('BASE_PATH', dirname(__DIR__));
define('VIEWS_PATH', BASE_PATH . '/Presentation');

// Otras configuraciones globales

// Función para obtener la URL base de la aplicación
function base_url($path = '') {
    return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
}

// Función para redirigir
function redirect($path) {
    header('Location: ' . base_url($path));
    exit();
}

// Función para depuración
function debug($data) {
    if (APP_DEBUG) {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }
}
?>
