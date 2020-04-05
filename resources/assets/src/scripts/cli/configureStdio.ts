import { Stdio } from 'blessing-skin-shell'
import type Commander from 'commander'
import * as event from '../event'

/* istanbul ignore next */
export function hackStdin() {
  if (process.env.NODE_ENV === 'test') {
    return process.stdin
  }

  // @ts-ignore
  return {
    on(eventName: string, handler: (key: string) => void) {
      if (eventName === 'keypress') {
        this._off = event.on('terminalKeyPress', (key: string) => {
          handler(key)
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

/* istanbul ignore next */
export function overrideExit(program: Commander.Command, stdio: Stdio) {
  Error.captureStackTrace = () => {}

  return program.exitOverride((error) => {
    if (!error.message.startsWith('(')) {
      stdio.print(error.message.replace(/\n/g, '\r\n'))
    }
  })
}
