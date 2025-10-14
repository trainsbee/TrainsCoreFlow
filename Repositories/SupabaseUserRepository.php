<?php

namespace Repositories;

use Core\SupabaseClient;
use Models\User;
use Models\Role;

class SupabaseUserRepository implements UserRepositoryInterface
{
    private $client;
    
    public function __construct(SupabaseClient $client)
    {
        $this->client = $client;
    }
    
    public function getAll(): array
    {
        try {
            $response = $this->client->request(
                'rest/v1/users',
                'GET',
                null,
                'select=*,roles(role_name)'
            );
            
            if (empty($response['data'])) {
                return [];
            }
            
            return array_map(function($userData) {
                return User::fromArray($userData);
            }, $response['data']);
            
        } catch (\Exception $e) {
            if (defined('APP_DEBUG') && APP_DEBUG) {
                error_log('Error in SupabaseUserRepository->getAll(): ' . $e->getMessage());
            }
            throw $e;
        }
    }
public function getByPage1(int $page = 1, int $perPage = 10): array
{
    try {
        $offset = ($page - 1) * $perPage;

        // 1️⃣ Traemos usuarios paginados
        $queryUsers = "select=*,roles(role_name)&limit={$perPage}&offset={$offset}";
        $response = $this->client->request('rest/v1/users', 'GET', null, $queryUsers);

        $users = [];
        if (!empty($response['data']) && is_array($response['data'])) {
            $users = array_map(fn($userData) => User::fromArray($userData), $response['data']);
        }

        // 2️⃣ Contamos total de registros correctamente
        $countResponse = $this->client->request(
            'rest/v1/users',
            'GET',
            null,
            'select=user_id',
            ['prefer' => 'count=exact']
        );

        $totalRecords = !empty($countResponse['data']) ? count($countResponse['data']) : 0;
        $totalPages = $totalRecords > 0 ? ceil($totalRecords / $perPage) : 1;

        return [
            'users' => $users,
            'pagination' => [
                'currentPage' => $page,
                'perPage' => $perPage,
                'totalPages' => $totalPages,
                'totalRecords' => $totalRecords
            ]
        ];

    } catch (\Exception $e) {
        if (defined('APP_DEBUG') && APP_DEBUG) {
            error_log('Error in SupabaseUserRepository->getByPage(): ' . $e->getMessage());
        }
        throw $e;
    }
}
public function getByPage(int $page = 1, int $perPage = 10, $startDate = null, $endDate = null): array
{
    try {
        $offset = ($page - 1) * $perPage;

        // Construir filtros base
        $queryUsers = "select=*,roles(role_name)&limit={$perPage}&offset={$offset}";
        
        // ➕ Cambio: Agregar filtro por rango de fechas si se proporcionan
        if ($startDate !== null) {
            $queryUsers .= "&created_at=gte.{$startDate}";
        }
        if ($endDate !== null) {
            // Opcional: Agregar fin del día al endDate para incluir todo el día (ej. 2025-10-14T23:59:59)
            // $endDate = $endDate . 'T23:59:59'; // Descomenta si necesitas rango inclusivo por día
            $queryUsers .= "&created_at=lte.{$endDate}";
        }

        // 1️⃣ Traemos usuarios paginados con filtro
        $response = $this->client->request('rest/v1/users', 'GET', null, $queryUsers);

        $users = [];
        if (!empty($response['data']) && is_array($response['data'])) {
            $users = array_map(fn($userData) => User::fromArray($userData), $response['data']);
        }

        // ➕ Cambio: Construir query para conteo con el mismo filtro de fechas
        $countQuery = 'select=user_id';
        if ($startDate !== null) {
            $countQuery .= "&created_at=gte.{$startDate}";
        }
        if ($endDate !== null) {
            $countQuery .= "&created_at=lte.{$endDate}";
        }

        // 2️⃣ Contamos total de registros con filtro
        $countResponse = $this->client->request(
            'rest/v1/users',
            'GET',
            null,
            $countQuery,
            ['prefer' => 'count=exact']
        );

        // En Supabase con prefer count=exact, el total viene en headers, no en data. ¡Corrección importante!
        // Asumiendo que tu $this->client maneja headers, ajusta si es necesario.
        // Ejemplo: $totalRecords = $countResponse['headers']['content-range'][1] ?? 0; // Mejor usa headers
        // Pero como en tu código usas count(data), lo adapto: si usas count=exact, data está vacío y total en header.
        // ➕ Recomendación: Cambia a leer header para precisión.
        $totalRecords = isset($countResponse['headers']['content-range']) 
            ? (int) explode('/', $countResponse['headers']['content-range'])[1] 
            : (!empty($countResponse['data']) ? count($countResponse['data']) : 0);

        $totalPages = $totalRecords > 0 ? ceil($totalRecords / $perPage) : 1;

        return [
            'users' => $users, // Cambiado de 'data' a 'users' para consistencia, pero ajusta si tu frontend espera 'data'
            'pagination' => [
                'currentPage' => $page,
                'perPage' => $perPage,
                'totalPages' => $totalPages,
                'totalRecords' => $totalRecords
            ]
        ];

    } catch (\Exception $e) {
        if (defined('APP_DEBUG') && APP_DEBUG) {
            error_log('Error in SupabaseUserRepository->getByPage(): ' . $e->getMessage());
        }
        throw $e;
    }
}



