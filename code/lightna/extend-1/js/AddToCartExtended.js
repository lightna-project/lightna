export function extend(AddToCart) {

    return class extends AddToCart {
        async addProduct(component, actionTrigger) {
            console.log('extend 1');
            super.addProduct(component, actionTrigger);
        }
    }
}
