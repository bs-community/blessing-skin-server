import './jquery';  // jQuery first
import 'bootstrap';
import 'bootstrap-fileinput';
import 'admin-lte';
import 'icheck';
import Vue from 'vue';
import swal from 'sweetalert2';
import { trans } from './i18n';

swal.setDefaults({
    confirmButtonText: trans('general.confirm'),
    cancelButtonText: trans('general.cancel')
});

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
