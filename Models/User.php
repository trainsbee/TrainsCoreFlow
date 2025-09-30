<?php
namespace Models;

class User {
    private $user_id;
    private $user_email;
    private $user_name;
    private $user_password;
    private $user_status;
    private $role_id;
    private $created_at;

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
        
        // Asegurarnos de que role_id se asigne correctamente
        if (isset($data['role_id'])) {
            $this->role_id = $data['role_id'];
        } else {
            $this->role_id = null;
        }
        
        $this->created_at = $data['created_at'] ?? null;
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
        
        // Agregar role_id solo si existe y no es nulo
        if ($this->role_id !== null) {
            $data['role_id'] = $this->role_id;
        }

        // Agregar user_id solo si existe
        if ($this->user_id) {
            $data['user_id'] = $this->user_id;
        }

        // Agregar password solo si existe
        if ($this->user_password) {
            $data['user_password'] = $this->user_password;
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