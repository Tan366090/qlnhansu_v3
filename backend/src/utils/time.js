/**
 * Returns the current Unix timestamp in seconds
 * @returns {number} Current Unix timestamp
 */
export function time() {
    return Math.floor(Date.now() / 1000);
} 