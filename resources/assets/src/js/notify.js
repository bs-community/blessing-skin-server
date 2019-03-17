/* eslint-disable max-params */
import $ from 'jquery'
import Swal from 'sweetalert2'
import toastr from 'toastr'
import { trans } from './i18n'

/**
 * Show modal if error occured when sending an ajax request.
 *
 * @param  {Error} error
 * @return {void}
 */
export function showAjaxError(error) {
  showModal(error.message.replace(/\n/g, '<br>'), trans('general.fatalError'), 'danger')
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
  const btnType = type === 'default' ? 'btn-primary' : 'btn-outline'
  const btnText = options.btnText || 'OK'
  const onClick = options.callback === undefined ? 'data-dismiss="modal"' : `onclick="${options.callback}"`
  const destroyOnClose = options.destroyOnClose !== false

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
    </div>`

  $(dom)
    .on('hidden.bs.modal', /* istanbul ignore next */ function modal() {
    // eslint-disable-next-line no-invalid-this
      destroyOnClose && $(this).remove()
    })
    .modal(options)
}

const swalInstance = Swal.mixin({
  confirmButtonText: trans('general.confirm'),
  cancelButtonText: trans('general.cancel'),
})

/**
 * @param {import('sweetalert2').SweetAlertOptions} options
 */
export function swal(options) {
  return swalInstance.fire(options)
}

window.toastr = toastr
window.swal = swal
blessing.notify = { showModal }
