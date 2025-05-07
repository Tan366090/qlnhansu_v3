// Capitalize first letter
export function capitalizeFirstLetter(string) {
    if (!string) return "";
    return string.charAt(0).toUpperCase() + string.slice(1);
}

// Truncate string
export function truncateString(string, maxLength) {
    if (!string) return "";
    if (string.length <= maxLength) return string;
    return string.substring(0, maxLength) + "...";
}

// Remove accents
export function removeAccents(string) {
    if (!string) return "";
    return string.normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/đ/g, "d").replace(/Đ/g, "D");
}

// Convert to slug
export function toSlug(string) {
    if (!string) return "";
    return removeAccents(string)
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, "-")
        .replace(/(^-|-$)+/g, "");
}

// Format currency
export function formatCurrency(amount, currency = "VND") {
    if (amount === null || amount === undefined) return "";
    
    const formatter = new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: currency
    });
    
    return formatter.format(amount);
}

// Format number
export function formatNumber(number, decimals = 0) {
    if (number === null || number === undefined) return "";
    
    return new Intl.NumberFormat("vi-VN", {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    }).format(number);
}

// Check if string contains only numbers
export function isNumeric(string) {
    if (!string) return false;
    return /^\d+$/.test(string);
}

// Check if string contains only letters
export function isAlpha(string) {
    if (!string) return false;
    return /^[a-zA-Z]+$/.test(string);
}

// Check if string contains only letters and numbers
export function isAlphanumeric(string) {
    if (!string) return false;
    return /^[a-zA-Z0-9]+$/.test(string);
}

// Generate random string
export function generateRandomString(length = 8) {
    const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    let result = "";
    
    for (let i = 0; i < length; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    
    return result;
} 