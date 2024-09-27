export function extend(AddToCart) {

    return class extends AddToCart {
        async addProduct(component, actionTrigger) {
            console.log('AddToCart extend 2');
            super.addProduct(component, actionTrigger);
        }
    }
}
