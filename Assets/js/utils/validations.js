// utils/validators.js
export function validateFields(data, requiredFields = []) {
    const missingFields = [];

    for (const field of requiredFields) {
        if (!data?.[field]) {
            missingFields.push(field);
        }
    }

    return {
        isValid: missingFields.length === 0, // boolean
        missingFields, // array con los campos vacíos
        data           // devolver también la data por conveniencia
    };
}
