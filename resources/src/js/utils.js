/*
 * @Author: printempw
 * @Date:   2016-07-16 09:02:32
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-09-15 10:39:49
 */

$.locales = {};

var locale = {};

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

function loadLocales() {
    for (lang in $.locales) {
        if (!isEmpty($.locales[lang])) {
            locale = $.locales[lang] || {};
        }
    }
}

function trans(key) {
    if (isEmpty(locale)) {
        loadLocales();
    }

    var segments = key.split('.');
    var temp = locale || {};

    for (i in segments) {
        if (isEmpty(temp[segments[i]])) {
            return key;
        } else {
            temp = temp[segments[i]];
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

function showMsg(msg, type) {
    type  = type  === undefined ? "info" : type;
    $("[id=msg]").removeClass().addClass("callout").addClass('callout-'+type).html(msg);
}

function showAjaxError(json) {
    showModal(json.responseText.replace(/\n/g, '<br />'), 'Fatal Error（请联系作者）', 'danger');
}

function isMobile() {
    if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
        return true;
    }
    return false;
}

function getQueryString(key) {
    result = location.search.match(new RegExp('[\?\&]'+key+'=([^\&]+)','i'));

    if (result == null || result.length < 1){
        return "";
    } else {
        return result[1];
    }
}
