/* eslint-disable max-params */
import $ from 'jquery'
import { ModalOptions } from '../shims'
import { trans } from './i18n'

export function showAjaxError(error: Error): void {
  showModal(error.message.replace(/\n/g, '<br>'), trans('general.fatalError'), 'danger')
}

export function showModal(
  message: string, title = 'Message',
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
    <div class="modal fade in">
      <div class="modal-dialog">
        <div class="modal-content bg-${type}">
          <div class="modal-header">
            <h4 class="modal-title">${title}</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">×</span>
            </button>
          </div>
          <div class="modal-body">
            <p>${message}</p>
          </div>
          <div class="modal-footer">
            <button type="button" ${onClick} class="btn btn-outline-light ${btnType}">
              ${btnText}
            </button>
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

Object.assign(blessing, { notify: { showModal } })
