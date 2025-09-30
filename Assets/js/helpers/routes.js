// Configuración base de la API
const API_BASE = 'http://localhost/trainscoreflow/';

// Definición de rutas de la API
export const routes = {
    // Usuarios
    users: {
        // CRUD básico
        getAll: () => `${API_BASE}users/store`,
        getOne: (id) => `${API_BASE}users/store/${id}`,
        store: () => `${API_BASE}users/store`,
        update: (id) => `${API_BASE}users/store/${id}`,
        delete: (id) => `${API_BASE}users/store/${id}`,
        
        // Autenticación
        login: () => `${API_BASE}/auth.php?action=login`,
        profile: () => `${API_BASE}/auth.php?action=profile`,
        
        // Métodos personalizados
        search: (query) => `${API_BASE}/users?search=${encodeURIComponent(query)}`
    },
    
    // Productos
    products: {
        getAll: () => `${API_BASE}/products`,
        getOne: (id) => `${API_BASE}/products/${id}`,
        create: () => `${API_BASE}/products`,
        update: (id) => `${API_BASE}/products/${id}`,
        delete: (id) => `${API_BASE}/products/${id}`
    },
    
    // Otras rutas pueden ir aquí
    auth: {
        login: () => `${API_BASE}/auth/login`,
        register: () => `${API_BASE}/auth/register`
    }
};

export default routes;
