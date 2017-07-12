'use strict';

$(document).ready(() => {
    swal.setDefaults({
        confirmButtonText: trans('general.confirm'),
        cancelButtonText: trans('general.cancel')
    });

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

$('#private').on('ifToggled', function () {
    $(this).prop('checked') ? $('#msg').show() : $('#msg').hide();
});
$('#type-skin').on('ifToggled', function () {
    $(this).prop('checked') ? $('#skin-type').show() : $('#skin-type').hide();
});

$.skinlib = {
    page:     getQueryString('page', 1),
    filter:   getQueryString('filter', 'skin'),
    sort:     getQueryString('sort', 'time'),
    uploader: getQueryString('uploader', 0),
    keyword:  decodeURI(getQueryString('keyword', ''))
};

$(document).ready(() => {

});

function renderSkinlib(items) {
    let container = $('#skinlib-container').html('');

    if (items.length === 0) {
        $('#skinlib-paginator').hide();

        container.html(`<p style="text-align: center; margin: 30px 0;">
            ${ trans('general.noResult') }
        </p>`);
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
    return Promise.resolve($.ajax({
        type: 'GET',
        url: url('skinlib/data'),
        dataType: 'json',
        data: $.skinlib,
        error: showAjaxError
    }));
}

function renderSkinlibItemComponent(item) {
    let title = "";
    let anonymous = "";
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
                        <small>${ trans("skinlib.filter." + item.type) }</small>
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

    if (type == "init") {
        console.log('Init paginator', page);
    } else {
        $('.overlay').show();
        reloadSkinlib();

        console.log('Rendering page', page);
    }
}

$('select.pagination').on('change', function () {
    onPageChange(parseInt($(this).val()));
});

$(document).on('click', '.more.like', function () {
    let tid = $(this).attr('tid');

    if ($(this).hasClass('anonymous'))
        return;

    if ($(this).hasClass('liked')) {
        removeFromCloset(tid);
    } else {
        addToCloset(tid);
    }
});

$('.filter').click(function (e) {
    e.preventDefault();
    let selectedFilter = $(this).data('filter');

    if (selectedFilter == "uploader") {
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

function updateUrlQueryString() {
    let query = $.param($.skinlib);

    window.history.pushState(null, null, url(`skinlib?${query}`));
}

function updateBreadCrumb() {
    if ($.skinlib.filter == "cape") {
        $('#filter-indicator').html(trans('general.cape'));
    } else {
        $('#filter-indicator').html(trans('general.skin') + `<small>
            ${ trans('skinlib.filter.' + $.skinlib.filter) }
        </small>`);
    }

    if ($.skinlib.uploader != 0) {
        $('#uploader-indicator').html(trans('skinlib.filter.uploader', {uid: $.skinlib.uploader}));
    } else {
        $('#uploader-indicator').html(trans('skinlib.filter.allUsers'));
    }

    $('#sort-indicator').html(trans('skinlib.sort.' + $.skinlib.sort));

    if ($.skinlib.keyword != "") {
        $('#search-indicator').html(trans('general.searchResult', {
            keyword: decodeURI($.skinlib.keyword)
        }));

        $('#navbar-search-input').val(decodeURI($.skinlib.keyword));
    }
}

function addToCloset(tid) {
    $.getJSON(url(`skinlib/info/${tid}`), (json) => {
        swal({
            title: trans('skinlib.setItemName'),
            inputValue: json.name,
            input: 'text',
            showCancelButton: true,
            inputValidator: (value) => {
                return new Promise((resolve, reject) => {
                    value ? resolve() : reject(trans('skinlib.emptyItemName'));
                });
            }
        }).then((result) => ajaxAddToCloset(tid, result));
    });
}

/**
 * Update button action & likes of texture.
 *
 * @param  {int}    tid
 * @param  {string} action add|remove
 * @return {null}
 */
function updateTextureStatus(tid, action) {
    let likes  = parseInt($('#likes').html()) + (action == "add" ? 1 : -1);
        action = (action == "add") ? 'removeFromCloset' : 'addToCloset';

    $(`a[tid=${tid}]`).attr('href', `javascript:${action}(${tid});`).attr('title', trans('skinlib.' + action)).toggleClass('liked');
    $('#'+tid).attr('href', `javascript:${action}(${tid});`).html(trans('skinlib.' + action));
    $('#likes').html(likes);
}

function ajaxAddToCloset(tid, name) {
    // remove interference of modal which is hide
    $('.modal').each(function () {
        return ($(this).css('display') == "none") ? $(this).remove() : null;
    });

    $.ajax({
        type: "POST",
        url: url("user/closet/add"),
        dataType: "json",
        data: { 'tid': tid, 'name': name },
        success: (json) => {
            if (json.errno == 0) {
                swal({
                    type: 'success',
                    html: json.msg
                });

                $('.modal').modal('hide');
                updateTextureStatus(tid, 'add');
            } else {
                toastr.warning(json.msg);
            }
        },
        error: showAjaxError
    });
}

function removeFromCloset(tid) {
    swal({
        text: trans('user.removeFromClosetNotice'),
        type: 'warning',
        showCancelButton: true,
        cancelButtonColor: '#3085d6',
        confirmButtonColor: '#d33'
    }).then(() => {
        $.ajax({
            type: "POST",
            url: url("/user/closet/remove"),
            dataType: "json",
            data: { 'tid' : tid },
            success: (json) => {
                if (json.errno == 0) {
                    swal({
                        type: 'success',
                        html: json.msg
                    });

                    updateTextureStatus(tid, 'remove');
                } else {
                    toastr.warning(json.msg);
                }
            },
            error: showAjaxError
        });
    });

}

$('body').on('change', '#file', () => handleFiles()).on('ifToggled', '#type-cape', () => {
    MSP.clear();
    handleFiles();
});

// Real-time preview
function handleFiles(files, type) {

    files = files || $('#file').prop('files');
    type  = type  || $('#type-cape').prop('checked') ? "cape" : "skin";

    if (files.length > 0) {
        let file = files[0];

        if (file.type === "image/png" || file.type === "image/x-png") {
            let reader = new FileReader();

            reader.onload = function (e) {
                let img = new Image();

                img.onload = () => {

                    (type == "skin") ? MSP.changeSkin(img.src) : MSP.changeCape(img.src);
                    let domTextureName = $('#name');
                    if (domTextureName.val() === '' || domTextureName.val() === domTextureName.attr('data-last-file-name')) {
                        const fileName = file.name.replace(/\.[Pp][Nn][Gg]$/, '');
                        domTextureName.attr('data-last-file-name', fileName);
                        domTextureName.val(fileName);
                    }
                };
                img.onerror = () => toastr.warning(trans('skinlib.fileExtError'));

                img.src = this.result;
            };
            reader.readAsDataURL(file);
        } else {
            toastr.warning(trans('skinlib.encodingError'));
        }
    }
};

function upload() {
    let form = new FormData();
    let file = $('#file').prop('files')[0];

    form.append('name',   $('#name').val());
    form.append('file',   file);
    form.append('public', ! $('#private').prop('checked'));

    if ($('#type-skin').prop('checked')) {
        form.append('type', $('#skin-type').val());
    } else if ($('#type-cape').prop('checked')) {
        form.append('type', 'cape');
    } else {
        return toastr.info(trans('skinlib.emptyTextureType'));
    }

    if (file === undefined) {
        toastr.info(trans('skinlib.emptyUploadFile'));
        $('#file').focus();
    } else if ($('#name').val() == "") {
        toastr.info(trans('skinlib.emptyTextureName'));
        $('#name').focus();
    } else if (file.type !== "image/png") {
        toastr.warning(trans('skinlib.fileExtError'));
        $('#file').focus();
    } else {
        $.ajax({
            type: "POST",
            url: url("skinlib/upload"),
            contentType: false,
            dataType: "json",
            data: form,
            processData: false,
            beforeSend: () => {
                $('#upload-button').html('<i class="fa fa-spinner fa-spin"></i> ' + trans('skinlib.uploading')).prop('disabled', 'disabled');
            },
            success: (json) => {
                if (json.errno == 0) {
                    let redirect = function () {
                        toastr.info(trans('skinlib.redirecting'));

                        window.setTimeout(() => {
                            window.location = url(`skinlib/show/${json.tid}`);
                        }, 1000);
                    };

                    // always redirect
                    swal({
                        type: 'success',
                        html: json.msg
                    }).then(redirect, redirect);

                } else {
                    swal({
                        type: 'warning',
                        html: json.msg
                    }).then(() => {
                        $('#upload-button').html(trans('skinlib.upload')).prop('disabled', '');
                    });
                }
            },
            error: (json) => {
                $('#upload-button').html(trans('skinlib.upload')).prop('disabled', '');
                showAjaxError(json);
            }
        });
    }
    return false;
}

function changeTextureName(tid, oldName) {
    swal({
        text: trans('skinlib.setNewTextureName'),
        input: 'text',
        inputValue: oldName,
        showCancelButton: true,
        inputValidator: (value) => {
            return new Promise((resolve, reject) => {
                (value) ? resolve() : reject(trans('skinlib.emptyNewTextureName'));
            });
        }
    }).then((new_name) => {
        $.ajax({
            type: "POST",
            url: url("skinlib/rename"),
            dataType: "json",
            data: { 'tid': tid, 'new_name': new_name },
            success: (json) => {
                if (json.errno == 0) {
                    $('#name').text(new_name);
                    toastr.success(json.msg);
                } else {
                    toastr.warning(json.msg);
                }
            },
            error: showAjaxError
        });
    });
}

$(document).on('click', '.private-label', function () {
    swal({
        text: trans('skinlib.setPublicNotice'),
        type: 'warning',
        showCancelButton: true
    }).then(() => {
        changePrivacy($(this).attr('tid'));
        $(this).remove();
    });
});

function changePrivacy(tid) {
    $.ajax({
        type: "POST",
        url: url(`skinlib/privacy`),
        dataType: "json",
        data: { 'tid': tid },
        success: (json) => {
            if (json.errno == 0) {
                toastr.success(json.msg);
                if (json.public == "0")
                    $('a:contains("' + trans('skinlib.setAsPrivate') + '")').html(trans('skinlib.setAsPublic'));
                else
                    $('a:contains("' + trans('skinlib.setAsPublic') + '")').html(trans('skinlib.setAsPrivate'));
            } else {
                toastr.warning(json.msg);
            }
        },
        error: showAjaxError
    });
}

function deleteTexture(tid) {
    swal({
        text: trans('skinlib.deleteNotice'),
        type: 'warning',
        showCancelButton: true
    }).then(function() {
        $.ajax({
            type: "POST",
            url: url("skinlib/delete"),
            dataType: "json",
            data: { 'tid': tid },
            success: (json) => {
                if (json.errno == 0) {
                    swal({
                        type: 'success',
                        html: json.msg
                    }).then(() => window.location = url('skinlib') );
                } else {
                    swal({
                        type: 'warning',
                        html: json.msg
                    });
                }
            },
            error: showAjaxError
        });
    });
}
