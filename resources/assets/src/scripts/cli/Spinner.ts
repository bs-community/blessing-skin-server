import type { Stdio } from 'blessing-skin-shell'
import spinners from 'cli-spinners/spinners.json'

const { dots } = spinners

export class Spinner {
  private timerId = 0
  private index = 0

  constructor(private stdio: Stdio) {}

  start(message = '') {
    this.timerId = window.setInterval(() => {
      this.index += 1
      this.index %= dots.frames.length

      this.stdio.reset()
      this.stdio.print(`${dots.frames[this.index]} ${message}`)
    }, dots.interval)
  }

  stop(message = '') {
    clearInterval(this.timerId)
    this.stdio.reset()
    this.stdio.println(message)
    this.stdio.print('\u001B[?25h')
  }
}
