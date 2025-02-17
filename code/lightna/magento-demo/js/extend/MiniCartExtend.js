import { ClickEventDelegator} from 'lightna/magento-frontend/common/ClickEventDelegator';

export function extend(MiniCart) {
    return class extends MiniCart {
        extendProperties() {
            const parentClickActions = this.actions.click;
            this.actions.click = {
                ...parentClickActions,
                'open-minicart': [() => this.customOpen()],
                'close-minicart': [
                    ...parentClickActions['close-minicart'],
                    () => console.log('Additional close handler for MiniCart')
                ],
                'increase-qty': [(event, trigger) => this.increaseQty(trigger)],
            };
        }

        customOpen(trigger) {
            super.open();
            console.log('customOpen for MiniCart');
        }

        increaseQty(trigger) {
            console.log('increaseQty for item in MiniCart');
        }
    }
}
