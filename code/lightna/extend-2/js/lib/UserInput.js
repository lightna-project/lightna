export function extend(UserInput) {

    return class extends UserInput {
        static collect(container, skipIfNotDisplayed) {
            console.log('UserInput extend');
            return super.collect(container, skipIfNotDisplayed);
        }
    }
}
