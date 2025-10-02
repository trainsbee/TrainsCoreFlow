<?php
namespace Controllers;

use Core\BaseController;
use Services\UserService;
use Exception;

class UserController extends BaseController {
    protected $service;

    public function __construct($viewRenderer, $service) {
        parent::__construct($viewRenderer);
        $this->service = $service;
    }

    /** -------- Helpers -------- */
    protected function sanitizeForDb(array $data): array {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeForDb($value);
            } else {
                $value = trim((string)$value);
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }

    protected function sanitizeForView($value): string {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    protected function jsonResponse($data, int $status = 200): void {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    /** -------- VISTAS (HTML) -------- */
    public function index() {
        try {
            $usuarios = $this->service->getAll(); //
        
            $this->renderView("users/index", [
                "usuarios" => $usuarios
            ]);
        } catch (Exception $e) {
            $this->renderView("errors/500", [
                "message" => $e->getMessage()
            ]);
        }
    }
    public function getAll() {
        try {
            $usuarios = $this->service->getAll();
            $usersArray = array_map(fn($user) => $user->toArray(), $usuarios);
            $this->jsonResponse([
                "success" => true,
                "data" => $usersArray
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                "success" => false,
                "message" => $e->getMessage()
            ], 400);
        }
    }

    public function create() {
        try {
            $roles = $this->service->getAllRoles();
            $this->renderView("users/create", [
                "roles" => $roles
            ]);
        } catch (Exception $e) {
            $this->renderView("errors/500", [
                "message" => $e->getMessage()
            ]);
        }
    }
    public function getAllRoles() {
        try {
            $roles = $this->service->getAllRoles();
            $this->jsonResponse([
                "success" => true,
                "data" => $roles
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                "success" => false,
                "message" => $e->getMessage()
            ], 400);
        }
    }

    /** -------- API (JSON) -------- */
    public function store() {
        try {
            $json = file_get_contents("php://input");
            $input = json_decode($json, true) ?? [];

            if (empty($input)) {
                throw new Exception("No se recibieron datos válidos");
            }

            $userData = $this->sanitizeForDb($input);

            $FormData = [
                "user_name" => $userData["user_name"] ?? "",
                "user_email" => $userData["user_email"] ?? "",
                "user_password" => $userData["user_password"] ?? "",
                "confirm_password" => $userData["confirm_password"] ?? "",
                "role_id" => $userData["role_id"] ?? "",
            ];
            $missingFields = emptyFields($FormData);
            if (!empty($missingFields)) {
                $this->jsonResponse([
                    "success" => false,
                    "message" => "Algunos campos están vacíos.",
                    "fields" => $missingFields
                ], 400);
            }

            
           

            if (!validateEmail($userData["user_email"])) {
                throw new Exception('El formato del correo electrónico no es válido');
            }

            if (!validatePasswords($userData["user_password"], $userData["confirm_password"])) {
                throw new Exception("Las contraseñas no coinciden");
            }

            unset($userData["confirm_password"]);

            $user = $this->service->createUser($userData);

            $this->jsonResponse([
                "success" => true,
                "message" => "Usuario creado exitosamente",
                "data" => $user
            ], 201);

        } catch (Exception $e) {
            $this->jsonResponse([
                "success" => false,
                "message" => $e->getMessage()
            ], 400);
        }
    }

    public function update($id) {
        $input = json_decode(file_get_contents("php://input"), true);
        
        try {
            if (isset($input["user_id"]) && (string)$input["user_id"] !== (string)$id) {
                throw new Exception("El ID del usuario no coincide");
            }
          

            $input["user_id"] = (string)$id;
            $updatedUser = $this->service->updateUser((string)$id, $input);
            $this->jsonResponse([
                "success" => true,
                "message" => "Usuario actualizado correctamente",
                "user" => $updatedUser
            ]);

        } catch (Exception $e) {
            $this->jsonResponse([
                "success" => false,
                "message" => $e->getMessage()
            ], 400);
        }
    }

    public function delete($id) {
        try {
            if (empty($id)) {
                throw new Exception("ID de usuario no proporcionado");
            }

            $id = trim($id);
            $this->service->deleteUser($id);

            $this->jsonResponse([
                "success" => true,
                "message" => "Usuario eliminado exitosamente"
            ]);

        } catch (Exception $e) {
            $this->jsonResponse([
                "success" => false,
                "message" => $e->getMessage()
            ], 400);
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
}