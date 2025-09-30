<?php
namespace Services;

require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Role.php';
require_once __DIR__ . '/../Repositories/UserRepositoryInterface.php';

use Models\User;
use Models\Role;
use Repositories\UserRepositoryInterface;

class UserService {
    private $repository;

    public function __construct(UserRepositoryInterface $repository) {
        $this->repository = $repository;
    }

    /**
     * Obtener todos los usuarios
     * 
     * @return array Array de objetos User
     */
    public function getAll(): array {
        try {
            return $this->repository->getAll();
        } catch (\Exception $e) {
            if (defined('APP_DEBUG') && APP_DEBUG) {
                error_log('Error en UserService->getAll(): ' . $e->getMessage());
            }
            return [];
        }
    }

    /**
     * Crear un nuevo usuario
     * 
     * @param array $userData Datos del usuario a crear
     * @return array Datos del usuario creado
     * @throws \Exception Si ocurre un error al crear el usuario
     */
    public function createUser(array $userData): array {
        try {
            // Obtener roles una sola vez para validación
            $roles = $this->getAllRoles();
            
            // Validar datos del usuario (incluye validación de campos requeridos, email, contraseña y rol)
            $this->validateUserData($userData, false, null, $roles);
            
            // Hashear la contraseña antes de guardarla
            $userData['user_password'] = password_hash($userData['user_password'], PASSWORD_BCRYPT);
            
            // Asegurar que el estado esté definido
            $userData['user_status'] = $userData['user_status'] ?? true;
            
            // Crear el usuario usando el repositorio
            return $this->repository->create($userData);
            
        } catch (\Exception $e) {
            if (defined('APP_DEBUG') && APP_DEBUG) {
                error_log('Error en UserService->createUser(): ' . $e->getMessage());
            }
            throw $e;
        }
    }

    /**
     * Obtener todos los roles
     * 
     * @return array Array de objetos Role
     */
    public function getAllRoles(): array {
        try {
            return $this->repository->getAllRoles();
        } catch (\Exception $e) {
            if (defined('APP_DEBUG') && APP_DEBUG) {
                error_log('Error en UserService->getAllRoles(): ' . $e->getMessage());
            }
            return [];
        }
    }

    /**
     * Verificar si un correo electrónico ya está en uso por otro usuario
     * 
     * @param string $email Correo electrónico a verificar
     * @param string|null $excludeUserId ID de usuario a excluir de la verificación (para actualizaciones)
     * @return bool True si el correo ya está en uso, false en caso contrario
     */
    /**
     * Verificar si un correo electrónico ya está en uso
     * 
     * @param string $email Correo electrónico a verificar
     * @param string|null $excludeUserId ID de usuario a excluir (para actualizaciones)
     * @return bool True si el correo está en uso, false si no
     * @throws \Exception Si ocurre un error al verificar el correo
     */
    public function isEmailInUse(string $email, ?string $excludeUserId = null): bool {
        try {
            return $this->repository->isEmailInUse($email, $excludeUserId);
        } catch (\Exception $e) {
            $errorMsg = 'Error al verificar el correo electrónico: ' . $e->getMessage();
            if (defined('APP_DEBUG') && APP_DEBUG) {
                error_log('Error en UserService->isEmailInUse(): ' . $errorMsg);
            }
            // Lanzar la excepción para que el llamador pueda manejarla apropiadamente
            throw new \Exception($errorMsg, 0, $e);
        }
    }
    
