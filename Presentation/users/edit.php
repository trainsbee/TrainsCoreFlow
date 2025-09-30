<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
</head>
<body>
    <main>
        <h1>Editar Usuario</h1>
        <a href="/supabase/users">Volver a la lista</a>
        
        <div id="form-message"></div>
        
        <form id="userForm">
            <input type="hidden" id="user_id" name="user_id" value="<?= htmlspecialchars($user['user_id'] ?? '') ?>">
            
            <div>
                <label for="user_name">
                    Nombre Completo
                    <input type="text" id="user_name" name="user_name" value="<?= htmlspecialchars($user['user_name'] ?? '') ?>" required>
                </label>
            </div>
            
            <div>
                <label for="user_email">
                    Correo Electrónico
                    <input type="email" id="user_email" name="user_email" value="<?= htmlspecialchars($user['user_email'] ?? '') ?>" required>
                </label>
            </div>
            
            <div>
                <label for="user_status">
                    Estado
                    <select id="user_status" name="user_status" required>
                        <option value="1" <?= (($user['user_status'] ?? false) ? 'selected' : '') ?>>Activo</option>
                        <option value="0" <?= (!($user['user_status'] ?? true) ? 'selected' : '') ?>>Inactivo</option>
                    </select>
                </label>
            </div>
            
            <div>
                <label for="role_id">
                    Rol
                    <select id="role_id" name="role_id" required>
                        <?php if (!empty($roles)): ?>
                            <?php 
                            $userRoleId = $user['role_id'] ?? '';
                            foreach ($roles as $role): 
                                $roleId = $role['role_id'] ?? '';
                                $roleName = $role['role_name'] ?? 'Sin nombre';
                                $isSelected = ($userRoleId == $roleId);
                            ?>
                                <option value="<?= htmlspecialchars($roleId) ?>" 
                                    <?= $isSelected ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($roleName) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">No hay roles disponibles</option>
                        <?php endif; ?>
                    </select>
                </label>
                <!-- Debug info -->
                <?php if (defined('APP_DEBUG') && APP_DEBUG): ?>
                <div style="margin-top: 20px; padding: 10px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 4px;">
                    <p><strong>Debug Info:</strong></p>
                    <p>Roles disponibles: <?= count($roles) ?></p>
                    <p>Usuario ID: <?= htmlspecialchars($user['user_id'] ?? 'N/A') ?></p>
                    <p>Rol seleccionado: <?= htmlspecialchars($user['role_id'] ?? 'Ninguno') ?></p>
                    <?php if (!empty($roles)): ?>
                        <ul>
                            <?php foreach ($roles as $role): ?>
                                <?php 
                                $roleId = $role['role_id'] ?? 'N/A';
                                $roleName = $role['role_name'] ?? 'Sin nombre';
                                ?>
                                <li>Role: <?= htmlspecialchars($roleName) ?> (ID: <?= htmlspecialchars($roleId) ?>)</li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <div>
                <button type="submit">
                    <span class="button-text">Actualizar Usuario</span>
                    <span class="spinner" style="display:none;">⏳</span>
                </button>
                <a href="/supabase/users">
                    <button type="button">Cancelar</button>
                </a>
            </div>
        </form>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('userForm');
            const submitButton = form.querySelector('button[type="submit"]');
            const buttonText = submitButton.querySelector('.button-text');
            const spinner = submitButton.querySelector('.spinner');
            const messageDiv = document.getElementById('form-message');
            
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // Recolectar datos del formulario
                const formData = {
                    user_id: document.getElementById('user_id').value,
                    user_name: document.getElementById('user_name').value,
                    user_email: document.getElementById('user_email').value,
                    user_status: document.getElementById('user_status').value,
                    role_id: document.getElementById('role_id').value
                };
                
                // Mostrar estado de carga
                submitButton.disabled = true;
                buttonText.style.display = 'none';
                spinner.style.display = 'inline';
                messageDiv.textContent = '';
                
                try {
                    const response = await fetch(`/supabase/users/${formData.user_id}/update`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(formData)
                    });
                    
                    const result = await response.json();
                    
                    if (response.ok) {
                        showMessage('Usuario actualizado exitosamente', 'success');
                        // Redirigir después de 1.5 segundos
                        setTimeout(() => {
                            window.location.href = '/supabase/users';
                        }, 1500);
                    } else {
                        throw new Error(result.message || 'Error al actualizar el usuario');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showMessage(error.message || 'Error al procesar la solicitud', 'error');
                } finally {
                    // Restaurar botón
                    submitButton.disabled = false;
                    buttonText.style.display = 'inline';
                    spinner.style.display = 'none';
                }
            });
            
            function showMessage(message, type = 'info') {
                messageDiv.textContent = message;
                messageDiv.className = 'message ' + type;
                
                // Desplazarse al mensaje
                messageDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    </script>
</body>
</html>
