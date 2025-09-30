// Configuración base de la API
const API_BASE = 'http://localhost/trainscoreflow/';

// Definición de rutas de la API
export const routes = {
    // Usuarios
    users: {
        // CRUD básico
        getAll: () => `${API_BASE}users/store`,
        getOne: () => `${API_BASE}users/store`,
        store: () => `${API_BASE}users/store`,
        update: () => `${API_BASE}users/update`,
        delete: () => `${API_BASE}users/delete`,
        
        // Autenticación
        login: () => `${API_BASE}/auth.php?action=login`,
        profile: () => `${API_BASE}/auth.php?action=profile`,
        
        // Métodos personalizados
        search: (query) => `${API_BASE}/users?search=${encodeURIComponent(query)}`
    },
    
    // Productos
    products: {
        getAll: () => `${API_BASE}/products`,
        getOne: () => `${API_BASE}/products`,
        create: () => `${API_BASE}/products`,
        update: () => `${API_BASE}/products`,
        delete: () => `${API_BASE}/products`
    },
    
    // Otras rutas pueden ir aquí
    auth: {
        login: () => `${API_BASE}/auth/login`,
        register: () => `${API_BASE}/auth/register`
    }
};

export default routes;
