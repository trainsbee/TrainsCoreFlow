<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Nuevo Usuario</title>
</head>
<body>
    <main>
        <h1>Crear Nuevo Usuario</h1>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form id="userForm" method="post" enctype="application/x-www-form-urlencoded">
            <div id="form-message" class="alert"></div>
            
            <div class="grid">
                <div>
                    <label for="user_name">
                        Nombre Completo
                        <input type="text" id="user_name" name="user_name" required>
                    </label>
                </div>
                <div>
                    <label for="user_email">
                        Correo Electrónico
                        <input type="email" id="user_email" name="user_email" required>
                    </label>
                </div>
            </div>

            <div class="grid">
                <div>
                    <label for="user_password">
                        Contraseña
                        <input type="password" id="user_password" name="user_password" required>
                    </label>
                </div>
                <div>
                    <label for="confirm_password">
                        Confirmar Contraseña
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </label>
                </div>
            </div>

            <div>
                <label for="role_id">
                    Rol
                    <select id="role_id" name="role_id" required>
                        <option value="">Seleccione un rol</option>
                        <?php if (!empty($roles)): ?>
                            <?php foreach ($roles as $role): ?>
                                <?php 
                                $roleId = is_array($role) ? ($role['role_id'] ?? '') : '';
                                $roleName = is_array($role) ? ($role['role_name'] ?? 'Sin nombre') : '';
                                ?>
                                <option value="<?= htmlspecialchars($roleId) ?>">
                                    <?= htmlspecialchars($roleName) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">No hay roles disponibles</option>
                        <?php endif; ?>
                    </select>
                </label>
            </div>

            <div class="form-actions">
                <a href="/supabase/users" role="button" class="secondary" type="button">Cancelar</a>
                <button type="submit" class="primary">
                    <span class="button-text">Guardar Usuario</span>
                    <span class="spinner">Guardando...</span>
                </button>
            </div>
        </form>
    </main>

    <script>
        document.getElementById('userForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const form = e.target;
            const submitButton = form.querySelector('button[type="submit"]');
            const buttonText = submitButton.querySelector('.button-text');
            const spinner = submitButton.querySelector('.spinner');
            const messageDiv = document.getElementById('form-message');
            
            // Validar contraseñas
            const password = document.getElementById('user_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                showMessage('Las contraseñas no coinciden', 'error');
                return;
            }
            
            // Crear objeto con los datos del formulario
            const formData = new URLSearchParams();
            formData.append('user_name', document.getElementById('user_name').value);
            formData.append('user_email', document.getElementById('user_email').value);
            formData.append('user_password', password);
            formData.append('confirm_password', confirmPassword);
            formData.append('role_id', document.getElementById('role_id').value);
            formData.append('user_status', '1');
            
            // Mostrar estado de carga
            submitButton.disabled = true;
            buttonText.classList.add('hidden');
            spinner.classList.remove('hidden');
            messageDiv.classList.add('hidden');
            
            try {
                const response = await fetch('/supabase/users/store', {
                    method: 'POST',
                    body: formData.toString(),
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'Accept': 'application/json'
                    }
                });
                
                const result = await response.json();
                
                if (response.ok) {
                    showMessage('Usuario creado exitosamente', 'success');
                    // Redirigir después de 1.5 segundos
                    setTimeout(() => {
                        window.location.href = '/supabase/users';
                    }, 1500);
                } else {
                    throw new Error(result.message || 'Error al crear el usuario');
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage(error.message || 'Error al procesar la solicitud', 'error');
            } finally {
                // Restaurar botón
                submitButton.disabled = false;
                buttonText.classList.remove('hidden');
                spinner.classList.add('hidden');
            }
        });
        
        function showMessage(message, type = 'info') {
            const messageDiv = document.getElementById('form-message');
            messageDiv.textContent = message;
            messageDiv.className = 'alert';
            messageDiv.classList.add(type === 'error' ? 'alert-error' : 'alert-success', 'visible');
            
            // Desplazarse al mensaje
            messageDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    </script>
</body>
</html>
