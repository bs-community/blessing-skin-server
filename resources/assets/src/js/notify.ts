/* eslint-disable max-params */
import $ from 'jquery'
import Swal from 'sweetalert2'
import toastr from 'toastr'
import { ModalOptions } from '../shims'
import { trans } from './i18n'

export function showAjaxError(error: Error): void {
  showModal(error.message.replace(/\n/g, '<br>'), trans('general.fatalError'), 'danger')
}

export function showModal(
  msg: string, title = 'Message',
  type = 'default',
  options: ModalOptions = {}
): void {
  const btnType = type === 'default' ? 'btn-primary' : 'btn-outline'
  const btnText = options.btnText || 'OK'
  const onClick = options.callback === undefined
    ? 'data-dismiss="modal"'
    : `onclick="${options.callback}"`
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
      destroyOnClose && $(this).remove()
    })
    // @ts-ignore
    .modal(options)
}

const swalInstance = Swal.mixin({
  confirmButtonText: trans('general.confirm'),
  cancelButtonText: trans('general.cancel'),
})

export function swal(options: import('sweetalert2').SweetAlertOptions) {
  return swalInstance.fire(options)
}

Object.assign(window, { toastr, swal })
Object.assign(blessing, { notify: { showModal } })
