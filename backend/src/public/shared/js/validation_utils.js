// Validate email
export function validateEmail(email) {
    if (!email) return false;
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Validate phone number (Vietnamese format)
export function validatePhoneNumber(phone) {
    if (!phone) return false;
    const re = /^(0[0-9]{9,10})$/;
    return re.test(phone);
}

// Validate password
export function validatePassword(password) {
    if (!password) return false;
    // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
    const re = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/;
    return re.test(password);
}

// Validate date format (dd/MM/yyyy)
export function validateDateFormat(date) {
    if (!date) return false;
    const re = /^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/\d{4}$/;
    return re.test(date);
}

// Validate URL
export function validateUrl(url) {
    if (!url) return false;
    try {
        new URL(url);
        return true;
    } catch {
        return false;
    }
}

// Validate file extension
export function validateFileExtension(filename, allowedExtensions) {
    if (!filename || !allowedExtensions) return false;
    const extension = filename.split(".").pop().toLowerCase();
    return allowedExtensions.includes(extension);
}

// Validate file size
export function validateFileSize(fileSize, maxSizeInMB) {
    if (!fileSize || !maxSizeInMB) return false;
    const maxSizeInBytes = maxSizeInMB * 1024 * 1024;
    return fileSize <= maxSizeInBytes;
}

// Validate required fields
export function validateRequiredFields(data, requiredFields) {
    if (!data || !requiredFields) return false;
    
    for (const field of requiredFields) {
        if (!data[field] || data[field].toString().trim() === "") {
            return false;
        }
    }
    
    return true;
}

// Validate number range
export function validateNumberRange(number, min, max) {
    if (number === null || number === undefined) return false;
    return number >= min && number <= max;
}

// Validate string length
export function validateStringLength(string, minLength, maxLength) {
    if (!string) return false;
    return string.length >= minLength && string.length <= maxLength;
}

// Validate number
export function validateNumber(value, min = null, max = null) {
    const num = Number(value);
    if (isNaN(num)) return false;
    
    if (min !== null && num < min) return false;
    if (max !== null && num > max) return false;
    
    return true;
}

// Validate required
export function validateRequired(value) {
    return value !== null && value !== undefined && value.toString().trim() !== "";
}

// Validate file
export function validateFile(file, options = {}) {
    if (!file) return false;
    
    const {
        maxSize = 5 * 1024 * 1024, // 5MB
        allowedTypes = ["image/jpeg", "image/png", "application/pdf"]
    } = options;
    
    if (file.size > maxSize) return false;
    if (!allowedTypes.includes(file.type)) return false;
    
    return true;
}

// Validate form fields
export function validateFormFields(fields) {
    const errors = {};
    
    for (const [key, value] of Object.entries(fields)) {
        if (!validateRequired(value)) {
            errors[key] = "This field is required";
        }
    }
    
    return {
        isValid: Object.keys(errors).length === 0,
        errors
    };
} 