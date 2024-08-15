import { $ } from './lib/utils';
import { ShoppingCart } from './common/ShoppingCart';
import { Menu } from './common/Menu';
import { MobileOverlay } from './common/MobileOverlay';
import { ProductOptions } from './product/Options';
import { Facets } from './listing/Facets';
import { Collapsible } from './common/Collapsible';
import { Tabs } from './common/Tabs';
import { Gallery } from './product/Gallery';

new ShoppingCart();
new Menu();
new MobileOverlay();
new ProductOptions();
new Facets();
new Gallery({ offset: 1, gallery: '.cjs-gallery' });
new Collapsible($('.cjs-facets'));
new Tabs($('.cjs-product-info-tabs'));
