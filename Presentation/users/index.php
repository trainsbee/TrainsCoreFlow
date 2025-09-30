<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Usuarios</title>
</head>
<body>
    <main class="container">
        <div class="page-header">
            <h1 class="page-title">Lista de Usuarios</h1>
            <div>
                <a href="/supabase/users/create" role="button" class="primary">
                    Nuevo Usuario
                </a>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($usuarios)): ?>
            <div class="alert alert-info">
                No se encontraron usuarios registrados.
                <a href="/supabase/users/create">¿Desea crear un nuevo usuario?</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table role="grid">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Estado</th>
                            <th>Fecha de Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($usuarios as $user): ?>
                            <tr>
                                <td><code><?= htmlspecialchars(substr($user->getUserId() ?? '', 0, 8)) ?>...</code></td>
                                <td><?= htmlspecialchars($user->getUserName() ?? 'N/A') ?></td>
                                <td><a href="mailto:<?= htmlspecialchars($user->getUserEmail() ?? '') ?>"><?= htmlspecialchars($user->getUserEmail() ?? 'N/A') ?></a></td>
                                <td>
                                    <span class="<?= ($user->getUserStatus() ?? false) ? 'status-active' : 'status-inactive' ?>">
                                        <?= ($user->getUserStatus() ?? false) ? 'Activo' : 'Inactivo' ?>
                                    </span>
                                </td>
                                <td><?= !empty($user->getCreatedAt()) ? date('d/m/Y H:i', strtotime($user->getCreatedAt())) : 'N/A' ?></td>
                                <td class="actions">
                                    <a href="/supabase/users/<?= $user->getUserId() ?>/edit" role="button" class="secondary small">Editar</a>
                                    <button type="button" class="contrast small" style="margin: 0;" onclick="confirmDelete('<?= $user->getUserId() ?>', '<?= htmlspecialchars($user->getUserName()) ?>')">
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>

    <script>
        function confirmDelete(userId, userName) {
            if (confirm(`¿Está seguro de eliminar al usuario "${userName}"? Esta acción no se puede deshacer.`)) {
                deleteUser(userId);
            }
        }

        async function deleteUser(userId) {
            try {
                // Mostrar indicador de carga
                const deleteButton = event.target;
                const originalText = deleteButton.textContent;
                deleteButton.textContent = 'Eliminando...';
                deleteButton.disabled = true;

                const response = await fetch(`/supabase/users/${userId}/delete`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                
                const result = await response.json();
                
                if (response.ok) {
                    // Mostrar mensaje de éxito
                    showMessage('Usuario eliminado exitosamente', 'success');
                    
                    // Eliminar la fila de la tabla
                    const row = deleteButton.closest('tr');
                    row.style.transition = 'opacity 0.3s ease';
                    row.style.opacity = '0';
                    
                    // Remover la fila después de la animación
                    setTimeout(() => {
                        row.remove();
                        
                        // Verificar si quedan usuarios
                        const tbody = document.querySelector('tbody');
                        if (tbody.children.length === 0) {
                            tbody.innerHTML = `
                                <tr>
                                    <td colspan="6" style="text-align: center;">
                                        <div class="alert alert-info">
                                            No se encontraron usuarios registrados.
                                            <a href="/supabase/users/create">¿Desea crear un nuevo usuario?</a>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        }
                    }, 300);
                } else {
                    throw new Error(result.message || 'Error al eliminar el usuario');
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage(error.message || 'Error al procesar la solicitud', 'error');
                
                // Restaurar el botón
                if (deleteButton) {
                    deleteButton.textContent = originalText;
                    deleteButton.disabled = false;
                }
            }
        }
        
        function showMessage(message, type = 'info') {
            // Crear elemento de mensaje si no existe
            let messageDiv = document.getElementById('message-container');
            if (!messageDiv) {
                messageDiv = document.createElement('div');
                messageDiv.id = 'message-container';
                messageDiv.style.position = 'fixed';
                messageDiv.style.top = '20px';
                messageDiv.style.right = '20px';
                messageDiv.style.zIndex = '1000';
                document.body.appendChild(messageDiv);
            }
            
            // Crear el mensaje
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert';
            alertDiv.classList.add(type === 'error' ? 'alert-error' : 'alert-success');
            alertDiv.textContent = message;
            alertDiv.style.marginBottom = '10px';
            alertDiv.style.padding = '12px 20px';
            alertDiv.style.borderRadius = '4px';
            alertDiv.style.maxWidth = '300px';
            alertDiv.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
            
            // Agregar a la página
            messageDiv.appendChild(alertDiv);
            
            // Eliminar después de 3 segundos
            setTimeout(() => {
                alertDiv.style.transition = 'opacity 0.3s ease';
                alertDiv.style.opacity = '0';
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.parentNode.removeChild(alertDiv);
                    }
                }, 300);
            }, 3000);
        }
    </script>
</body>
</html>
