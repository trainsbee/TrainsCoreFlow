<?php
/**
 * Autoloader PSR-4 optimizado
 */

$autoloadDirs = [
    'Core'        => __DIR__ . '/Core',
    'Models'      => __DIR__ . '/Models',
    'Repositories'=> __DIR__ . '/Repositories',
    'Services'    => __DIR__ . '/Services',
    'Controllers' => __DIR__ . '/Controllers'
];

$classMap = [];

/**
 * Construye un mapa de clases para cada directorio base
 */
function buildClassMap($baseDir, $namespace = '') {
    $map = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $relativePath = str_replace('\\', '/', substr($file->getPathname(), strlen($baseDir) + 1));
            $className = substr($relativePath, 0, -4); // quitar .php
            $fullClassName = $namespace ? $namespace . '\\' . str_replace('/', '\\', $className) : str_replace('/', '\\', $className);
            $map[$fullClassName] = $file->getPathname();
        }
    }

    return $map;
}

// Construir el mapa de clases solo una vez
foreach ($autoloadDirs as $namespace => $dir) {
    if (is_dir($dir)) {
        $classMap = array_merge($classMap, buildClassMap($dir, $namespace));
    }
}


// Registrar autoloader
spl_autoload_register(function($class) use ($classMap) {
    if (isset($classMap[$class])) {
        require $classMap[$class];
        return true;
    }
    return false;
});
?>