export class ClickEventDelegator {
    static actions = {}

    constructor() {
        document.addEventListener('click', (event) => this.handleClick(event));
    }

    static addActions(actions) {
        for (const [action, handler] of Object.entries(actions)) {
            if (this.actions[action]) {
                const originalHandler = this.actions[action];
                this.actions[action] = (event, element) => {
                    originalHandler(event, element);
                    handler(event, element);
                };

                return;
            }

            this.actions[action] = handler;
        }
    }

    handleClick(event) {
        const actionElement = event.target.closest('[data-click-action]');
        if (!actionElement) return;

        const actionType = actionElement.getAttribute('data-click-action');
        const actionHandler = ClickEventDelegator.actions[actionType];
        if (actionHandler) {
            try {
                actionHandler(event, actionElement);
            } catch (error) {
                console.error(`Error handling click action: ${action}.`, error);
            }
        }
    }
}
