import $ from 'jquery';
import sweetalert2 from 'sweetalert2';
import 'toastr';
import { trans } from './i18n';

/**
 * Show message to div#msg with level
 *
 * @param  {string} msg
 * @param  {string} type
 * @return {void}
 */
export function showMsg(msg, type = 'info') {
    $('#msg')
        .removeClass()
        .addClass('callout')
        .addClass(`callout-${type}`)
        .html(msg);
}

/**
 * Show modal if error occured when sending an ajax request.
 *
 * @param  {TypeError | string} error
 * @return {void}
 */
export function showAjaxError(error) {
    if (!error) {
        return console.warn('Empty Ajax response body.');
    }

    const message = typeof error === 'string' ? error : error.message;
    showModal(message.replace(/\n/g, '<br>'), trans('general.fatalError'), 'danger');
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
export function showModal(msg, title = 'Message', type = 'default', options = {}) {
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

    $(dom).on('hidden.bs.modal', /* istanbul ignore next */ function () {
        destroyOnClose && $(this).remove();
    }).modal(options);
}

export const swal = sweetalert2.mixin({
    confirmButtonText: trans('general.confirm'),
    cancelButtonText: trans('general.cancel')
});
