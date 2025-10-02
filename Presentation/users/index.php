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
    <h1>Lista de Usuarios</h1>
    <table id="usersTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Estado</th>
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
        <form id="editUserForm" class="form-data" data-method="PUT" data-destination="users.update" calling-method="update" data-type="json">
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
            <button type="submit">Guardar Cambios</button>
        </form>
    </div>

    <script type="module">
        import { CustomFetch } from './Assets/js/helpers/customFetch.js';
        import { routes } from './Assets/js/helpers/routes.js';

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
        }

        // Renderizar tabla de usuarios
        function renderUsers(users) {
            const tableBody = document.querySelector("#usersTable tbody");
            tableBody.innerHTML = '';

            users.forEach(user => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${user.user_id.substring(0, 8)}...</td>
                    <td>${user.user_name}</td>
                    <td>${user.user_email}</td>
                    <td>${user.user_status ? 'Activo' : 'Inactivo'}</td>
                    <td>
                        <button class="edit-btn">Editar</button>
                        <button class="delete-btn">Eliminar</button>
                    </td>
                `;
                tableBody.appendChild(tr);

                // Editar
                tr.querySelector('.edit-btn').addEventListener('click', () => openSidebar(user));

                // Eliminar
                tr.querySelector('.delete-btn').addEventListener('click', async () => {
                    if (!confirm(`Eliminar usuario ${user.user_name}?`)) return;
                    const res = await customFetch.post(routes.users.delete(user.user_id));
                    if (res.success) getUsers();
                });
            });
        }

        // Obtener usuarios desde la API
        async function getUsers() {
            try {
                const { data } = await customFetch.get(routes.users.getAll());
                renderUsers(data || []);
            } catch (error) {
                console.error('Error al obtener usuarios:', error);
            }
        }

        // Manejar submit del formulario de edición
        editForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const userId = document.getElementById('editUserId').value;
            const userName = document.getElementById('editUserName').value;
            const userEmail = document.getElementById('editUserEmail').value;
            const userStatus = document.getElementById('editUserStatus').value === '1';

            const payload = { user_id: userId, user_name: userName, user_email: userEmail, user_status: userStatus };

            try {
                const res = await customFetch.put(routes.users.update(userId), {
                    body: payload,
                    headers: { 'Content-Type': 'application/json' }
                });
                if (res.success) {
                    sidebar.classList.remove('show');
                    getUsers(); // refrescar tabla
                }
            } catch (error) {
                console.error('Error al actualizar usuario:', error);
            }
        });

        // Ejecutar al cargar la página
        getUsers();
    </script>
</body>
</html>
