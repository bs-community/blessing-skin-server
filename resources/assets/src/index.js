import Vue from 'vue';
import routes from './components/route';
import './js';

Vue.config.productionTip = false;

if (process.env.NODE_ENV === 'development') {
    const langs = [
        { lang: 'en', load: () => import('../../lang/en/front-end') },
        { lang: 'zh_CN', load: () => import('../../lang/zh_CN/front-end') },
    ];
    setTimeout(langs.find(({ lang }) => lang === blessing.locale).load, 0);
}

// eslint-disable-next-line no-undef
__webpack_public_path__ = process.env.NODE_ENV === 'development'
    ? 'http://127.0.0.1:8080/public/'
    : blessing.base_url + '/public/';

const route = routes.find(route => route.path === blessing.route);
if (route) {
    new Vue({
        el: route.el,
        render: h => h(route.component)
    });
}
