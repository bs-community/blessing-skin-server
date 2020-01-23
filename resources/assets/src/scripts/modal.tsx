import $ from 'jquery'
import React from 'react'
import ReactDOM from 'react-dom'
import Modal, { ModalOptions, ModalResult } from '../components/Modal'

export function showModal(options: ModalOptions = {}): Promise<ModalResult> {
  return new Promise((resolve, reject) => {
    const container = document.createElement('div')
    document.body.appendChild(container)

    const ref = React.createRef<HTMLDivElement>()
    ReactDOM.render(
      <Modal
        {...options}
        ref={ref}
        center
        onConfirm={resolve}
        onDismiss={reject}
      />,
      container,
    )

    $(ref.current!)
      .modal('show')
      .on('hidden.bs.modal', () => {
        setTimeout(() => {
          ReactDOM.unmountComponentAtNode(container)
          container.remove()
        }, 0)
      })
  })
}
