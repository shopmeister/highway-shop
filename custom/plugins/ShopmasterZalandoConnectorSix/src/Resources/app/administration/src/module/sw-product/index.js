import './page/sw-product-detail';
import './components';
import template from "./page/sw-product-detail/sw-product-detail.html.twig";

const {Module, Component} = Shopware;

Module.register('sw-product-detail-tab-sm-zalando', {
    routeMiddleware(next, currentRoute) {
        if (currentRoute.name === 'sw.product.detail') {
            currentRoute.children.push({
                name: 'sw.product.detail.zalando',
                path: '/sw/product/detail/:id/zalando',
                component: 'sm-product-submission',
                meta: {
                    parentPath: "sw.product.index"
                }
            });
        }
        next(currentRoute);
    }
});


// Component.override('sw-product', {
//     routes: {
//         detail: {
//             children: {
//                 zalando: {
//                     component: 'sw-product-detail-specifications',
//                     path: 'zalando',
//                     meta: {
//                         parentPath: 'sw.product.index',
//                         privilege: 'product.viewer',
//                     },
//                 },
//             }
//         }
//     }
// });
// Module.register('sw-product', {});