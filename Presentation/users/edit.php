<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
</head>
<body>
<?php include __DIR__ . '/../Template/header_admin.php'; ?>
<?php include __DIR__ . '/../Template/admin_nav.php'; ?> 

    <main>
        <h1>Editar Usuario</h1>
        <a href="/trainscoreflow/users">Volver a la lista</a>
        
        <div id="form-message"></div>
        
        <form data-method="PUT" class="form-data" data-destination="users.update" calling-method="update" data-type="json" data-id="<?= htmlspecialchars($user['user_id'] ?? '') ?>">
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
    <?php include __DIR__ . '/../Template/footer_admin.php'; ?>

</body>
</html>
