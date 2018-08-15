import './jquery';  // jQuery first
import 'bootstrap';
import 'admin-lte';
import 'icheck';
import Vue from 'vue';

Vue.mixin({
    mounted() {
        $(this.$el).iCheck({
            radioClass: 'iradio_square-blue',
            checkboxClass: 'icheckbox_square-blue'
        }).on('ifChecked ifUnchecked', function () {
            $(this)[0].dispatchEvent(new Event('change'));
        });

        $('[data-toggle="tooltip"]').tooltip();
    }
});

$(document).ready(() => {
    $('input').iCheck({
        radioClass: 'iradio_square-blue',
        checkboxClass: 'icheckbox_square-blue'
    });

    $('[data-toggle="tooltip"]').tooltip();
});

(() => {
    const list = [
        {
            path: 'auth',
            styl: () => import('../stylus/auth.styl')
        },
    ];

    const style = list.find(({ path }) => blessing.route.startsWith(path));
    if (style) {
        style.styl();
    }
})();
