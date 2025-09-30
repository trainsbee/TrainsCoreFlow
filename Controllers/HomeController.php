<?php
namespace Controllers;

use Core\BaseController;

class HomeController extends BaseController {

    public function __construct($viewRenderer) {
        parent::__construct($viewRenderer);
    }

    public function index() {
        $this->renderView('home', [], [
            'title' => 'Inicio - Sistema de Usuarios',
            'description' => 'Bienvenido al sistema de gestión de usuarios con Supabase'
        ]);
    }
}