    /**
     * Validar datos del usuario
     * 
     * @param array $userData Datos del usuario a validar
     * @param bool $isUpdate Indica si es una actualización (false para creación)
     * @param string|null $excludeUserId ID de usuario a excluir en la validación de email (para actualizaciones)
     * @param array|null $roles Lista opcional de roles para validación (evita consulta adicional)
     * @throws \Exception Si la validación falla
     */
    private function validateUserData(array $userData, bool $isUpdate = false, ?string $excludeUserId = null, ?array $roles = null) {
        // Validar campos requeridos
        $required = ['user_name', 'user_email'];
        
        if (!$isUpdate) {
            $required[] = 'user_password';
        }
        
        foreach ($required as $field) {
            if (!isset($userData[$field]) || $userData[$field] === '') {
                throw new \Exception("El campo $field es requerido");
            }
        }
        
        // Validar formato de email
        if (!filter_var($userData['user_email'], FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('El formato del correo electrónico no es válido');
        }
        
        // Validar que el email no esté en uso
        if ($this->isEmailInUse($userData['user_email'], $excludeUserId)) {
            throw new \Exception('El correo electrónico ya está en uso por otro usuario');
        }
        
        // Validar que la contraseña esté presente en creación
        if (!$isUpdate && empty($userData['user_password'])) {
            throw new \Exception('La contraseña es requerida');
        }
        
        // Validar rol si se proporciona
        if (isset($userData['role_id'])) {
            // Obtener roles si no se proporcionaron
            if ($roles === null) {
                $roles = $this->getAllRoles();
            }
            
            $roleExists = false;
            $roleIdToCheck = $userData['role_id'];
            
            foreach ($roles as $role) {
                $roleId = is_array($role) ? ($role['role_id'] ?? null) : $role->getRoleId();
                if ($roleId === $roleIdToCheck) {
                    $roleExists = true;
                    break;
                }
            }
            
            if (!$roleExists) {
                throw new \Exception('El rol seleccionado no es válido');
            }
        }
    }
    
    /**
     * Actualizar un usuario existente
     * 
     * @param string $userId ID del usuario a actualizar
     * @param array $userData Datos del usuario a actualizar
     * @return array Datos del usuario actualizado
     * @throws \Exception Si ocurre un error al actualizar el usuario
     */
    public function updateUser(string $userId, array $userData): array {
        try {
            // Verificar si el usuario existe
            $existingUser = $this->getUserById($userId);
            if (!$existingUser) {
                throw new \Exception('Usuario no encontrado');
            }
            
            // Obtener roles una sola vez para validación
            $roles = $this->getAllRoles();
            
            // Validar datos del usuario (incluye validación de email y contraseña si se proporcionan)
            $this->validateUserData($userData, true, $userId, $roles);
            
            // Si se proporcionó una nueva contraseña, hashearla
            if (!empty($userData['user_password'])) {
                $userData['user_password'] = password_hash($userData['user_password'], PASSWORD_BCRYPT);
            } else {
                // No actualizar la contraseña si no se proporciona una nueva
                unset($userData['user_password']);
            }
            
            // Actualizar el usuario usando el repositorio
            return $this->repository->update($userId, $userData);
            
        } catch (\Exception $e) {
            if (defined('APP_DEBUG') && APP_DEBUG) {
                error_log('Error en UserService->updateUser(): ' . $e->getMessage());
            }
            throw $e;
        }
    }
    
    /**
     * Obtener un usuario por su ID (público)
     * 
     * @param string $userId ID del usuario a buscar
     * @return array|null Datos del usuario o null si no se encuentra
     * @throws \Exception Si ocurre un error al buscar el usuario
     */
    public function getUserById(string $userId): ?array {
        try {
            return $this->repository->findById($userId);
        } catch (\Exception $e) {
            if (defined('APP_DEBUG') && APP_DEBUG) {
                error_log('Error en UserService->getUserById(): ' . $e->getMessage());
            }
            throw $e;
        }
    }
    
    /**
     * Eliminar un usuario
     * 
     * @param string $userId ID del usuario a eliminar
     * @return bool Siempre devuelve true si no hay errores
     * @throws \InvalidArgumentException Si el ID no es válido
     * @throws \Exception Si ocurre un error al eliminar el usuario
     */
    public function deleteUser(string $userId): bool {
        try {
            // Validar que el ID no esté vacío y tenga formato válido (UUID o número)
            if (empty($userId)) {
                throw new \InvalidArgumentException('ID de usuario no proporcionado');
            }
            
            // Validar formato del ID (permite UUIDs o números)
            if (!preg_match('/^[a-f0-9\-]+$/i', $userId)) {
                throw new \InvalidArgumentException('Formato de ID no válido');
            }
            
            // Intentar eliminar el usuario
            // No verificamos si existe primero porque Supabase maneja esto eficientemente
            $result = $this->repository->delete($userId);
            
            // Si llegamos aquí, la operación fue exitosa
            // Supabase devuelve éxito incluso si el usuario no existía
            return true;
            
        } catch (\Exception $e) {
            if (defined('APP_DEBUG') && APP_DEBUG) {
                error_log('Error en UserService->deleteUser(): ' . $e->getMessage());
            }
            throw $e;
        }
    }
}
?>
