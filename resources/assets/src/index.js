import Vue from 'vue';
import routes from './components/route';
import './js';

Vue.config.productionTip = false;

// eslint-disable-next-line no-undef
__webpack_public_path__ = blessing.base_url + '/public/';

const route = routes.find(route => route.path === blessing.route);
if (route) {
    new Vue({
        el: route.el,
        render: h => h(route.component)
    });
}
