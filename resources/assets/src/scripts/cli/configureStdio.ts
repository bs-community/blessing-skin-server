import { Stdio } from 'blessing-skin-shell'
import * as event from '../event'

/* istanbul ignore next */
export function hackStdin() {
  if (process.env.NODE_ENV === 'test') {
    return process.stdin
  }

  // @ts-ignore
  return {
    on(eventName: string, handler: (str: string, key: string) => void) {
      if (eventName === 'keypress') {
        this._off = event.on('terminalKeyPress', (key: string) => {
          handler(key, key)
        })
      }
    },
    isTTY: true,
    setRawMode() {},
    removeListener() {
      this._off()
    },
  } as NodeJS.ReadStream & { _off(): void }
}

/* istanbul ignore next */
export function hackStdout(stdio: Stdio) {
  return {
    write(msg: string) {
      stdio.print(msg.replace(/\n/g, '\r\n'))
      return true
    },
  } as NodeJS.WriteStream
}
