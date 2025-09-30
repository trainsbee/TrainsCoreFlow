<?php
namespace Controllers;

use Core\BaseController;
use Services\UserService;
use Models\User;
use Models\Role;

use Exception;

class UserController extends BaseController {
    protected $service;

    public function __construct($viewRenderer, $service) {
        parent::__construct($viewRenderer);
        $this->service = $service;
    }
    
    /**
     * Sanitiza un array de datos
     * 
     * @param array $data Datos a sanitizar
     * @return array Datos sanitizados
     */
    protected function sanitizeInput(array $data): array {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeInput($value);
            } else {
                // Eliminar espacios en blanco al inicio y final
                $value = trim($value);
                // Convertir a string si no es nulo
                $value = $value === null ? '' : (string)$value;
                // Escapar caracteres especiales
                $sanitized[$key] = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
        }
        return $sanitized;
    }
    
    /**
     * Envía una respuesta JSON
     * 
     * @param mixed $data Datos a enviar
     * @param int $status Código de estado HTTP
     */
    protected function jsonResponse($data, int $status = 200): void {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }
    
    /**
     * Determina si la solicitud espera una respuesta JSON
     * 
     * @return bool
     */
    protected function wantsJson(): bool {
        $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
        return strpos($acceptHeader, 'application/json') !== false || 
               (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }

    public function index() {
        try {
            $usuarios = $this->service->getAll();
            $this->renderView('users.index', [
                'usuarios' => $usuarios
            ], [
                'title' => 'Usuarios - Sistema de Gestión',
                'description' => 'Listado de usuarios registrados en el sistema'
            ]);
        } catch (\Exception $e) {
            $this->renderError('Error al cargar la lista de usuarios: ' . $e->getMessage(), 500);
        }
    }

    public function edit($id) {
        try {
            // Obtener la lista de roles para el formulario
            $roles = $this->service->getAllRoles();
            
            // Verificar si hay roles disponibles
            if (empty($roles)) {
                throw new \Exception('No se encontraron roles disponibles. Por favor, asegúrese de que existan roles en la base de datos.');
            }
            
            // Obtener los datos del usuario
            $user = $this->service->getUserById($id);
            
            if (!$user) {
                throw new \Exception('Usuario no encontrado');
            }
            
            // Asegurarse de que los datos del usuario tengan los campos necesarios
            $user = array_merge([
                'user_id' => '',
                'user_name' => '',
                'user_email' => '',
                'user_status' => true,
                'role_id' => ''
            ], $user);
            
            // Asegurarse de que los roles tengan el formato correcto
            $formattedRoles = [];
            foreach ($roles as $role) {
                if (is_array($role)) {
                    $formattedRoles[] = $role;
                } elseif (is_object($role) && method_exists($role, 'toArray')) {
                    $formattedRoles[] = $role->toArray();
                } elseif (is_object($role)) {
                    $formattedRoles[] = (array) $role;
                }
            }
            
            // Incluir la vista con los datos del usuario y los roles
            $this->renderView('users.edit', [
                'user' => $user,
                'roles' => $formattedRoles
            ], [
                'title' => 'Editar Usuario - Sistema de Gestión',
                'description' => 'Formulario para editar datos del usuario'
            ]);
            
        } catch (\Exception $e) {
            $this->renderError('Error al cargar el formulario de edición: ' . $e->getMessage(), 500);
        }
    }

    
    public function update($id) {
        try {
            // Obtener datos del formulario
            $input = [];
            
            // Verificar si es una solicitud JSON
            $isJson = !empty($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;
            
            if ($isJson) {
                // Obtener datos JSON del cuerpo de la petición
                $json = file_get_contents('php://input');
                $input = json_decode($json, true) ?? [];
            } else {
                // Obtener datos del formulario normal
                $input = $_POST;
            }
            
            // Validar que el ID de la ruta coincida con el ID del formulario si existe
            if (isset($input['user_id']) && $input['user_id'] !== $id) {
                throw new \Exception('El ID del usuario no coincide');
            }
            
            // Asegurarse de que el ID esté en los datos
            $input['user_id'] = $id;
            
            // Actualizar el usuario usando el servicio
            $updatedUser = $this->service->updateUser($id, $input);
            
            // Manejar respuesta para API o navegador
            if ($isJson || !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                http_response_code(200);
                echo json_encode([
                    'success' => true, 
                    'message' => 'Usuario actualizado correctamente',
                    'user' => $updatedUser
                ]);
                return;
            } else {
                // Redirigir a la lista de usuarios con mensaje de éxito
                $_SESSION['success_message'] = 'Usuario actualizado correctamente';
                header('Location: /supabase/users');
                exit();
            }
            
        } catch (\Exception $e) {
            error_log('Error en UserController->update(): ' . $e->getMessage());
            
            if ($this->wantsJson()) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => ['general' => $e->getMessage()]
                ], 400);
            } else {
                // En caso de error, volver al formulario con los datos ingresados
                $roles = $this->service->getAllRoles();
                $user = $this->service->getUserById($id);
                
                // Combinar datos existentes con los nuevos (usando datos sanitizados)
                $userData = array_merge($user, $userData);
                
                $this->renderView('users.edit', [
                    'user' => $userData,
                    'roles' => $roles,
                    'error' => $e->getMessage()
                ], [
                    'title' => 'Editar Usuario - Sistema de Gestión',
                    'description' => 'Formulario para editar un usuario existente'
                ]);
            }
        }
    }

    /**
     * Elimina un usuario existente
     * 
     * @param string $id ID del usuario a eliminar
     * @return void
     */
    public function delete($id) {
        try {
            // Validar que el ID no esté vacío
            if (empty($id)) {
                throw new \InvalidArgumentException('ID de usuario no proporcionado');
            }
            
            // Sanitizar el ID
            $id = trim($id);
            
            // Eliminar el usuario usando el servicio
            // No necesitamos verificar el resultado ya que deleteUser lanzará una excepción si hay un error
            $this->service->deleteUser($id);
            
            // Manejar respuesta para API o navegador
            if ($this->wantsJson()) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Usuario eliminado exitosamente'
                ]);
            } else {
                $_SESSION['success_message'] = 'Usuario eliminado correctamente';
                header('Location: /users');
                exit;
            }
            
        } catch (\Exception $e) {
            error_log('Error en UserController->delete(): ' . $e->getMessage());
            
            if ($this->wantsJson()) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => ['general' => $e->getMessage()]
                ], 400);
            } else {
                $_SESSION['error_message'] = 'Error al eliminar el usuario: ' . $e->getMessage();
                header('Location: /users');
                exit;
            }
        }
    }
    
    public function create() {
        try {
            // Obtener la lista de roles para el formulario
            $roles = $this->service->getAllRoles();
            
            // Verificar si hay roles disponibles
            if (empty($roles)) {
                throw new \Exception('No se encontraron roles disponibles. Por favor, asegúrese de que existan roles en la base de datos.');
            }
            
            // Incluir la vista con los roles
            $this->renderView('users.create', [
                'roles' => $roles
            ], [
                'title' => 'Crear Usuario - Sistema de Gestión',
                'description' => 'Formulario para crear un nuevo usuario en el sistema'
            ]);
            
        } catch (\Exception $e) {
            $this->renderError('Error al cargar el formulario de creación: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Almacena un nuevo usuario en la base de datos
     * 
     * @return void
     */
    public function store() {
        try {
            // Sanitizar los datos de entrada
            $userData = $this->sanitizeInput($_POST);
            
            // Validar campos requeridos
            $required = ['user_name', 'user_email', 'user_password', 'confirm_password', 'role_id'];
            $missingFields = [];
            
            foreach ($required as $field) {
                if (empty($userData[$field])) {
                    $missingFields[] = $field;
                }
            }
            
            if (!empty($missingFields)) {
                throw new \Exception('Los siguientes campos son requeridos: ' . implode(', ', $missingFields));
            }
            
            // Validar formato de email
            if (!filter_var($userData['user_email'], FILTER_VALIDATE_EMAIL)) {
                throw new \Exception('El formato del correo electrónico no es válido');
            }
            
            // Validar que las contraseñas coincidan
            if ($userData['user_password'] !== $userData['confirm_password']) {
                throw new \Exception('Las contraseñas no coinciden');
            }
            
            // Eliminar confirm_password ya que no es necesario para el servicio
            unset($userData['confirm_password']);
            
            // Crear el usuario usando el servicio
            $user = $this->service->createUser($userData);
            
            // Manejar respuesta para API o navegador
            if ($this->wantsJson()) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Usuario creado exitosamente',
                    'data' => $user
                ], 201);
            } else {
                $_SESSION['success_message'] = 'Usuario creado exitosamente';
                header('Location: /users');
                exit;
            }
            
        } catch (\Exception $e) {
            error_log('Error en UserController->store(): ' . $e->getMessage());
            
            if ($this->wantsJson()) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => ['general' => $e->getMessage()]
                ], 400);
            } else {
                // En caso de error, volver al formulario con los datos ingresados
                $roles = $this->service->getAllRoles();
                
                $this->renderView('users.create', [
                    'roles' => $roles,
                    'user' => $userData, // Usar datos sanitizados
                    'error' => $e->getMessage()
                ], [
                    'title' => 'Crear Usuario - Sistema de Gestión',
                    'description' => 'Formulario para crear un nuevo usuario'
                ]);
            }
        }
        exit();
    }
}
