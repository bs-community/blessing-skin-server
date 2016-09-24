/*
 * @Author: printempw
 * @Date:   2016-07-16 09:02:32
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-09-24 18:10:03
 */

$.locales = {};

var locale = {};

/**
 * Check if given value is empty.
 *
 * @param  {any}  obj
 * @return {Boolean}
 */
function isEmpty(obj) {

    // null and undefined are "empty"
    if (obj == null) return true;

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
            locale = $.locales[lang] || {};
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
function trans(key, parameters) {
    if (isEmpty(locale)) {
        loadLocales();
    }

    parameters = parameters || {};

    var segments = key.split('.');
    var temp = locale || {};

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

function showModal(msg, title, type, callback) {
    title = title === undefined ? "Messgae" : title;
    type  = type  === undefined ? "default" : type;
    callback = callback === undefined ? 'data-dismiss="modal"' : 'onclick="'+callback+'"';
    var btn_type = (type != "default") ? "btn-outline" : "btn-primary";
    var dom = '<div class="modal modal-'+type+' fade in"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button><h4 class="modal-title">'+title+'</h4></div><div class="modal-body"><p>'+msg+'</p></div><div class="modal-footer"><button type="button" '+callback+' class="btn '+btn_type+'">OK</button></div></div></div></div>';
    $(dom).modal();
}

/**
 * Show message to div#msg with level
 *
 * @param  {string} msg
 * @param  {string} type
 * @return {void}
 */
function showMsg(msg, type) {
    type = (type === undefined) ? "info" : type;
    $("[id=msg]").removeClass().addClass("callout").addClass('callout-'+type).html(msg);
}

/**
 * Show modal if error occured when sending an ajax request.
 *
 * @param  {object} json
 * @return {void}
 */
function showAjaxError(json) {
    showModal(json.responseText.replace(/\n/g, '<br />'), trans('utils.fatalError'), 'danger');
}

/**
 * Check if current environment is mobile.
 *
 * @return {Boolean}
 */
function isMobile() {
    if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
        return true;
    }
    return false;
}

/**
 * Get parameters in query string with key.
 *
 * @param  {string} key
 * @return {string}
 */
function getQueryString(key) {
    result = location.search.match(new RegExp('[\?\&]'+key+'=([^\&]+)','i'));

    if (result == null || result.length < 1){
        return "";
    } else {
        return result[1];
    }
}
