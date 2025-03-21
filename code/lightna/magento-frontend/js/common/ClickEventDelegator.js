export class ClickEventDelegator {
    static actions = {
        // 'action-name': [], array of functions
    }
    dataAttribute = 'data-click-action';

    constructor() {
        this.extendProperties();
        this.initializeEventListeners();
    }

    extendProperties() {
    }

    initializeEventListeners() {
        document.addEventListener('click', (event) => this.handleClick(event));
    }

    static add(actions) {
        for (const [action, handlers] of Object.entries(actions)) {
            if (this.actions[action]) {
                this.actions[action].push(...handlers);
                return;
            }

            this.actions[action] = handlers;
        }
    }

    handleClick(event) {
        const actionElement = event.target.closest(`[${this.dataAttribute}]`);
        if (!actionElement) return;

        const actionType = actionElement.getAttribute(this.dataAttribute);
        const actionHandlers = ClickEventDelegator.actions[actionType];
        if (actionHandlers?.length) {
            for (const actionHandler of actionHandlers) {
                try {
                    actionHandler(event, actionElement);
                } catch (error) {
                    console.error(`Error handling click action: '${actionType}'.\n`, error);
                }
            }
        }
    }
}
