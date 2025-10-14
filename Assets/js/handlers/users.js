
import { validateFields } from '../utils/validations.js';

async function processResponse(response, handlerName) {
    try {
        // response ya es JSON de customFetch
        return await response;
    } catch (error) {
        console.error(`Error processing ${handlerName}:`, error.message);
        // Retornar un objeto consistente con error: true
        return {
            error: true,
            status: 0,
            statusText: "Error de red",
            body: {},
            message: error.message
        };
    }
}



export async function store(response) {
    try {
        const data = await processResponse(response, 'Users');

        // Manejo de errores HTTP + código de negocio 422
        if (data.error && data.status === 422) {
            // aquí ya tienes body con mensaje y fields
            const body = data.body || data; 
            console.warn("⚠️ Validación fallida:", body.message, body.fields || []);
            return;
        }

        // Otros errores HTTP
        if (data.error) {
            console.error("Error HTTP inesperado:", data.status, data.statusText, data.body?.message);
            return;
        }

        // Casos exitosos y códigos de negocio
        switch (data.status) {
            case 'USER_CREATED':
                console.log("✅ Usuario creado:", data.data);
                break;
            default:
                console.warn("⚠️ Respuesta inesperada:", data);
        }

    } catch (error) {
        console.error("Error procesando store:", error.message);
    }
}



export async function getUser(response) {
    try {
        const data = await processResponse(response, 'Users');
        
        switch (data.status) {
            case 'USER_UPDATED':
            console.log(data.data) 

            default:
                console.log(data.data)
                break;
        }
    } catch (error) {
        console.error('Error al procesar la respuesta:', error.message);
    }
}


export async function update(response) {
    const data = await processResponse(response, 'Users');
 
    // const validation = validateFields(data?.data, ["name", "email"]);

    // if (!validation.isValid) {
    //     console.error("❌ Faltan campos:", validation.missingFields);
    //     // Aquí podrías enviar `validation.missingFields` al front
    //     return validation;
    // }
    switch (data.status) {
        case 'USER_UPDATED':
            console.log(data.data)
            updateUserRow(data.data);
        function updateUserRow(user) {
            const row = document.querySelector(`tr[id="${user.user_id}"]`);
            if (!row) return;

            row.cells[0].textContent = user.user_name;
            row.cells[1].textContent = user.user_email;
            row.cells[2].textContent = user.user_status ? "Activo" : "Inactivo";
            row.cells[3].textContent = rolesMap[user.role_id];

            row.classList.add('updated');



            setTimeout(() => row.classList.remove('updated'), 1000);
        }
            break;

        case 'error':

            break;

        default:

            break;
    }
}