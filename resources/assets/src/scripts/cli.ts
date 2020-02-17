import { Terminal } from 'xterm'
import { FitAddon } from 'xterm-addon-fit'
import { Shell } from 'blessing-skin-shell'
import 'xterm/css/xterm.css'
import styles from '@/styles/terminal.scss'

let launched = false

export function launch() {
  if (launched) {
    return
  }

  const card = document.createElement('div')
  card.className = `card ${styles.terminal}`
  const header = document.createElement('div')
  header.className = 'card-header'
  const headerStuff = document.createElement('div')
  headerStuff.className = 'd-flex justify-content-between'
  const title = document.createElement('h4')
  title.className = 'card-title mt-1'
  title.textContent = 'Blessing Skin Shell'
  headerStuff.appendChild(title)
  const btnClose = document.createElement('button')
  btnClose.innerHTML = '&times;'
  btnClose.className = 'btn btn-default'
  headerStuff.appendChild(btnClose)
  header.appendChild(headerStuff)
  card.appendChild(header)

  const body = document.createElement('div')
  body.className = 'card-body p-2'
  body.style.backgroundColor = '#000'
  const container = document.createElement('div')
  body.appendChild(container)
  card.appendChild(body)
  document.body.appendChild(card)

  const terminal = new Terminal()
  const fitAddon = new FitAddon()
  terminal.loadAddon(fitAddon)
  terminal.setOption('fontFamily', 'Monaco, Consolas, "Roboto Mono", "Noto Sans", "Droid Sans Mono"')
  terminal.open(container)
  fitAddon.fit()

  const shell = new Shell(terminal)
  const unbind = terminal.onData(e => shell.input(e))
  launched = true

  const handleClose = () => {
    unbind.dispose()
    shell.free()
    fitAddon.dispose()
    terminal.dispose()
    btnClose.removeEventListener('click', handleClose)
    card.remove()
    launched = false
  }
  btnClose.addEventListener('click', handleClose)
}
