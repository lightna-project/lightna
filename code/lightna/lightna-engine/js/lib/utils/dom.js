export function $(element, scope) {
    // if elements is a string then it's selector
    if (getClassName(element) === 'String') {
        if (!scope) scope = document;
        return scope.querySelector(element);
    }

    return element;
}

export function $$(elements, scope = document) {
    if (scope === null) return [];

    // if elements is a string then it's selector
    if (getClassName(elements) === 'String') {
        elements = scope.querySelectorAll(elements);
    }

    if (getClassName(elements) === 'NodeList') {
        let nodeList = elements;
        elements = [];
        for (let i = 0; i < nodeList.length; i++) elements.push(nodeList[i]);
    }

    // if single element received
    if (getClassName(elements) !== 'Array') elements = [elements];

    return elements;
}

export function getClassName(object) {
    if (object === null) return null;
    let name = object.constructor.name;
    if (name) return name;

    // firefox %(
    return object.constructor.toString().match(/function\s+(\w*)/)[1];
}
