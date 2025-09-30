<?php
namespace Models;

class Role {
    private $role_id;
    private $role_name;

    /**
     * Constructor de la clase Role
     * 
     * @param array $data Datos del rol
     */
    public function __construct(array $data = []) {
        $this->role_id = $data['role_id'] ?? null;
        $this->role_name = $data['role_name'] ?? null;
    }

    /**
     * Convierte el objeto a un array para enviar a la API
     * 
     * @return array
     */
    public function toArray(): array {
        $data = [
            'role_name' => $this->role_name
        ];

        // Agregar role_id solo si existe
        if ($this->role_id) {
            $data['role_id'] = $this->role_id;
        }

        return $data;
    }

    /**
     * Crea una instancia de Role desde un array
     * 
     * @param array $data
     * @return Role
     */
    public static function fromArray(array $data): Role {
        return new self($data);
    }

    // Getters y setters
    public function getRoleId() {
        return $this->role_id;
    }

    public function getRoleName() {
        return $this->role_name;
    }

    public function setRoleName($role_name) {
        $this->role_name = $role_name;
        return $this;
    }
}