export function deepMerge(target, source) {
    // If source is not an object, return the target (no merge)
    if (typeof source !== 'object' || source === null) {
        return source;
    }

    // Ensure target is an object (create an empty one if it's not)
    if (typeof target !== 'object' || target === null) {
        target = Array.isArray(source) ? [] : {};
    }

    // Loop through each property in the source
    for (let key in source) {
        if (source.hasOwnProperty(key)) {
            // Recursively merge properties
            target[key] = deepMerge(target[key], source[key]);
        }
    }

    return target;
}
