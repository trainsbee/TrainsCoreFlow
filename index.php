<?php
// Cargar configuración
require_once __DIR__ . '/Config/Config.php';

// Cargar el autoloader
require_once __DIR__ . '/autoload.php';

// Usar los namespaces necesarios
use Core\SupabaseClient;
use Core\ViewRenderer;
use Core\Router;
use Repositories\SupabaseUserRepository;
use Services\UserService;
use Controllers\HomeController;
use Controllers\UserController;

// Crear instancia del cliente Supabase
$client = new SupabaseClient(SUPABASE_URL, SUPABASE_KEY);

// Crear ViewRenderer
$viewRenderer = new ViewRenderer();

// Inicializar el sistema de inyección de dependencias
$container = [];

// Registrar dependencias
$container['userRepository'] = new SupabaseUserRepository($client);
$container['userService'] = new UserService($container['userRepository']);

// Crear controladores con dependencias
$homeController = new HomeController($viewRenderer);
$userController = new UserController($viewRenderer, $container['userService']);

// Crear router
$router = new Router();

// Registrar controladores en el router
$router->registerController('home', $homeController);
$router->registerController('user', $userController);

// Rutas
$router->get('', ['home', 'index']);

// User routes
$router->get('/users', ['user', 'index']);
$router->get('/users/create', ['user', 'create']);
$router->post('/users/store', ['user', 'store']);
$router->get('/users/{id}/edit', ['user', 'edit']);
$router->post('/users/{id}/update', ['user', 'update']);
$router->post('/users/{id}/delete', ['user', 'delete']);

// Get the request URI and method
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base_path = '/trainscoreflow';

// Remove base path from the URI
$uri = str_replace($base_path, '', $request_uri);
$uri = $uri === '' ? '/' : $uri;

// Remove any trailing slashes
$uri = rtrim($uri, '/');

// Get the request method
$method = $_SERVER['REQUEST_METHOD'];

// Dispatch the request
$router->dispatch($uri, $method);



