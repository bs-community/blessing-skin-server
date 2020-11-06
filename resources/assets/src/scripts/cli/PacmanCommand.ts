import type { Stdio } from 'blessing-skin-shell'
import cac from 'cac'
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

  const program = cac('pacman')
  program.help()

  program.option('-S, --sync <plugin>', 'install or upgrade a plugin')
  program.option('-R, --remove <plugin>', 'remove a plugin')

  const { options } = program.parse(['', ''].concat(args), { run: false })

  const opts: Options = options
  /* istanbul ignore else */
  if (opts.sync) {
    await install(opts.sync, stdio)
  } else if (opts.remove) {
    await remove(opts.remove, stdio)
  }
}
