'use strict';

console.log(`\n %c Blessing Skin v${blessing.version} %c https://blessing.studio \n\n`,"color: #fadfa3; background: #030307; padding:5px 0;","background: #fadfa3; padding:5px 0;");

$.locales       = {};
$.currentLocale = {};

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

// polyfill of String.prototype.includes
if (!String.prototype.includes) {
    String.prototype.includes = function(search, start) {
        'use strict';
        if (typeof start !== 'number') {
            start = 0;
        }

        if (start + search.length > this.length) {
            return false;
        } else {
            return this.indexOf(search, start) !== -1;
        }
    };
}

// polyfill of String.prototype.endsWith
if (!String.prototype.endsWith) {
    String.prototype.endsWith = function (searchString, position) {
        var subjectString = this.toString();
        if (typeof position !== 'number' || !isFinite(position) || Math.floor(position) !== position || position > subjectString.length) {
            position = subjectString.length;
        }
        position -= searchString.length;
        var lastIndex = subjectString.lastIndexOf(searchString, position);
        return lastIndex !== -1 && lastIndex === position;
    };
}

$(window).ready(activateLayout).resize(activateLayout);

function activateLayout() {
    if (location.pathname == "/" || location.pathname.includes('auth'))
        return;

    $.AdminLTE.layout.activate();
}

/**
 * Check if given value is empty.
 *
 * @param  {any}  obj
 * @return {Boolean}
 */
function isEmpty(obj) {

    // null and undefined are "empty"
    if (obj == null) return true;

    if (typeof (obj) == 'number' || typeof (obj) == 'boolean') return false;

    // Assume if it has a length property with a non-zero value
    // that that property is correct.
    if (obj.length > 0)    return false;
    if (obj.length === 0)  return true;

    // If it isn't an object at this point
    // it is empty, but it can't be anything *but* empty
    // Is it empty?  Depends on your application.
    if (typeof obj !== "object") return true;

    // Otherwise, does it have any properties of its own?
    // Note that this doesn't handle
    // toString and valueOf enumeration bugs in IE < 9
    for (var key in obj) {
        if (hasOwnProperty.call(obj, key)) return false;
    }

    return true;
}

/**
 * Load current selected language.
 *
 * @return void
 */
function loadLocales() {
    for (lang in $.locales) {
        if (!isEmpty($.locales[lang])) {
            $.currentLocale = $.locales[lang] || {};
        }
    }
}

/**
 * Translate according to given key.
 *
 * @param  {string} key
 * @param  {dict}   parameters
 * @return {string}
 */
function trans(key, parameters = {}) {
    if (isEmpty($.currentLocale)) {
        loadLocales();
    }

    let segments = key.split('.');
    let temp = $.currentLocale || {};

    for (i in segments) {
        if (isEmpty(temp[segments[i]])) {
            return key;
        } else {
            temp = temp[segments[i]];
        }
    }

    for (i in parameters) {
        if (!isEmpty(parameters[i])) {
            temp = temp.replace(':'+i, parameters[i]);
        }
    }

    return temp;
}

function showModal(msg, title = 'Message', type = 'default', options = {}) {
    let btnType = (type != "default") ? "btn-outline" : "btn-primary";
    let onClick = (options.callback === undefined) ? 'data-dismiss="modal"' : `onclick="${options.callback}"`;

    let dom = `
    <div class="modal modal-${type} fade in">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h4 class="modal-title">${title}</h4>
                </div>
                <div class="modal-body">
                    <p>${msg}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" ${onClick} class="btn ${btnType}">OK</button>
                </div>
            </div>
        </div>
    </div>`;

    $(dom).modal(options);
}

/**
 * Show message to div#msg with level
 *
 * @param  {string} msg
 * @param  {string} type
 * @return {void}
 */
function showMsg(msg, type = 'info') {
    $("[id=msg]").removeClass().addClass("callout").addClass('callout-'+type).html(msg);
}

/**
 * Show modal if error occured when sending an ajax request.
 *
 * @param  {object} json
 * @return {void}
 */
function showAjaxError(json) {
    if (!json.responseText) {
        console.warn('Empty Ajax response body.');
        return;
    }

    showModal(json.responseText.replace(/\n/g, '<br />'), trans('general.fatalError'), 'danger');
}

/**
 * Get parameters in query string with key.
 *
 * @param  {string} key
 * @param  {string} defaultValue
 * @return {string}
 */
function getQueryString(key, defaultValue) {
    result = location.search.match(new RegExp('[\?\&]'+key+'=([^\&]+)','i'));

    if (result == null || result.length < 1){
        return defaultValue;
    } else {
        return result[1];
    }
}

/**
 * Return a debounced function
 *
 * @param {Function} func
 * @param {number}   delay
 * @param {Array}    args
 * @param {Object}   context
 */
