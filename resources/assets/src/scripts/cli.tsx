import React, { useEffect, useRef } from 'react'
import ReactDOM from 'react-dom'
import { Terminal } from 'xterm'
import { FitAddon } from 'xterm-addon-fit'
import { Shell } from 'blessing-skin-shell'
import 'xterm/css/xterm.css'
import Draggable from 'react-draggable'
import ClosetCommand from './cli/ClosetCommand'
import RmCommand from './cli/RmCommand'
import styles from '@/styles/terminal.module.scss'

let launched = false

const TerminalWindow: React.FC<{ onClose(): void }> = (props) => {
  const mount = useRef<HTMLDivElement>(null)

  useEffect(() => {
    const el = mount.current
    if (!el) {
      return
    }

    const terminal = new Terminal()
    const fitAddon = new FitAddon()
    terminal.loadAddon(fitAddon)
    terminal.setOption(
      'fontFamily',
      'Monaco, Consolas, "Roboto Mono", "Noto Sans", "Droid Sans Mono"',
    )
    terminal.open(el)
    fitAddon.fit()

    const shell = new Shell(terminal)
    shell.addExternal('closet', ClosetCommand)
    shell.addExternal('rm', RmCommand)

    const unbind = terminal.onData((e) => shell.input(e))
    launched = true

    return () => {
      unbind.dispose()
      shell.free()
      fitAddon.dispose()
      terminal.dispose()
      launched = false
    }
  }, [])

  return (
    <Draggable handle=".card-header">
      <div className={`card ${styles.terminal}`}>
        <div className="card-header">
          <div className="d-flex justify-content-between">
            <h4 className="card-title mt-1">Blessing Skin Shell</h4>
            <button className="btn btn-default" onClick={props.onClose}>
              &times;
            </button>
          </div>
        </div>
        <div className={`card-body p-2 ${styles.body}`} ref={mount}></div>
      </div>
    </Draggable>
  )
}

export function launch() {
  if (launched) {
    return
  }

  const container = document.createElement('div')
  document.body.appendChild(container)

  const handleClose = () => {
    ReactDOM.unmountComponentAtNode(container)
    container.remove()
  }

  ReactDOM.render(<TerminalWindow onClose={handleClose} />, container)
}
