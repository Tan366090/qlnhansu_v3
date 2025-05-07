// Format date to dd/MM/yyyy
export function formatDate(date) {
    if (!date) return "";
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, "0");
    const month = String(d.getMonth() + 1).padStart(2, "0");
    const year = d.getFullYear();
    return `${day}/${month}/${year}`;
}

// Format date to yyyy-MM-dd
export function formatDateISO(date) {
    if (!date) return "";
    const d = new Date(date);
    return d.toISOString().split("T")[0];
}

// Format date to custom format
export function formatDateCustom(date, format) {
    if (!date) return "";
    const d = new Date(date);
    
    const replacements = {
        "dd": String(d.getDate()).padStart(2, "0"),
        "MM": String(d.getMonth() + 1).padStart(2, "0"),
        "yyyy": d.getFullYear(),
        "HH": String(d.getHours()).padStart(2, "0"),
        "mm": String(d.getMinutes()).padStart(2, "0"),
        "ss": String(d.getSeconds()).padStart(2, "0")
    };
    
    return format.replace(/dd|MM|yyyy|HH|mm|ss/g, match => replacements[match]);
}

// Get age from birth date
export function getAge(birthDate) {
    if (!birthDate) return 0;
    const today = new Date();
    const birth = new Date(birthDate);
    let age = today.getFullYear() - birth.getFullYear();
    const monthDiff = today.getMonth() - birth.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
        age--;
    }
    
    return age;
}

// Add days to date
export function addDays(date, days) {
    if (!date) return null;
    const result = new Date(date);
    result.setDate(result.getDate() + days);
    return result;
}

// Add months to date
export function addMonths(date, months) {
    if (!date) return null;
    const result = new Date(date);
    result.setMonth(result.getMonth() + months);
    return result;
}

// Add years to date
export function addYears(date, years) {
    if (!date) return null;
    const result = new Date(date);
    result.setFullYear(result.getFullYear() + years);
    return result;
}

// Get difference between two dates in days
export function getDaysDifference(date1, date2) {
    if (!date1 || !date2) return 0;
    const d1 = new Date(date1);
    const d2 = new Date(date2);
    const diffTime = Math.abs(d2 - d1);
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
}

// Check if date is valid
export function isValidDate(date) {
    if (!date) return false;
    const d = new Date(date);
    return d instanceof Date && !isNaN(d);
}

// Get first day of month
export function getFirstDayOfMonth(date) {
    if (!date) return null;
    const d = new Date(date);
    return new Date(d.getFullYear(), d.getMonth(), 1);
}

// Get last day of month
export function getLastDayOfMonth(date) {
    if (!date) return null;
    const d = new Date(date);
    return new Date(d.getFullYear(), d.getMonth() + 1, 0);
}

// Check if date is weekend
export function isWeekend(date) {
    if (!date) return false;
    const d = new Date(date);
    return d.getDay() === 0 || d.getDay() === 6;
}

// Check if date is today
export function isToday(date) {
    if (!date) return false;
    const today = new Date();
    const d = new Date(date);
    return d.toDateString() === today.toDateString();
}

// Check if date is in the past
export function isPastDate(date) {
    if (!date) return false;
    const today = new Date();
    const d = new Date(date);
    return d < today;
}

// Check if date is in the future
export function isFutureDate(date) {
    if (!date) return false;
    const today = new Date();
    const d = new Date(date);
    return d > today;
}

// Get current date
export function getCurrentDate() {
    return new Date();
} 