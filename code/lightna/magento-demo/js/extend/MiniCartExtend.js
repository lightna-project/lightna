import { ClickEventDelegator} from 'lightna/magento-frontend/common/ClickEventDelegator';

export function extend(MiniCart) {
    return class extends MiniCart {
        constructor() {
            super({
                actions: {
                    click: {
                        'open-minicart': [() => this.customOpen()],
                        'increase-qty': [(event, trigger) => this.increaseQty(trigger)],
                    }
                },
                classes: {
                    fade: 'fade-out-extended',
                    customClass: 'custom-class',
                },
            });
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
