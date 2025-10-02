
import { validateFields } from '../utils/validations.js';

async function processResponse(response, handlerName) {
    try {
        return await response;
    } catch (error) {
        console.error(`Error processing ${handlerName}:`, error.message);
        throw error;
    }
}

export async function getUser(response) {
    try {
        const data = await processResponse(response, 'Users');
        
        switch (data.status) {
            case 'success':
            console.log(data.data) 

            default:
                console.log(data.data)
                break;
        }
    } catch (error) {
        console.error('Error al procesar la respuesta:', error.message);
    }
}
export async function store(response) {
    const data = await processResponse(response, 'Users');
    console.log(data)
    // const validation = validateFields(data?.data, ["name", "email"]);

    // if (!validation.isValid) {
    //     console.error("❌ Faltan campos:", validation.missingFields);
    //     // Aquí podrías enviar `validation.missingFields` al front
    //     return validation;
    // }
    switch (data.status) {
        case 'success':
            console.log(data.data)
        
            break;

        case 'error':
            
            break;

        default:
             
            break;
    }
}
export async function update(response) {
    const data = await processResponse(response, 'Users');
    console.log(data)
    // const validation = validateFields(data?.data, ["name", "email"]);

    // if (!validation.isValid) {
    //     console.error("❌ Faltan campos:", validation.missingFields);
    //     // Aquí podrías enviar `validation.missingFields` al front
    //     return validation;
    // }
    switch (data.status) {
        case 'success':
            console.log(data.data)
        
            break;

        case 'error':
            
            break;

        default:
             
            break;
    }
}