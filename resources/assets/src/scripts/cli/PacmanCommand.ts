import type { Stdio } from 'blessing-skin-shell'
import { Command } from 'commander'
import { hackStdout, overrideExit } from './configureStdio'
import { install, remove } from './pluginManager'

type Options = {
  sync?: string
  remove?: string
}

export default async function pacman(stdio: Stdio, args: string[]) {
  if (args.length === 0) {
    stdio.println('error: no operation specified (use -h for help)')
    return
  }

  const program = new Command()

  /* istanbul ignore next */
  if (process.env.NODE_ENV !== 'test') {
    process.stdout = hackStdout(stdio)
    overrideExit(program, stdio)
  }

  program.name(pacman.name)

  program
    .option('-S, --sync <plugin>')
    .description('install or upgrade a plugin')
  program.option('-R, --remove <plugin>').description('remove a plugin')

  program.parse(args, { from: 'user' })

  const opts: Options = program.opts()
  /* istanbul ignore else */
  if (opts.sync) {
    await install(opts.sync, stdio)
  } else if (opts.remove) {
    await remove(opts.remove, stdio)
  }
}
