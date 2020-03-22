import { Stdio } from 'blessing-skin-shell'
import type Commander from 'commander'

/* istanbul ignore next */
export function hackStdout(stdio: Stdio) {
  return {
    write(msg: string) {
      stdio.print(msg.replace(/\n/g, '\r\n'))
    },
  } as NodeJS.WriteStream
}

/* istanbul ignore next */
export function overrideExit(program: Commander.Command, stdio: Stdio) {
  Error.captureStackTrace = () => {}

  return program.exitOverride(error => {
    if (!error.message.startsWith('(')) {
      stdio.print(error.message.replace(/\n/g, '\r\n'))
    }
  })
}
