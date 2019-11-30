import $ from 'jquery'
import 'bootstrap'
import Vue from 'vue'
import Modal from '../components/Modal.vue'

export interface ModalOptions {
  mode?: 'alert' | 'confirm' | 'prompt'
  title?: string
  text?: string
  dangerousHTML?: string
  input?: string
  placeholder?: string
  inputType?: string
  validator?(value: any): string | boolean | void
  type?: string
  showHeader?: boolean
  center?: boolean
  okButtonText?: string
  okButtonType?: string
  cancelButtonText?: string
  cancelButtonType?: string
  flexFooter?: boolean
}

export interface ModalResult {
  value: string
}

export function showModal(options: ModalOptions = {}): Promise<ModalResult> {
  return new Promise((resolve, reject) => {
    const container = document.createElement('div')
    document.body.appendChild(container)

    const instance = new Vue({
      render: h => h(Modal, {
        props: Object.assign({ center: true }, options),
        on: {
          confirm: resolve,
          dismiss: reject,
        },
      }),
    }).$mount(container)

    $(instance.$el).modal('show')
  })
}
