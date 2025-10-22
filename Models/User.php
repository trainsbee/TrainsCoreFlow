<?php
namespace Models;

class User {
    public ?string $user_id;
    public ?string $user_email;
    public ?string $user_name;
    public ?string $user_password;
    public ?bool $user_status;
    public ?string $role_id;
    public ?string $role_name;
    public ?string $created_at;
    public ?string $updated_at;
    /**
     * Constructor de la clase User
     * 
     * @param array $data Datos del usuario
     */
    public function __construct(array $data = []) {
        $this->user_id = $data['user_id'] ?? null;
        $this->user_email = $data['user_email'] ?? null;
        $this->user_name = $data['user_name'] ?? null;
        $this->user_password = $data['user_password'] ?? null;
        $this->user_status = $data['user_status'] ?? true;
        $this->role_id = $data['role_id'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;

        $this->role_name = $data['role_name'] ?? null;


        // ✅ Asignar role_name si viene en la relación de Supabase
        if (isset($data['roles']) && is_array($data['roles'])) {
            $this->role_name = $data['roles']['role_name'] ?? null;
        } elseif (isset($data['role_name'])) {
            $this->role_name = $data['role_name'];
        } else {
            $this->role_name = null;
        }

    }


    /**
     * Convierte el objeto a un array para enviar a la API
     * 
     * @return array
     */
  public function toArray(): array {
    $data = [
        'user_email' => $this->user_email,
        'user_name' => $this->user_name,
        'user_status' => $this->user_status
    ];

    if ($this->role_id !== null) {
        $data['role_id'] = $this->role_id;
    }

    if (property_exists($this, 'role_name') && $this->role_name !== null) {
        $data['role_name'] = $this->role_name;
    }

    if ($this->user_id) {
        $data['user_id'] = $this->user_id;
    }

    if ($this->user_password) {
        $data['user_password'] = $this->user_password;
    }

    // ✅ Agregar created_at y updated_at
    if ($this->created_at) {
        $data['created_at'] = $this->created_at;
    }
    if ($this->updated_at) {
        $data['updated_at'] = $this->updated_at;
    }

    return $data;
}


    /**
     * Crea una instancia de User desde un array
     * 
     * @param array $data
     * @return User
     */
    public static function fromArray(array $data): User {
        return new self($data);
    }

    // Getters y setters
    public function getUserId() {
        return $this->user_id;
    }

    public function getUserEmail() {
        return $this->user_email;
    }

    public function setUserEmail($user_email) {
        $this->user_email = $user_email;
        return $this;
    }

    public function getUserName() {
        return $this->user_name;
    }

    public function setUserName($user_name) {
        $this->user_name = $user_name;
        return $this;
    }

    public function getUserPassword() {
        return $this->user_password;
    }

    public function setUserPassword($user_password) {
        $this->user_password = $user_password;
        return $this;
    }

    public function getUserStatus() {
        return $this->user_status;
    }

    public function setUserStatus($user_status) {
        $this->user_status = $user_status;
        return $this;
    }

    public function getRoleId() {
        return $this->role_id;
    }

    public function setRoleId($role_id) {
        $this->role_id = $role_id;
        return $this;
    }

    public function getCreatedAt() {
        return $this->created_at;
    }
}