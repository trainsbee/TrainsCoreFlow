<?php
namespace Services;

require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Role.php';
require_once __DIR__ . '/../Repositories/UserRepositoryInterface.php';
require_once __DIR__ . '/../Exceptions/ValidationException.php';

use Models\User;
use Models\Role;
use Repositories\UserRepositoryInterface;
use Exceptions\ValidationException;

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
        $roles = $this->getAllRoles();
        $this->validateUserData($userData, false, null, $roles);

        unset($userData['confirm_password']);
        $userData['user_password'] = password_hash($userData['user_password'], PASSWORD_BCRYPT);
        $userData['user_status'] = $userData['user_status'] ?? true;

        return $this->repository->create($userData);
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

    private function validateUserData(array $userData, bool $isUpdate = false, ?string $excludeUserId = null, ?array $roles = null) {
        $required = ['user_name', 'user_email'];
        if (!$isUpdate) {
            $required[] = 'user_password';
            $required[] = 'confirm_password';
        }

        $fieldsError = [];
        foreach ($required as $field) {
            if (!isset($userData[$field]) || trim($userData[$field]) === '') {
                $fieldsError[] = $field;
            }
        }
        if (!empty($fieldsError)) {
            throw new ValidationException("Algunos campos son requeridos", $fieldsError);
        }

        if (!filter_var($userData['user_email'], FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException("El correo electrónico no es válido", ['user_email']);
        }

        if ($this->isEmailInUse($userData['user_email'], $excludeUserId)) {
            throw new ValidationException("El correo electrónico ya está en uso", ['user_email']);
        }

        if ((!$isUpdate || isset($userData['confirm_password'])) &&
            (($userData['user_password'] ?? '') !== ($userData['confirm_password'] ?? ''))) {
            throw new ValidationException("Las contraseñas no coinciden", ['user_password','confirm_password']);
        }

        if (isset($userData['role_id'])) {
            if ($roles === null) {
                $roles = $this->getAllRoles();
            }
            $roleExists = false;
            foreach ($roles as $role) {
                $roleId = is_array($role) ? ($role['role_id'] ?? null) : $role->getRoleId();
                if ($roleId === $userData['role_id']) {
                    $roleExists = true;
                    break;
                }
            }
            if (!$roleExists) {
                throw new ValidationException("El rol seleccionado no es válido", ['role_id']);
            }
        }
    }

    public function updateUser(string $userId, array $userData): array {
        $existingUser = $this->getUserById($userId);
        if (!$existingUser) {
            throw new \Exception('Usuario no encontrado');
        }

        $roles = $this->getAllRoles();
        $this->validateUserData($userData, true, $userId, $roles);

        if (!empty($userData['user_password'])) {
            $userData['user_password'] = password_hash($userData['user_password'], PASSWORD_BCRYPT);
        } else {
            unset($userData['user_password']);
        }
        unset($userData['confirm_password']);

        return $this->repository->update($userId, $userData);
    }

    public function getUserById(string $userId): ?array {
        return $this->repository->findById($userId);
    }

    public function deleteUser(string $userId): bool {
        if (empty($userId) || !preg_match('/^[a-f0-9\-]+$/i', $userId)) {
            throw new \InvalidArgumentException('ID de usuario no válido');
        }
        $this->repository->delete($userId);
        return true;
    }
}
?>
