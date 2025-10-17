import { CustomFetch } from '../helpers/customFetch.js';
import { routes } from '../helpers/routes.js';

export function removeRow(userId) {
    const row = document.querySelector(`tr[id="${userId}"]`);
    if (row) {
        row.remove();
    }
}

export async function httpGetRoles() { 
    const customFetch = new CustomFetch();
    const response = await customFetch.get(routes.users.getAllRoles());
    return response;
}

export async function httpGetPaginatedUsers(page, perPage, startDate, endDate) {
    const customFetch = new CustomFetch();
    const url = routes.users.getByPage(page, perPage, startDate, endDate);
    const { data, pagination } = await customFetch.get(url);
    return { data, pagination };
}

export async function httpGetUser(userId) {
    const customFetch = new CustomFetch();
    const { data: users } = await customFetch.get(routes.users.getOne(userId));
    return users;
}

export async function httpDeleteUser(userId) {
    const customFetch = new CustomFetch();
    const res = await customFetch.delete(routes.users.delete(userId));
    return res;
}

export async function httpOpenSidebar(userId) {
    const users = await httpGetUser(userId);
    const sidebar = document.getElementById('sidebar');
    const editForm = document.getElementById('editUserForm');
    sidebar.classList.add('show');
    document.getElementById('editUserId').value = users.user_id;
    document.getElementById('editUserName').value = users.user_name;
    document.getElementById('editUserEmail').value = users.user_email;
    document.getElementById('editUserStatus').value = users.user_status ? '1' : '0';
    editForm.setAttribute('data-id', users.user_id);
    document.getElementById('edit_role_id').value = users.role_id;
}

