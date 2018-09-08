import Vue from 'vue';
import './js';
import * as emitter from './js/event';
import routes from './components/route';

Vue.config.productionTip = false;

if (process.env.NODE_ENV === 'development') {
    const langs = [
        { lang: 'en', load: () => import('../../lang/en/front-end') },
        { lang: 'zh_CN', load: () => import('../../lang/zh_CN/front-end') },
    ];
    setTimeout(langs.find(({ lang }) => lang === blessing.locale).load, 0);
}

(() => {
    const route = routes.find(
        route => (new RegExp(`^${route.path}$`, 'i')).test(blessing.route)
    );
    if (route) {
        Vue.prototype.$route = (new RegExp(`^${route.path}$`, 'i')).exec(blessing.route);
        new Vue({
            el: route.el,
            mounted() {
                emitter.emit('mounted', { el: route.el });
            },
            render: h => h(route.component)
        });
    }
})();
