import crypto from "crypto";

/**
 * Generates a random token for password reset or other security purposes
 * @param {number} length Length of the token (default: 32)
 * @returns {string} Random token in hexadecimal format
 */
export function generateRandomToken(length = 32) {
    return crypto.randomBytes(length).toString("hex");
}
