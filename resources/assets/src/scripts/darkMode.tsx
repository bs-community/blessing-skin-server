import * as React from 'react'
import * as ReactDOM from 'react-dom'
import DarkModeButton from '@/components/DarkModeButton'

const el = document.querySelector('#toggle-dark-mode')
if (el) {
  const initMode = document.body.classList.contains('dark-mode')
  ReactDOM.render(<DarkModeButton initMode={initMode} />, el)
}
