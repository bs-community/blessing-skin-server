'use strict';

/**
 * Show message to div#msg with level
 *
 * @param  {string} msg
 * @param  {string} type
 * @return {void}
 */
function showMsg(msg, type = 'info') {
    $('[id=msg]').removeClass().addClass('callout').addClass(`callout-${type}`).html(msg);
}

/**
 * Show modal if error occured when sending an ajax request.
 *
 * @param  {object} json
 * @return {void}
 */
function showAjaxError(json) {
    if (typeof json === 'string') {
        return console.warn(json);
    }

    if (! json.responseText) {
        return console.warn('Empty Ajax response body.');
    }

    showModal(json.responseText.replace(/\n/g, '<br />'), trans('general.fatalError'), 'danger');
}

/**
 * Show a bootstrap modal.
 *
 * @param  {string} msg      Modal content
 * @param  {string} title    Modal title
 * @param  {string} type     Modal type, default|info|success|warning|error
 * @param  {object} options  All $.fn.modal options, plus { btnText, callback, destroyOnClose }
 * @return {void}
 */
function showModal(msg, title = 'Message', type = 'default', options = {}) {
    const btnType = (type !== 'default') ? 'btn-outline' : 'btn-primary';
    const btnText = options.btnText || 'OK';
    const onClick = (options.callback === undefined) ? 'data-dismiss="modal"' : `onclick="${options.callback}"`;
    const destroyOnClose = (options.destroyOnClose === false) ? false : true;

    const dom = `
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
                    <button type="button" ${onClick} class="btn ${btnType}">${btnText}</button>
                </div>
            </div>
        </div>
    </div>`;

    $(dom).on('hidden.bs.modal', function () {
        destroyOnClose && $(this).remove();
    }).modal(options);
}

if (process.env.NODE_ENV === 'test') {
    module.exports = {
        showMsg,
        showModal,
        showAjaxError,
    };
}
