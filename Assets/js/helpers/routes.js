// Configuración base de la API
const API_BASE = 'http://localhost/trainscoreflow/';

// Definición de rutas de la API
export const routes = {
    // Usuarios
    users: {
        // CRUD básico
        getAll: () => `${API_BASE}users/getAll`,                          // GET /users
        getAllRoles: () => `${API_BASE}users/getAllRoles`,                          // GET /users/getAllRoles
        getByPage: (page, perPage, startDate, endDate) => `${API_BASE}users/getByPage?page=${page}&perPage=${perPage}&startDate=${startDate}&endDate=${endDate}`, // GET /users/getByPage?page=1&perPage=10
        getOne: (id) => `${API_BASE}users/${id}/getUserById`,                  // GET /users/{id} (si implementas show)
        store: () => `${API_BASE}users/store`,                     // POST /users/store
        update: (id) => `${API_BASE}users/${id}/update`,           // POST /users/{id}/update
        delete: (id) => `${API_BASE}users/${id}/delete`,           // POST /users/{id}/delete
        
        // Autenticación
        login: () => `${API_BASE}auth.php?action=login`,
        profile: () => `${API_BASE}auth.php?action=profile`,
        
        // Métodos personalizados
        search: (query) => `${API_BASE}users?search=${encodeURIComponent(query)}`
    },
    
    // Productos
    products: {
        getAll: () => `${API_BASE}products`,
        getOne: (id) => `${API_BASE}products/${id}`,
        create: () => `${API_BASE}products`,
        update: (id) => `${API_BASE}products/${id}`,
        delete: (id) => `${API_BASE}products/${id}`
    },
    
    // Otras rutas
    auth: {
        login: () => `${API_BASE}auth/login`,
        register: () => `${API_BASE}auth/register`
    }
};

export default routes;
