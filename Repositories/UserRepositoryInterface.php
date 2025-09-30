<?php

namespace Repositories;

use Models\User;

interface UserRepositoryInterface
{
    /**
     * Get all users
     * 
     * @return array Array of User objects
     */
    public function getAll(): array;
    
    /**
     * Find a user by ID
     * 
     * @param string $id
     * @return array|null User data or null if not found
     */
    public function findById(string $id): ?array;
    
    /**
     * Create a new user
     * 
     * @param array $data User data
     * @return array Created user data
     */
    public function create(array $data): array;
    
    /**
     * Update a user
     * 
     * @param string $id
     * @param array $data User data to update
     * @return array Updated user data
     */
    public function update(string $id, array $data): array;
    
    /**
     * Delete a user
     * 
     * @param string $id
     * @return bool True if successful
     */
    public function delete(string $id): bool;
    
    /**
     * Check if email is already in use
     * 
     * @param string $email
     * @param string|null $excludeUserId User ID to exclude from check (for updates)
     * @return bool
     */
    public function isEmailInUse(string $email, ?string $excludeUserId = null): bool;
    
    /**
     * Get all roles
     * 
     * @return array Array of Role objects
     */
    public function getAllRoles(): array;
}
