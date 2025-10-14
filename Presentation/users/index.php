<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Usuarios</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: #f4f4f4;
        }
        button {
            padding: 5px 10px;
            margin: 2px;
            cursor: pointer;
        }
        .sidebar {
            position: fixed;
            right: 0;
            top: 0;
            width: 400px;
            height: 100%;
            background: #fff;
            box-shadow: -2px 0 5px rgba(0,0,0,0.2);
            padding: 20px;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            z-index: 1000;
        }
        .sidebar.show {
            transform: translateX(0);
        }
        .sidebar h2 {
            margin-top: 0;
        }
        .sidebar label {
            display: block;
            margin-top: 10px;
        }
        .sidebar input, .sidebar select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
    <button id="openSidebarCreate">Crear Usuario</button>
    <h1>Lista de Usuarios</h1>
    <table id="usersTable">
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Email</th>
                <th>Estado</th>
                <th>Role</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <!-- Usuarios se cargarán aquí con JS -->
        </tbody>
    </table>

    <!-- Sidebar de edición -->
    <div id="sidebar" class="sidebar">
        <button id="closeSidebar">Cerrar</button>
        <h2>Editar Usuario</h2>
        <form id="editUserForm" data-id="" class="form-data" data-method="PUT" data-destination="users.update" calling-method="update" data-type="json">
            <input type="hidden" name="user_id" id="editUserId">
            <label>Nombre</label>
            <input type="text" name="user_name" id="editUserName">
            <label>Email</label>
            <input type="email" name="user_email" id="editUserEmail">
            <label>Estado</label>
            <select name="user_status" id="editUserStatus">
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
            </select>
            <div>
                <label for="edit_role_id">
                    Rol
                    <select id="edit_role_id" name="edit_role_id">
                        <option value="">Seleccione un rol</option>
                       
                    </select>
                </label>
            </div>
            <button type="submit">Guardar Cambios</button>
        </form>
    </div>
    
    <div id="sidebar-create" class="sidebar">
        <button id="closeSidebarCreate">Cerrar</button>
        <h2>Crear Nuevo Usuario</h2>

    <form data-method="POST" class="form-data" data-destination="users.store" calling-method="store" data-type="json">
            <div id="form-message" class="alert"></div>
            
            <div class="grid">
                <div>
                    <label for="user_name">
                        Nombre usuario
                        <input type="text" id="user_name" name="user_name">
                    </label>
                </div>
                <div>
                    <label for="user_email">
                        Correo electrónico
                        <input type="email" id="user_email" name="user_email">
                    </label>
                </div>
            </div>

            <div class="grid">
                <div>
                    <label for="user_password">
                        Contraseña  
                        <input type="password" id="user_password" name="user_password">
                    </label>
                </div>
                <div>
                    <label for="confirm_password">
                        Confirmar contraseña    
                        <input type="password" id="confirm_password" name="confirm_password">
                    </label>
                </div>
            </div>
            <div>
                <label for="user_status">
                    Estado
                    <select id="user_status" name="user_status">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </label>
            </div>
            <div>
                <label for="create_role_id">
                    Rol
                    <select id="create_role_id" name="create_role_id">
                        <option value="">Seleccione un rol</option>
                       
                    </select>
                </label>
            </div>

            <div class="form-actions">
                <a href="/supabase/users" role="button" class="secondary" type="button">Cancelar</a>
                <button type="submit" class="primary">
                    <span class="button-text">Guardar Usuario</span>
                </button>
            </div>
        </form>
    </div>
  

