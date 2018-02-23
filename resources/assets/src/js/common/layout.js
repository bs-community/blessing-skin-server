'use strict';

$.defaultPaginatorConfig = {
    visiblePages: 5,
    currentPage: 1,
    first: '<li><a style="cursor: pointer;">«</a></li>',
    prev: '<li><a style="cursor: pointer;">‹</a></li>',
    next: '<li><a style="cursor: pointer;">›</a></li>',
    last: '<li><a style="cursor: pointer;">»</a></li>',
    page: '<li><a style="cursor: pointer;">{{page}}</a></li>',
    wrapper: '<ul class="pagination pagination-sm no-margin"></ul>'
};

$(document).ready(() => {
    $('input').iCheck({
        radioClass: 'iradio_square-blue',
        checkboxClass: 'icheckbox_square-blue'
    });

    $('[data-toggle="tooltip"]').tooltip();

    swal.setDefaults({
        confirmButtonText: trans('general.confirm'),
        cancelButtonText: trans('general.cancel')
    });
});
