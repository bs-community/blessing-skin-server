'use strict';

$.skinlib = {
    page:     getQueryString('page', 1),
    filter:   getQueryString('filter', 'skin'),
    sort:     getQueryString('sort', 'time'),
    uploader: getQueryString('uploader', 0),
    keyword:  decodeURI(getQueryString('keyword', ''))
};

$(document).ready(() => {
    if ($('#skinlib-container').length != 0) {
        // Initially render skinlib
        requestSkinlibData().then(result => {
            renderSkinlib(result.items);

            updatePaginator(
                $.skinlib.page,
                result.total_pages || 1
            );
        });
    }
});

$('select.pagination').on('change', function () {
    onPageChange(parseInt($(this).val()));
});

$('.filter').click(function (e) {
    e.preventDefault();
    let selectedFilter = $(this).data('filter');

    if (selectedFilter == 'uploader') {
        $.skinlib.uploader = $(this).data('uid');
        console.log('Show items uploaded by uid ' + $.skinlib.uploader);
    } else {
        $.skinlib.filter = selectedFilter;
        console.log('Filter by ' + $.skinlib.filter);
    }

    reloadSkinlib();
});

$('.sort').click(function (e) {
    e.preventDefault();
    $.skinlib.sort = $(this).data('sort');

    console.log('Sort by ' + $.skinlib.sort);
    reloadSkinlib();
});

$('#search-form').submit(function (e) {
    e.preventDefault();
    $.skinlib.keyword = $('#navbar-search-input').val();

    console.log('Search keyword: ' + $.skinlib.keyword);
    reloadSkinlib();
});

function renderSkinlib(items) {
    let container = $('#skinlib-container').html('');

    if (items.length === 0) {
        $('#skinlib-paginator').hide();

        container.html(
            '<p style="text-align: center; margin: 30px 0;">' +
            trans('general.noResult') +
            '</p>'
        );
    } else {
        $('#skinlib-paginator').show();

        for (const item of items) {
            container.append(renderSkinlibItemComponent(item));
        }
    }

    $('.overlay').hide();
}

function reloadSkinlib() {
    requestSkinlibData().then(result => {
        $('.overlay').show();
        renderSkinlib(result.items);

        updatePaginator($.skinlib.page, result.total_pages || 1);
    }).then(() => {
        updateUrlQueryString();
        updateBreadCrumb();
    });
}

function requestSkinlibData() {
    return fetch({
        type: 'GET',
        url: url('skinlib/data'),
        dataType: 'json',
        data: $.skinlib,
        error: showAjaxError
    });
}

function renderSkinlibItemComponent(item) {
    let title = '';
    let anonymous = '';
    let liked = item.liked ? 'liked' : '';

    if (item.liked === undefined) {
        // If user haven't logged in
        title = trans('skinlib.anonymous');
        anonymous = 'anonymous';
    } else {
        title = item.liked ? trans('skinlib.removeFromCloset') : trans('skinlib.addToCloset');
    }

    return `<a href="${ url('skinlib/show/' + item.tid) }">
        <div class="item" tid="${ item.tid }">
            <div class="item-body">
                <img src="${ url('preview/' + item.tid + '.png') }">
            </div>

            <div class="item-footer">
                <p class="texture-name">
                    <span title="${ item.name }">${ item.name }
                        <small>${ trans('skinlib.filter.' + item.type) }</small>
                    </span>
                </p>

                <a title="${title}" class="more like ${liked} ${anonymous}" tid="${ item.tid }" href="javascript:;" data-placement="top" data-toggle="tooltip"><i class="fa fa-heart"></i></a>

                <small class="more private-label ${(item.public == 0) ? '' : 'hide'}" tid="${ item.tid }">
                    ${ trans('skinlib.private') }
                </small>
            </div>
        </div>
    </a>`;
}

function updatePaginator(currentPage, totalPages) {
    $.skinlib.page = currentPage;

    $('p.pagination').text(trans('general.pagination', {
        page: currentPage,
        total: totalPages
    }));

    let paginator = $('#skinlib-paginator');

    if (paginator.html().length == 0) {
        // init paginator
        $('#skinlib-paginator').jqPaginator($.extend({}, $.defaultPaginatorConfig, {
            currentPage: parseInt(currentPage),
            totalPages: parseInt(totalPages),
            onPageChange: onPageChange
        }));
    } else {
        $('#skinlib-paginator').jqPaginator('option', {
            currentPage: parseInt(currentPage),
            totalPages: parseInt(totalPages)
        });
    }

    let pageSelectElement = $('select.pagination').html('');

    for (let i = 1; i <= totalPages; i++) {
        pageSelectElement.append(`
            <option value="${i}" ${ (i == currentPage) ? 'selected' : '' }>${i}</option>
        `);
    }
}

function onPageChange(page, type) {
    $.skinlib.page = page;
    updateBreadCrumb();

    if (type == 'init') {
        console.log('Init paginator', page);
    } else {
        $('.overlay').show();
        reloadSkinlib();

        console.log('Rendering page', page);
    }
}

function updateUrlQueryString() {
    let query = $.param($.skinlib);

    window.history.pushState(null, null, url(`skinlib?${ query }`));

    $('li.locale').each(function () {
        $(this).find('a').prop('href', `?lang=${ $(this).data('code') }&${ query }`);
    });
}

function updateBreadCrumb() {
    if ($.skinlib.filter == 'cape') {
        $('#filter-indicator').html(trans('general.cape'));
    } else {
        $('#filter-indicator').html(trans('general.skin') + `<small>
            ${ trans('skinlib.filter.' + $.skinlib.filter) }
        </small>`);
    }

    if ($.skinlib.uploader != 0) {
        $('#uploader-indicator').html(trans('skinlib.filter.uploader', { uid: $.skinlib.uploader }));
    } else {
        $('#uploader-indicator').html(trans('skinlib.filter.allUsers'));
    }

    $('#sort-indicator').html(trans('skinlib.sort.' + $.skinlib.sort));

    if ($.skinlib.keyword != '') {
        $('#search-indicator').html(trans('general.searchResult', {
            keyword: decodeURI($.skinlib.keyword)
        }));

        $('#navbar-search-input').val(decodeURI($.skinlib.keyword));
    }
}

if (typeof require !== 'undefined' && typeof module !== 'undefined') {
    module.exports = {
        renderSkinlib,
        reloadSkinlib,
        updatePaginator,
        updateUrlQueryString,
        updateBreadCrumb
    };
}
