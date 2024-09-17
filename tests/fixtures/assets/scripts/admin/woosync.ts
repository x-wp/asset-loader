import { WpRouter } from '@wptoolset/router';
import { SyncListRoute } from './routes/sync-list.resolver';
import { EditProductController } from './controllers/edit-product.controller';
import { CommonController } from './controllers/common.controller';

const routes = new WpRouter({
  common: () => new CommonController(),
  woocommercePageWoosyncSynchronizations: () => new SyncListRoute(),
  postTypeProduct: () => new EditProductController(),
});

document.addEventListener('DOMContentLoaded', () => routes.loadEvents());
