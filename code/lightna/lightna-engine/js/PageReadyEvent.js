export class PageReadyEvent {
    static listeners = [];
    static isTriggered = false;

    static addListener(listener) {
        if (!PageReadyEvent.isTriggered) {
            PageReadyEvent.listeners.push(listener);
        } else {
            listener();
        }
    }

    static trigger() {
        if (PageReadyEvent.isTriggered) return;

        for (const listener of PageReadyEvent.listeners) {
            listener();
        }

        PageReadyEvent.isTriggered = true;
    }
}
