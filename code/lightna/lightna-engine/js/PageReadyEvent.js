/**
 * Use PageReady as the global instance of PageReadyEvent.
 */
export class PageReadyEvent {
    #listeners = [];
    #isTriggered = false;

    extendProperties() {
    }

    addListener(listener) {
        if (!this.#isTriggered) {
            this.#listeners.push(listener);
        } else {
            listener();
        }
    }

    trigger() {
        if (this.#isTriggered) return;

        for (const listener of this.#listeners) {
            listener();
        }

        this.#isTriggered = true;
    }
}
