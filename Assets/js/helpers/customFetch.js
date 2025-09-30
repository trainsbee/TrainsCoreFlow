class CustomFetch {
    constructor() {
        this.post = this.post.bind(this);
        this.get = this.get.bind(this);
        this.put = this.put.bind(this);
        this.delete = this.delete.bind(this);
    }

    async customFetch(url, options = {}) {
        try {
            if (typeof url !== 'string' || !url) {
                throw new Error('La URL proporcionada no es válida.');
            }

            this.endPoint = url;
            this.options = this._prepareOptions(options);

            const response = await fetch(this.endPoint, this.options);

            if (!response.ok) {
                const errorBody = await response.text();
                throw new Error(response.statusText || 'Error en la solicitud');
            }

            return await response.json();
        } catch (err) {
            return {
                error: true,
                status: err.status || "00",
                statusText: err.statusText || "Ocurrió un error",
                message: err.message || "Ocurrió un error desconocido"
            };
        }
    }

    abort(controller) {
        if (controller) {
            controller.abort();
        } else {
            console.warn('No se proporcionó un controlador de aborto.');
        }
    }

    _prepareOptions(options) {
        const controller = new AbortController();
        const mergedOptions = { ...options, signal: controller.signal };

        if (mergedOptions.body instanceof FormData) {
            // No establecer Content-Type cuando se usa FormData
            delete mergedOptions.headers['Content-Type'];
        } else if (mergedOptions.body && !(mergedOptions.body instanceof FormData)) {
            // Convertir el cuerpo en JSON si no es FormData
            mergedOptions.body = JSON.stringify(mergedOptions.body);
            mergedOptions.headers = {
                ...mergedOptions.headers,
                'Content-Type': 'application/json'
            };
        }

        if (mergedOptions.headers) {
            mergedOptions.headers = {
                ...mergedOptions.headers,
                'Authorization': 'Bearer <token>', // Ejemplo de token de autorización
                'Accept': 'application/json',
                'Cache-Control': 'no-cache'
            };
        }

        if (mergedOptions.timeout) {
            setTimeout(() => this.abort(controller), mergedOptions.timeout);
        }

        return mergedOptions;
    }

    get(url, options = {}) {
        return this.customFetch(url, { ...options, method: 'GET' });
    }

    post(url, options = {}) {
        return this.customFetch(url, { ...options, method: 'POST' });
    }

    put(url, options = {}) {
        return this.customFetch(url, { ...options, method: 'PUT' });
    }

    delete(url, options = {}) {
        return this.customFetch(url, { ...options, method: 'DELETE' });
    }
}

export { CustomFetch };