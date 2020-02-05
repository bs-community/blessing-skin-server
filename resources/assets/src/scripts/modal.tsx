import React from 'react'
import ReactDOM from 'react-dom'
import Modal, { ModalOptions, ModalResult } from '../components/Modal'

export function showModal(options: ModalOptions = {}): Promise<ModalResult> {
  return new Promise((resolve, reject) => {
    const container = document.createElement('div')
    document.body.appendChild(container)

    const handleClose = () => {
      ReactDOM.unmountComponentAtNode(container)
      document.body.removeChild(container)
    }

    ReactDOM.render(
      <Modal
        {...options}
        show
        center
        onConfirm={resolve}
        onDismiss={reject}
        onClose={handleClose}
      />,
      container,
    )
  })
}
