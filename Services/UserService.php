<?php
namespace Services;

require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Role.php';
require_once __DIR__ . '/../Repositories/UserRepositoryInterface.php';
require_once __DIR__ . '/../Helpers/Helpers.php';

use Models\User;
use Models\Role;
use Repositories\UserRepositoryInterface;
use Exceptions\ValidationException;
use Helpers\Helpers;

class UserService {
    private $repository;

    public function __construct(UserRepositoryInterface $repository) {
        $this->repository = $repository;
    }

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
    

    public function createUser(array $userData): array {
        $required = ['user_name', 'user_email', 'user_password', 'confirm_password', 'create_role_id'];
        $roles = $this->getAllRoles();

        

        Helpers::required($userData, $required);
        Helpers::email($userData['user_email']);
        Helpers::match($userData['user_password'], $userData['confirm_password'], 'user_password', 'confirm_password');

        unset($userData['confirm_password']);
        $userData['user_password'] = password_hash($userData['user_password'], PASSWORD_BCRYPT);
        $userData['user_status'] = $userData['user_status'] ?? true;

        $userData['role_id'] = $userData['create_role_id'];
        unset($userData['create_role_id']);
        
        $existingUserName = $this->isUserInUse($userData['user_name']);
        if ($existingUserName) {
            throw new ValidationException("El usuario ya existe", ['user_name']);
        }

        $existingUser = $this->isEmailInUse($userData['user_email']);
        if ($existingUser) {
            throw new ValidationException("El usuario ya existe", ['user_email']);
        }

        if (isset($userData['role_id'])) {
            $role = $this->getRoleById($userData['role_id']);
            if (!$role) {
                throw new ValidationException("El rol seleccionado no es válido", ['role_id']);
            }
        }

        return $this->repository->create($userData);
    }

    public function updateUser(string $userId, array $userData): array {
  
        $existingUser = $this->getUserById($userId);
        if (!$existingUser) {
            throw new \Exception('Usuario no encontrado');
        }
          
        if (isset($userData['edit_role_id'])) {
            $role = $this->getRoleById($userData['edit_role_id']);
            if (!$role) {
                throw new ValidationException("El rol seleccionado no es válido", ['edit_role_id']);
            }
            // Mapear al nombre correcto para la DB
            $userData['role_id'] = $userData['edit_role_id'];
            unset($userData['edit_role_id']);
        }
        $required = ['user_name', 'user_email', 'role_id'];
        Helpers::required($userData, $required);
        Helpers::email($userData['user_email'], ['gmail.com']);

        $existingUser = $this->isUserInUse($userData['user_name'], $userId);
        if ($existingUser) {
            throw new ValidationException("El usuario ya existe", ['user_name']);
        }

        $existingUser = $this->isEmailInUse($userData['user_email'], $userId);
        if ($existingUser) {
            throw new ValidationException("El usuario ya existe", ['user_email']);
        }

        if (isset($userData['role_id'])) {
            $role = $this->getRoleById($userData['role_id']);
            if (!$role) {
                throw new ValidationException("El rol seleccionado no es válido", ['role_id']);
            }
        }

        return $this->repository->update($userId, $userData);
    }

    public function getRoleById(string $roleId): ?array {
        try {
            return $this->repository->getRoleById($roleId);
        } catch (\Exception $e) {
            if (defined('APP_DEBUG') && APP_DEBUG) {
                error_log('Error en UserService->getRoleById(): ' . $e->getMessage());
            }
            return null;
        }
    }

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
    public function isUserInUse(string $userName, ?string $excludeUserId = null): bool {
        try {
            return $this->repository->isUserInUse($userName, $excludeUserId);
        } catch (\Exception $e) {
            $errorMsg = 'Error al verificar el usuario: ' . $e->getMessage();
            if (defined('APP_DEBUG') && APP_DEBUG) {
                error_log('Error en UserService->isUserInUse(): ' . $errorMsg);
            }
            throw new \Exception($errorMsg, 0, $e);
        }
    }
    public function isEmailInUse(string $email, ?string $excludeUserId = null): bool {
        try {
            return $this->repository->isEmailInUse($email, $excludeUserId);
        } catch (\Exception $e) {
            $errorMsg = 'Error al verificar el correo electrónico: ' . $e->getMessage();
            if (defined('APP_DEBUG') && APP_DEBUG) {
                error_log('Error en UserService->isEmailInUse(): ' . $errorMsg);
            }
            throw new \Exception($errorMsg, 0, $e);
        }
    }

    public function getUserById(string $userId): ?array {
        return $this->repository->findById($userId);
    }

    public function deleteUser(string $userId): bool {
        $existingUser = $this->getUserById($userId);
        if (!$existingUser) {
            throw new \InvalidArgumentException('Usuario no encontrado');
        }
        if (empty($userId) || !preg_match('/^[a-f0-9\-]+$/i', $userId)) {
            throw new \InvalidArgumentException('ID de usuario no válido');
        }
        $this->repository->delete($userId);
        return true;
    }
}
?>