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

$(window).resize(activateLayout);

$(document).ready(() => {
    activateLayout();

    $('li.active > ul').show();

    $('input').iCheck({
        checkboxClass: `icheckbox_square${getColorScheme()}`
    });

    swal.setDefaults({
        confirmButtonText: trans('general.confirm'),
        cancelButtonText: trans('general.cancel')
    });
});

function activateLayout() {
    if (location.pathname == '/' || location.pathname.includes('auth'))
        return;

    $.AdminLTE.layout.activate();
}

function getColorScheme() {
    const color = /skin-(\w+)(?:-light)? sidebar-mini/.exec(
        document.getElementsByTagName('body')[0].className
    )[1];
    return color === 'black' ? '' : `-${color}`;
}
