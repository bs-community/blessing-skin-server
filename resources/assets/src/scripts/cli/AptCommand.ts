import type { Stdio } from 'blessing-skin-shell'
import { Command } from 'commander'
import { hackStdout, overrideExit } from './configureStdio'
import { install, remove } from './pluginManager'

export default async function apt(stdio: Stdio, args: string[]) {
  const program = new Command()

  /* istanbul ignore next */
  if (process.env.NODE_ENV !== 'test') {
    process.stdout = hackStdout(stdio)
    overrideExit(program, stdio)
  }

  program.name(apt.name)

  program
    .command('install <plugin>')
    .description('install a new plugin')
    .action((plugin: string) => install(plugin, stdio))

  program
    .command('upgrade <plugin>')
    .description('upgrade an existed plugin')
    .action((plugin: string) => install(plugin, stdio))

  program
    .command('remove <plugin>')
    .description('remove a plugin')
    .action((plugin: string) => remove(plugin, stdio))

  await program.parseAsync(args, { from: 'user' })
}
