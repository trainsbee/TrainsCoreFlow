<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Sistema de Usuarios' ?></title>
    <meta name="description" content="<?= $description ?? 'Sistema de gestión de usuarios con Supabase' ?>">
</head>
<body>
    <!-- Navegación -->
    <nav>
        <div>
            <a href="/supabase">Sistema de Usuarios</a>
            <ul>
                <li><a href="/supabase">Inicio</a></li>
                <li><a href="/supabase/users">Usuarios</a></li>
                <li><a href="/supabase/users/create">Nuevo Usuario</a></li>
            </ul>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <main>
        <!-- Mensajes Flash -->
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div>
                <?= htmlspecialchars($_SESSION['flash_success']) ?>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div>
                <?= htmlspecialchars($_SESSION['flash_error']) ?>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <!-- Contenido de la Vista -->
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer>
        <div>
            <div>
                <p>&copy; <?= date('Y') ?> Sistema de Usuarios con Supabase. Desarrollado con PHP y Clean Architecture.</p>
                <p>
                    <a href="/supabase/users">Gestión de Usuarios</a>
                    |
                    <a href="/supabase/users/create">Crear Usuario</a>
                </p>
            </div>
        </div>
    </footer>
</body>
</html>
