import type { Stdio } from 'blessing-skin-shell'
import cac from 'cac'
import { install, remove } from './pluginManager'

export default async function dnf(stdio: Stdio, args: string[]) {
  const program = cac('dnf')
  program.help()

  program
    .command('install <plugin>', 'install a new plugin')
    .action((plugin: string) => install(plugin, stdio))

  program
    .command('upgrade <plugin>', 'upgrade an existed plugin')
    .action((plugin: string) => install(plugin, stdio))

  program
    .command('remove <plugin>', 'remove a plugin')
    .action((plugin: string) => remove(plugin, stdio))

  program.parse(['', ''].concat(args), { run: false })
  await program.runMatchedCommand()
}
