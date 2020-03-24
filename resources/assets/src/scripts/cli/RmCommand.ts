import type { Stdio } from 'blessing-skin-shell'
import { Command } from 'commander'
import * as fetch from '../net'
import { hackStdout, overrideExit } from './configureStdio'

type Options = {
  force?: boolean
  recursive?: boolean
}

export default async function rm(stdio: Stdio, args: string[]) {
  const program = new Command()

  /* istanbul ignore next */
  if (process.env.NODE_ENV !== 'test') {
    process.stdout = hackStdout(stdio)
    overrideExit(program, stdio)
  }

  program
    .name('rm')
    .option(
      '-f, --force',
      'ignore nonexistent files and arguments, never prompt',
    )
    .option(
      '-r, --recursive',
      'remove directories and their contents recursively',
    )
    .option('--no-preserve-root', "do not treat '/' specially")
    .arguments('<file>')

  const opts: Options = program.parse(args, { from: 'user' }).opts()
  const path = program.args[0]

  if (!path) {
    stdio.println('rm: missing operand')
    stdio.println("Try 'rm --help' for more information.")
  }

  if (opts.force && opts.recursive && path.startsWith('/')) {
    await fetch.post('/admin/resource?clear-cache')
  }
}
