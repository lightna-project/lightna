export function randomString(length) {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    length = typeof length === 'undefined' ? 16 : length;

    let string = '';
    for (let i = 0; i < length; i++) {
        string += chars.charAt(Math.floor(Math.random() * chars.length));
    }

    return string;
}