    public function getRoleById(string $roleId): ?array {
        try {
            $response = $this->client->request(
                'rest/v1/roles',
                'GET',
                null,
                'role_id=eq.' . $roleId . '&select=role_id,role_name'
            );
            
            if (empty($response['data'])) {
                return null;
            }
            
            return $response['data'][0];
            
        } catch (\Exception $e) {
            if (defined('APP_DEBUG') && APP_DEBUG) {
                error_log('Error in SupabaseUserRepository->getRoleById(): ' . $e->getMessage());
            }
            return null;
        }
    }
    
    public function findById(string $id): ?array
    {
        try {
            $response = $this->client->request(
                'rest/v1/users',
                'GET',
                null,
                'user_id=eq.' . $id . '&select=*,roles(role_name)'
            );
            
            if (empty($response['data'])) {
                return null;
            }
            
            $userData = $response['data'][0];
            if (is_object($userData)) {
                $userData = (array) $userData;
            }
            
            // Asegurar que los campos requeridos estén presentes
            $userData['user_id'] = $userData['user_id'] ?? $id;
            $userData['user_status'] = $userData['user_status'] ?? true;
            
            // Convertir a objeto User y luego a array para asegurar consistencia
            return User::fromArray($userData)->toArray();
            
        } catch (\Exception $e) {
            if (defined('APP_DEBUG') && APP_DEBUG) {
                error_log('Error in SupabaseUserRepository->findById(): ' . $e->getMessage());
            }
            throw $e;
        }
    }
    
    public function create(array $data): array
    {
        print_r($data);
        try {
            $response = $this->client->request(
                'rest/v1/users',
                'POST',
                $data,
                null,
                ['Prefer' => 'return=representation']
            );
            
            if (empty($response['data'])) {
                throw new \Exception('No se pudo crear el usuario');
            }
            
            return $response['data'][0];
            
        } catch (\Exception $e) {
            if (defined('APP_DEBUG') && APP_DEBUG) {
                error_log('Error in SupabaseUserRepository->create(): ' . $e->getMessage());
            }
            throw $e;
        }
    }
    
    public function update(string $id, array $data): array
    {
        try {
            $response = $this->client->request(
                'rest/v1/users',
                'PATCH',
                $data,
                'user_id=eq.' . $id,
                ['Prefer' => 'return=representation']
            );
            
            if (empty($response['data'])) {
                throw new \Exception('No se pudo actualizar el usuario');
            }
            
            return $response['data'][0];
            
        } catch (\Exception $e) {
            if (defined('APP_DEBUG') && APP_DEBUG) {
                error_log('Error in SupabaseUserRepository->update(): ' . $e->getMessage());
            }
            throw $e;
        }
    }
    
