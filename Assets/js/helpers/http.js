import {CustomFetch} from "./customFetch.js";
import { routes } from './routes.js';
//import handlers from './handlers.js';
// Función para cargar handlers dinámicamente según el módulo
// Función para cargar handlers dinámicamente según el módulo

class FormHandler {
    constructor(formElement, url, customFetch, type = 'form') {
        this.formElement = formElement; // almacena el elemento del formulario HTML
        this.url = url; // almacena la URL donde se enviará el formulario
        this.customFetch = customFetch; // almacena la instancia de CustomFetch para realizar solicitudes HTTP
        this.type = type; // tipo de datos: 'form' para FormData, 'json' para JSON
    }

    async handleSubmit(e) {
        e.preventDefault(); // evita el comportamiento por defecto del formulario (recargar la página)
        try {
            // determina cómo enviar los datos según el tipo especificado
            if (this.type === 'json') {
                await this.submitJsonData(e);
            } else {
                await this.submitFormData(e);
            }
        } catch (error) {
            console.error("Ha ocurrido un error al enviar el formulario:", error.message);
        }
    }
    async loadHandler(handlerName) {
        try {
            // Extraer solo el nombre del módulo (antes del punto)
            const moduleName = handlerName.split('.')[0];
            const module = await import(`../handlers/${moduleName}.js`);
            return module;
        } catch (error) {
            console.error(`Error loading handler for ${handlerName}:`, error.message);
            throw error;
        }
    }



    async submitFormData(e) {
        e.preventDefault();

        const formData = new FormData(this.formElement);

        const csrfMetaTag = document.querySelector('meta[name="X-CSRF-TOKEN"]');
        const csrfToken = csrfMetaTag ? csrfMetaTag.getAttribute('content') : null;

        const headers = {
            'Authorization': 'Bearer <token>',
            'Custom-Header': 'CustomValue'
        };

        if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken;
        }

        try {
            const response = await this.customFetch.post(this.url, {
                body: formData,
                contentType: 'application/json',
                headers: headers
            });

            const manageData = e.target.getAttribute("data-destination");  // Obtiene el nombre del módulo (ej: 'attendances')
            const methodData = e.target.getAttribute("calling-method");   // Obtiene el nombre del método (ej: 'setAttendance')

            const handlerModule = await this.loadHandler(manageData);     // Cargar el módulo correspondiente

            if (handlerModule) {
                if (methodData && typeof handlerModule[methodData] === 'function') {
                    //console.log(`Calling method: ${methodData} from module: ${manageData}`);
                    await handlerModule[methodData](response); // Ejecutar la función del módulo
                } else {
                    console.warn(`No handler function found for method: ${methodData} in ${manageData}`);
                }
            } else {
                console.warn(`No handler found for destination: ${manageData}`);
            }


        } catch (error) {
            console.error("Error submitting form data:", error.message);
        }


    }

    async submitJsonData(e) {
        e.preventDefault(); // Evita el comportamiento por defecto del formulario

        const formData = new FormData(this.formElement);
        const data = Object.fromEntries(formData);

        const csrfMetaTag = document.querySelector('meta[name="X-CSRF-TOKEN"]');
        const csrfToken = csrfMetaTag ? csrfMetaTag.getAttribute('content') : null;

        const headers = {
            'Authorization': 'Bearer <token>',
            'Custom-Header': 'CustomValue',
            'Content-Type': 'application/json'
        };

        if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken;
        }
        try {
            const response = await this.customFetch.post(this.url, {
                body: data,
                contentType: 'application/json',
                headers: headers
            });

            const manageData = e.target.getAttribute("data-destination");  // Obtiene el nombre del módulo (ej: 'attendances')
            const methodData = e.target.getAttribute("calling-method");   // Obtiene el nombre del método (ej: 'setAttendance')

            const handlerModule = await this.loadHandler(manageData);     // Cargar el módulo correspondiente

            if (handlerModule) {
                if (methodData && typeof handlerModule[methodData] === 'function') {
                    //console.log(`Calling method: ${methodData} from module: ${manageData}`);
                    await handlerModule[methodData](response); // Ejecutar la función del módulo
                } else {
                    console.warn(`No handler function found for method: ${methodData} in ${manageData}`);
                }
            } else {
                console.warn(`No handler found for destination: ${manageData}`);
            }

        } catch (error) {
            console.error("Error submitting form data:", error.message);
        }
    }
}


const forms = document.querySelectorAll(".form-data");

const customFetch = new CustomFetch();
forms.forEach((form) => {

    // Obtiene la URL a la que se enviará el formulario a partir del atributo "data-url" del elemento del formulario
    const endPoint = form.getAttribute("data-destination");
    const method = form.getAttribute("calling-method");
    const type = form.getAttribute("data-type") || 'form'; // Obtiene el tipo de datos, por defecto 'form'

    if (endPoint) {
        // Obtiene la URL usando el nuevo sistema de rutas
        let url = '';
        // Asume que endPoint está en formato 'modulo.accion' (ej: 'users.getAll')
        const [module, action] = endPoint.split('.');
        if (routes[module] && routes[module][action]) {
            // Si es una función que necesita un ID (como getOne, update, delete)
            const id = form.getAttribute('data-id');
            url = id ? routes[module][action](id) : routes[module][action]();
        } else {
            console.error(`Ruta no encontrada: ${endPoint}`);
            return;
        }
        
        // Crea una nueva instancia de FormHandler y pasa el formulario, la URL y la instancia de CustomFetch
        const formHandler = new FormHandler(form, url, customFetch, type);
        // Añade un controlador de eventos al evento "submit" del formulario que llama al método handleSubmit de FormHandler
        form.addEventListener("submit", (e) => formHandler.handleSubmit(e));
    }
});