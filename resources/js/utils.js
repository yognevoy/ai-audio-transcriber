/**
 * Escape HTML special characters to prevent XSS attacks.
 *
 * @param {string} text - The text to escape
 * @returns {string} - The escaped text safe for HTML insertion
 */
export function escapeHtml(text) {
    if (typeof text !== 'string') {
        return text;
    }

    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Capitalize the first letter of a string.
 *
 * @param {string} string - The string to capitalize
 * @returns {string} - The capitalized string
 */
export function ucFirst(string) {
    if (typeof string !== 'string' || string.length === 0) {
        return string;
    }
    return string.charAt(0).toUpperCase() + string.slice(1);
}