    /**
     * Eliminar un usuario por su ID
     * 
     * @param string $id ID del usuario a eliminar
     * @return bool True si se eliminó correctamente, false si no se encontró el usuario
     * @throws \Exception Si ocurre un error al intentar eliminar
     */
    public function delete(string $id): bool
    {
        try {
            // No necesitamos verificar si el usuario existe primero
            // porque Supabase devuelve éxito (204) incluso si el usuario no existe
            
            // Realizar la eliminación
            $response = $this->client->request(
                'rest/v1/users',
                'DELETE',
                null,
                'user_id=eq.' . $id . '&select=user_id'  // Solo pedimos el ID para minimizar la respuesta
            );
            
            // En Supabase, una eliminación exitosa devuelve 204 No Content
            // Incluso si el usuario no existía, devuelve 204
            // También verificamos si la respuesta es un array vacío (puede variar según la versión de Supabase)
            return $response['status'] === 204 || empty($response['data']);
            
        } catch (\Exception $e) {
            $errorMsg = 'Error al eliminar el usuario: ' . $e->getMessage();
            if (defined('APP_DEBUG') && APP_DEBUG) {
                error_log('Error in SupabaseUserRepository->delete(): ' . $errorMsg);
            }
            throw new \Exception($errorMsg, 0, $e);
        }
    }
    
    public function isEmailInUse(string $email, ?string $excludeUserId = null): bool
    {
        try {
            $query = 'user_email=eq.' . urlencode($email) . '&select=user_id';
            if ($excludeUserId) {
                $query .= '&user_id=neq.' . urlencode($excludeUserId);
            }
            
            $response = $this->client->request(
                'rest/v1/users',
                'GET',
                null,
                $query
            );
            
            // Verificar si hay algún usuario con ese email
            return !empty($response['data']);
            
        } catch (\Exception $e) {
            if (defined('APP_DEBUG') && APP_DEBUG) {
                error_log('Error in SupabaseUserRepository->isEmailInUse(): ' . $e->getMessage());
            }
            return false;
        }
    }
    
    public function isUserInUse(string $userName, ?string $excludeUserId = null): bool
    {
        try {
            $query = 'user_name=eq.' . urlencode($userName) . '&select=user_id';
            if ($excludeUserId) {
                $query .= '&user_id=neq.' . urlencode($excludeUserId);
            }
            
            $response = $this->client->request(
                'rest/v1/users',
                'GET',
                null,
                $query
            );
            
            // Verificar si hay algún usuario con ese ID
            return !empty($response['data']);
            
        } catch (\Exception $e) {
            if (defined('APP_DEBUG') && APP_DEBUG) {
                error_log('Error in SupabaseUserRepository->isUserInUse(): ' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Obtener todos los roles disponibles
     * 
     * @return array Array de objetos Role
     * @throws \Exception Si ocurre un error al obtener los roles
     */
    public function getAllRoles(): array
    {
        try {
            $response = $this->client->request(
                'rest/v1/roles',
                'GET',
                null,
                'select=*&order=role_name.asc'
            );
            
            if (empty($response['data'])) {
                return [];
            }
            
            return array_map(function($roleData) {
                $role = is_object($roleData) ? (array) $roleData : $roleData;
                
                // Asegurar que los campos requeridos estén presentes
                $roleData = [
                    'role_id' => $role['role_id'] ?? null,
                    'role_name' => $role['role_name'] ?? 'Sin nombre',
                    'created_at' => $role['created_at'] ?? null,
                    'updated_at' => $role['updated_at'] ?? null
                ];
                
                // Convertir a objeto Role y luego a array para mantener consistencia
                return Role::fromArray($roleData)->toArray();
                
            }, $response['data']);
            
        } catch (\Exception $e) {
            $errorMsg = 'Error al obtener los roles: ' . $e->getMessage();
            if (defined('APP_DEBUG') && APP_DEBUG) {
                error_log('Error in SupabaseUserRepository->getAllRoles(): ' . $errorMsg);
            }
            throw new \Exception($errorMsg, 0, $e);
        }
    }
}