function debounce(func, delay, args = [], context = undefined) {
    if (isNaN(delay) || typeof func !== 'function') {
        throw new Error('Arguments type of function "debounce" is incorrent!');
    }

    let timer = null;
    return function () {
        clearTimeout(timer);
        timer = setTimeout(() => {
            func.apply(context, args);
        }, delay);
    }
}

function url(relativeUri) {
    relativeUri       = relativeUri || "";
    blessing.base_url = blessing.base_url || "";

    if (relativeUri[0] != "/") {
        relativeUri = "/" + relativeUri;
    }

    return blessing.base_url + relativeUri;
}

function confirmLogout() {
    swal({
        text: trans('general.confirmLogout'),
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: trans('general.confirm'),
        cancelButtonText: trans('general.cancel')
    }).then(() => {
        logout().then((json) => {
            swal({
                type: 'success',
                html: json.msg
            });
            window.setTimeout(() => window.location = url(), 1000);
        });
    });
}

function logout() {
    return Promise.resolve($.ajax({
        type: "POST",
        url: url('auth/logout'),
        dataType: "json"
    }));
}

$('#logout-button').click(() => confirmLogout());

$(document).ready(() => $('li.active > ul').show());

var TexturePreview = function (type, tid, preference) {
    this.tid               = tid;
    this.type              = type;
    this.selector          = $('#' + type);
    this.preference        = type == 'steve' ? 'default' : 'slim';
    this.playerPreference  = preference;

    this.change2dPreview = function () {
        this.selector
            .attr('src', url(`preview/200/${this.tid}.png`))
            .show()
            .parent().attr('href', url('skinlib/show/' + this.tid))
            .next().hide();

        return this;
    }

    this.change3dPreview = function () {

        if (this.playerPreference == this.preference || this.type == 'cape') {
            $.ajax({
                type: "GET",
                url: url(`skinlib/info/${this.tid}`),
                dataType: "json",
                success: (json) => {
                    let textureUrl = url('textures/' + json.hash);

                    if (this.type == 'cape') {
                        MSP.changeCape(textureUrl);
                    } else {
                        MSP.changeSkin(textureUrl);
                    }
                },
                error: (json) => showAjaxError(json)
            });
        }

        return this;
    }

    this.showNotUploaded = function () {
        this.selector.hide().parent().next().show();

        // clear 3D preview of cape
        if (this.type == 'cape') {
            MSP.changeCape('');
        }

        return this;
    }
}

TexturePreview.previewType = '3D';

TexturePreview.init3dPreview = () => {
    if (TexturePreview.previewType == '2D') return;

    $('#preview-2d').hide();

    if ($(window).width() < 800) {
        var canvas = MSP.get3dSkinCanvas($('#skinpreview').width(), $('#skinpreview').width());
        $("#skinpreview").append($(canvas).prop("id", "canvas3d"));
    } else {
        var canvas = MSP.get3dSkinCanvas(350, 350);
        $("#skinpreview").append($(canvas).prop("id", "canvas3d"));
    }
}

TexturePreview.show3dPreview = () => {
    TexturePreview.previewType = "3D";

    TexturePreview.init3dPreview();
    $('#preview-2d').hide();
    $('.operations').show();
    $('#preview-switch').html(trans('user.switch2dPreview'));
}

TexturePreview.show2dPreview = () => {
    TexturePreview.previewType = '2D';

    $('#canvas3d').remove();
    $('.operations').hide();
    $('#preview-2d').show();
    $('#preview-switch').html(trans('user.switch3dPreview')).attr('onclick', 'show3dPreview();');
}

// change 3D preview status
$('.fa-pause').click(function () {
    MSP.setStatus('rotation',  ! MSP.getStatus('rotation'));
    MSP.setStatus('movements', ! MSP.getStatus('movements'));

    $(this).toggleClass('fa-pause').toggleClass('fa-play');
});

$('.fa-forward').click(() => MSP.setStatus('running',  !MSP.getStatus('running')) );
$('.fa-repeat' ).click(() => MSP.setStatus('rotation', !MSP.getStatus('rotation')) );

(function ($) {
    if ($('#copyright-text').length != 0 && $('#copyright-text').text().indexOf('Blessing') >= 0) {
        return;
    }

    $.ajax({
        type: 'POST',
        url: 'https://work.prinzeugen.net/statistics/whitelist',
        dataType: 'json',
        data: { site_name: blessing.site_name, site_url: blessing.base_url }
    }).done((json) => {
        if (!json.inWhiteList) {
            // :(
            showModal("It looks like that you have removed the program's copyright. It's better for you to restore it ASAP, otherwise something terrible will be applied to your site :)<br><br>If there is a false alarm, please send a mail to h@prinzeugen.net to correct it.", 'CMN BAD ASS', 'danger', {
                'backdrop': 'static',
                'keyboard': false
            });
        }
    });
})(jQuery);
