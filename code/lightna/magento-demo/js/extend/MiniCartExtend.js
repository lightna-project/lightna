import { ClickEventDelegator} from 'lightna/magento-frontend/common/ClickEventDelegator';

export function extend(MiniCart) {
    return class extends MiniCart {
        initialize() {
            this.extendParentProperties();
            super.initialize();
        }

        extendParentProperties() {
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

            MiniCart.classes =  {
                ...MiniCart.classes,
                fade: 'fade-out-extended',
                customClass: 'custom-class',
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
