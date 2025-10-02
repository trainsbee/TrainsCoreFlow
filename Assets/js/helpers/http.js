// Assets/js/helpers/formHandler.js
import { CustomFetch } from "./customFetch.js";
import { routes } from './routes.js';

class FormHandler {
    constructor(formElement, endPoint, customFetch, type = 'form') {
        this.formElement = formElement;
        this.endPoint = endPoint; // Guardar endPoint en lugar de url precalculada
        this.customFetch = customFetch;
        this.type = type; // 'form' o 'json'
    }

    async handleSubmit(e) {
        e.preventDefault();
        try {
            // Obtener data-id o user_id dinámicamente al enviar el formulario
            const id = this.formElement.getAttribute('data-id') 
            || this.formElement.querySelector('input[name="user_id"]')?.value 
            || null;
            const [module, action] = this.endPoint.split('.');
            if (!routes[module] || !routes[module][action]) {
                console.error(`Ruta no encontrada: ${this.endPoint}`);
                return;
            }
            const url = id ? routes[module][action](id) : routes[module][action]();
            console.log('URL generada para submit:', url); // Debug

            if (this.type === 'json') {
                await this.submitJsonData(url);
            } else {
                await this.submitFormData(url);
            }
        } catch (error) {
            console.error("Error al enviar formulario:", error.message);
        }
    }

    async loadHandler(handlerName) {
        try {
            const moduleName = handlerName.split('.')[0];
            const module = await import(`../handlers/${moduleName}.js`);
            return module;
        } catch (error) {
            console.error(`Error cargando handler para ${handlerName}:`, error.message);
            throw error;
        }
    }

    async submitFormData(url) {
        const formData = new FormData(this.formElement);
        await this.sendRequest(url, formData);
    }

    async submitJsonData(url) {
        const formData = new FormData(this.formElement);
        const data = Object.fromEntries(formData);
        await this.sendRequest(url, data);
    }

    async sendRequest(url, data) {
        const csrfMetaTag = document.querySelector('meta[name="X-CSRF-TOKEN"]');
        const csrfToken = csrfMetaTag ? csrfMetaTag.getAttribute('content') : null;

        const headers = {
            'Authorization': 'Bearer <token>',
            'Custom-Header': 'CustomValue'
        };

        let body = data;

        if (this.type === 'json') {
            headers['Content-Type'] = 'application/json';
            body = data; // CustomFetch ya se encarga de stringificar si es JSON
        }

        if (csrfToken) headers['X-CSRF-TOKEN'] = csrfToken;

        const method = (this.formElement.getAttribute('data-method') || 'POST').toUpperCase();
        console.log('Método:', method, 'URL:', url, 'Datos:', data); // Debug

        try {
            let response;
            switch (method) {
                case 'POST':
                    response = await this.customFetch.post(url, { body, headers });
                    break;
                case 'PUT':
                    response = await this.customFetch.put(url, { body, headers });
                    break;
                default:
                    throw new Error(`Método HTTP no soportado: ${method}`);
            }

            const manageData = this.formElement.getAttribute("data-destination");
            const methodData = this.formElement.getAttribute("calling-method");

            const handlerModule = await this.loadHandler(manageData);
            if (handlerModule && methodData && typeof handlerModule[methodData] === 'function') {
                await handlerModule[methodData](response);
            }
        } catch (error) {
            console.error("Error enviando solicitud:", error.message);
        }
    }
}

// Inicialización
const forms = document.querySelectorAll(".form-data");
const customFetch = new CustomFetch();

forms.forEach((form) => {
    const endPoint = form.getAttribute("data-destination");
    const type = form.getAttribute("data-type") || 'form';

    if (!endPoint) return;

    const formHandler = new FormHandler(form, endPoint, customFetch, type);
    form.addEventListener("submit", (e) => formHandler.handleSubmit(e));
});

export default FormHandler;