<?php

namespace Core;

/**
 * Clase para renderizar vistas de manera elegante y mantenible
 */
class ViewRenderer {
    private $viewsPath;
    private $layoutPath;
    private $data = [];
    
    /**
     * Constructor
     * 
     * @param string $viewsPath Ruta base de las vistas
     * @param string $layoutPath Ruta del layout principal (opcional)
     */
    public function __construct($viewsPath = null, $layoutPath = null) {
        $this->viewsPath = $viewsPath ?: __DIR__ . '/../Presentation';
        $this->layoutPath = $layoutPath ?: $this->viewsPath . '/layout.php';
    }
    
    /**
     * Renderiza una vista con datos
     * 
     * @param string $view Nombre de la vista (sin extensión)
     * @param array $data Datos para pasar a la vista
     * @param bool $useLayout Si se debe usar el layout principal
     * @return string HTML renderizado
     */
    public function render($view, $data = [], $useLayout = true) {
        // Extraer datos para que estén disponibles como variables en la vista
        $this->data = $data;
        extract($data);
        
        // Iniciar buffer de salida
        ob_start();
        
        if ($useLayout && file_exists($this->layoutPath)) {
            // Renderizar con layout
            $content = $this->renderPartial($view, $data, false);
            include $this->layoutPath;
        } else {
            // Renderizar vista directamente
            $viewFile = $this->getViewFile($view);
            if (file_exists($viewFile)) {
                include $viewFile;
            } else {
                throw new \Exception("Vista no encontrada: {$viewFile}");
            }
        }
        
        // Obtener contenido del buffer y limpiar
        $content = ob_get_clean();
        return $content;
    }
    
    /**
     * Renderiza una vista parcial (sin layout)
     * 
     * @param string $view Nombre de la vista
     * @param array $data Datos para pasar a la vista
     * @param bool $return Si se debe retornar o imprimir
     * @return string|null HTML renderizado
     */
    public function renderPartial($view, $data = [], $return = true) {
        extract($data);
        
        ob_start();
        $viewFile = $this->getViewFile($view);
        
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new \Exception("Vista parcial no encontrada: {$viewFile}");
        }
        
        $content = ob_get_clean();
        
        if ($return) {
            return $content;
        } else {
            echo $content;
            return null;
        }
    }
    
    /**
     * Renderiza y envía la vista al navegador
     * 
     * @param string $view Nombre de la vista
     * @param array $data Datos para pasar a la vista
     * @param bool $useLayout Si se debe usar el layout principal
     */
    public function renderAndSend($view, $data = [], $useLayout = true) {
        $content = $this->render($view, $data, $useLayout);
        echo $content;
    }
    
    /**
     * Obtiene la ruta completa del archivo de vista
     * 
     * @param string $view Nombre de la vista
     * @return string Ruta completa del archivo
     */
    private function getViewFile($view) {
        // Convertir notación de puntos a barras (ej: 'users.index' -> 'users/index')
        $viewPath = str_replace('.', '/', $view);
        $viewFile = $this->viewsPath . '/' . $viewPath . '.php';
        
        return $viewFile;
    }
    
    /**
     * Establece datos globales para todas las vistas
     * 
     * @param string $key Clave del dato
     * @param mixed $value Valor del dato
     */
    public function setGlobal($key, $value) {
        $this->data[$key] = $value;
    }
    
    /**
     * Obtiene datos globales
     * 
     * @param string $key Clave del dato
     * @return mixed Valor del dato
     */
    public function getGlobal($key) {
        return $this->data[$key] ?? null;
    }
    
    /**
     * Renderiza una vista JSON (para respuestas AJAX)
     * 
     * @param array $data Datos para convertir a JSON
     * @param int $status Código HTTP
     */
    public function renderJson($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
    
    /**
     * Renderiza una vista de error
     * 
     * @param string $message Mensaje de error
     * @param int $status Código HTTP
     * @param string $view Vista de error personalizada
     */
    public function renderError($message, $status = 404, $view = 'errors.404') {
        http_response_code($status);
        $this->renderAndSend($view, [
            'message' => $message,
            'status' => $status
        ]);
        exit();
    }
}
