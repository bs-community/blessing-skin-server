import type { Stdio } from 'blessing-skin-shell'
import cac from 'cac'
import * as fetch from '../net'

type Options = {
  force?: boolean
  recursive?: boolean
  help?: boolean
}

export default async function rm(stdio: Stdio, args: string[]) {
  const program = cac('rm')
  program.help()

  program
    .command('<file>')
    .option(
      '-f, --force',
      'ignore nonexistent files and arguments, never prompt',
    )
    .option(
      '-r, --recursive',
      'remove directories and their contents recursively',
    )
    .option('--no-preserve-root', "do not treat '/' specially")

  const opts: Options = program.parse(['', ''].concat(args), {
    run: false,
  }).options
  const path = program.args[0]

  if (!path && !opts.help) {
    stdio.println('rm: missing operand')
    stdio.println("Try 'rm --help' for more information.")
  }

  if (opts.force && opts.recursive && path?.startsWith('/')) {
    await fetch.post('/admin/resource?clear-cache')
  }
}
