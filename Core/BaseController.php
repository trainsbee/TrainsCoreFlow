<?php

namespace Core;

/**
 * Clase base para todos los controladores
 * Proporciona funcionalidades comunes como renderizado de vistas
 */
class BaseController {
    protected $viewRenderer;
    protected $service;
    
    /**
     * Constructor
     * 
     * @param ViewRenderer $viewRenderer Instancia del renderizador de vistas
     * @param mixed $service Servicio principal del controlador (opcional)
     */
    public function __construct(ViewRenderer $viewRenderer, $service = null) {
        $this->viewRenderer = $viewRenderer;
        $this->service = $service;
    }
    
    /**
     * Renderiza una vista completa con layout
     * 
     * @param string $view Nombre de la vista
     * @param array $data Datos para pasar a la vista
     * @param array $metaData Metadatos como título, descripción, etc.
     */
    protected function renderView($view, $data = [], $metaData = []) {
        // Combinar datos con metadatos
        $viewData = array_merge($data, $metaData);
        
        // Establecer valores por defecto
        $viewData['title'] = $metaData['title'] ?? 'Sistema de Usuarios';
        $viewData['description'] = $metaData['description'] ?? 'Sistema de gestión de usuarios con Supabase';
        
        $this->viewRenderer->renderAndSend($view, $viewData);
    }
    
    /**
     * Renderiza una vista parcial (sin layout)
     * 
     * @param string $view Nombre de la vista
     * @param array $data Datos para pasar a la vista
     */
    protected function renderPartial($view, $data = []) {
        $this->viewRenderer->renderPartial($view, $data, false);
    }
    
    /**
     * Renderiza una respuesta JSON
     * 
     * @param array $data Datos para convertir a JSON
     * @param int $status Código HTTP
     */
    protected function renderJson($data, $status = 200) {
        $this->viewRenderer->renderJson($data, $status);
    }
    
    /**
     * Renderiza una página de error
     * 
     * @param string $message Mensaje de error
     * @param int $status Código HTTP
     * @param string $view Vista de error personalizada
     */
    protected function renderError($message, $status = 404, $view = 'errors.404') {
        $this->viewRenderer->renderError($message, $status, $view);
    }
    
    /**
     * Establece un mensaje flash en sesión
     * 
     * @param string $message Mensaje a mostrar
     * @param string $type Tipo de mensaje (success, error, info, warning)
     */
    protected function setFlashMessage($message, $type = 'success') {
        $_SESSION["flash_{$type}"] = $message;
    }
    
    /**
     * Redirige a una URL
     * 
     * @param string $url URL de destino
     * @param int $status Código HTTP de redirección
     */
    protected function redirect($url, $status = 302) {
        header("Location: {$url}", true, $status);
        exit();
    }
    
    /**
     * Redirige con mensaje flash
     * 
     * @param string $url URL de destino
     * @param string $message Mensaje a mostrar
     * @param string $type Tipo de mensaje (success, error, info, warning)
     * @param int $status Código HTTP de redirección
     */
    protected function redirectWithMessage($url, $message, $type = 'success', $status = 302) {
        $this->setFlashMessage($message, $type);
        $this->redirect($url, $status);
    }
    
    /**
     * Obtiene datos de la petición actual
     * 
     * @param string $method Método HTTP (GET, POST, etc.)
     * @return array Datos de la petición
     */
    protected function getRequestData($method = 'POST') {
        switch ($method) {
            case 'POST':
                return $_POST;
            case 'GET':
                return $_GET;
            case 'JSON':
                $json = file_get_contents('php://input');
                return json_decode($json, true) ?: [];
            default:
                return $_REQUEST;
        }
    }
    
    /**
     * Valida datos de entrada
     * 
     * @param array $data Datos a validar
     * @param array $rules Reglas de validación
     * @return array Errores de validación
     */
    protected function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $fieldRules) {
            foreach ($fieldRules as $rule) {
                if (strpos($rule, ':') !== false) {
                    [$ruleName, $ruleValue] = explode(':', $rule);
                } else {
                    $ruleName = $rule;
                    $ruleValue = null;
                }
                
                $value = $data[$field] ?? null;
                
                switch ($ruleName) {
                    case 'required':
                        if (empty($value)) {
                            $errors[$field][] = "El campo {$field} es requerido";
                        }
                        break;
                        
                    case 'email':
                        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field][] = "El campo {$field} debe ser un email válido";
                        }
                        break;
                        
                    case 'min':
                        if (!empty($value) && strlen($value) < $ruleValue) {
                            $errors[$field][] = "El campo {$field} debe tener al menos {$ruleValue} caracteres";
                        }
                        break;
                        
                    case 'max':
                        if (!empty($value) && strlen($value) > $ruleValue) {
                            $errors[$field][] = "El campo {$field} no debe exceder {$ruleValue} caracteres";
                        }
                        break;
                        
                    case 'confirmed':
                        $confirmField = $field . '_confirm';
                        if (!empty($value) && $value !== ($data[$confirmField] ?? null)) {
                            $errors[$field][] = "El campo {$field} no coincide con la confirmación";
                        }
                        break;
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Verifica si la petición es AJAX
     * 
     * @return bool
     */
    protected function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Verifica si la petición es de tipo POST
     * 
     * @return bool
     */
    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    /**
     * Verifica si la petición es de tipo GET
     * 
     * @return bool
     */
    protected function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
}