<script>
    let rolesMap = {};
    </script>
    <script type="module">
       import { CustomFetch } from './Assets/js/helpers/customFetch.js';
       import { routes } from './Assets/js/helpers/routes.js';

     
      const sidebarCreate = document.getElementById('sidebar-create');
        const closeSidebarCreateBtn = document.getElementById('closeSidebarCreate');
        const openSidebarCreateBtn = document.getElementById('openSidebarCreate');
        
        // Cerrar sidebar de creación
        closeSidebarCreateBtn.addEventListener('click', () => sidebarCreate.classList.remove('show'));

        // Abrir sidebar de creación
        openSidebarCreateBtn.addEventListener('click', () => sidebarCreate.classList.add('show'));

        // OBTENER LA LISTA D EROLES PARA EL SELECT UAR FETCH

        async function getRoles() {
            const customFetch = new CustomFetch();
            const { data } = await customFetch.get(routes.users.getAllRoles());

            const createSelect = document.getElementById('create_role_id');
            const editSelect = document.getElementById('edit_role_id');

            data.forEach(role => {
            rolesMap[role.role_id] = role.role_name;
            const option1 = document.createElement('option');
            option1.value = role.role_id;
            option1.textContent = role.role_name;
            createSelect.appendChild(option1);

            const option2 = document.createElement('option');
            option2.value = role.role_id;
            option2.textContent = role.role_name;
            editSelect.appendChild(option2);
    });
}


      getRoles();

        const customFetch = new CustomFetch();
        const sidebar = document.getElementById('sidebar');
        const closeSidebarBtn = document.getElementById('closeSidebar');
        const editForm = document.getElementById('editUserForm');

        // Cerrar sidebar
        closeSidebarBtn.addEventListener('click', () => sidebar.classList.remove('show'));

        // Abrir sidebar y rellenar form con datos del usuario
        function openSidebar(user) {
            sidebar.classList.add('show');
            document.getElementById('editUserId').value = user.user_id;
            document.getElementById('editUserName').value = user.user_name;
            document.getElementById('editUserEmail').value = user.user_email;
            document.getElementById('editUserStatus').value = user.user_status ? '1' : '0';
            // Actualizar el atributo data-id del formulario
            editForm.setAttribute('data-id', user.user_id);
            document.getElementById('edit_role_id').value = user.role_id;
        }

        // Renderizar tabla de usuarios
        function renderUsers(users) {
            const tableBody = document.querySelector("#usersTable tbody");
            tableBody.innerHTML = '';

            users.forEach(user => {
                const tr = document.createElement('tr');
                tr.id = user.user_id;
                tr.innerHTML = `
                    <td>${user.user_name}</td>
                    <td>${user.user_email}</td>
                    <td>${user.user_status ? 'Activo' : 'Inactivo'}</td>
                    <td>${user.role_name}</td>
                    <td>
                        <button class="edit-btn">Editar</button>
                        <button class="delete-btn">Eliminar</button>
                    </td>
                `;
                tableBody.appendChild(tr);

                // Editar
                tr.querySelector('.edit-btn').addEventListener('click', async () => {
                    try {
                        // 1️⃣ Obtener los datos actualizados del usuario desde el backend
                        const { data: users } = await customFetch.get(routes.users.getOne(user.user_id));

                        // 2️⃣ Pasar esos datos a la función que abre el sidebar
                        openSidebar(users);
                    } catch (error) {
                        console.error("No se pudo obtener el usuario:", error);
                    }
                });

                // Eliminar
                tr.querySelector('.delete-btn').addEventListener('click', async () => {
                    if (!confirm(`Eliminar usuario ${user.user_name}?`)) return;
                    const res = await customFetch.delete(routes.users.delete(user.user_id));
                    if (res.status === 'USER_DELETED') {
                        removeRow(res.user_id);
                    }

                });
            });
        }

    
       function removeRow(userId) {
           const row = document.querySelector(`tr[id="${userId}"]`);
           if (row) {
               row.remove();
           }
       }



let currentPage = 1;
let totalPages = 1;
let perPage = 7; // Puedes cambiar esto si quieres

async function getPaginatedUsers(page = 1) {
    try {
        const { data, pagination } = await customFetch.get(routes.users.getByPage(page, perPage));

        // Aquí puedes llamar a tu función que renderiza la tabla
        renderUsers(data);

        currentPage = pagination.currentPage;
        totalPages = pagination.totalPages;

        document.getElementById('currentPage').textContent = currentPage;

        document.getElementById('prevPage').disabled = currentPage === 1;
        document.getElementById('nextPage').disabled = currentPage === totalPages;

    } catch (error) {
        console.error('Error al obtener usuarios paginados:', error);
    }
}

document.getElementById('prevPage').addEventListener('click', () => {
    if (currentPage > 1) getPaginatedUsers(currentPage - 1);
});

document.getElementById('nextPage').addEventListener('click', () => {
    if (currentPage < totalPages) getPaginatedUsers(currentPage + 1);
});

// Cargar la primera página al inicio
getPaginatedUsers(currentPage);

    </script>

    <div class="pagination">
        <button id="prevPage">Anterior</button>
        <span id="currentPage">1</span>
        <button id="nextPage">Siguiente</button>
    </div>
    <?php include __DIR__ . '/../Template/footer_admin.php'; ?>
</body>
</html>