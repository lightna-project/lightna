export function extend(AddToCart) {
    return class extends AddToCart {
        constructor() {
            super();
            console.log('AddToCart constructor extend example');
        }
    }
}
