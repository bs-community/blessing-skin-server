import type { Stdio } from 'blessing-skin-shell'
import { Command } from 'commander'
import * as fetch from '../net'
import { User, Texture } from '../types'
import { hackStdout, overrideExit } from './configureStdio'

type Response = fetch.ResponseBody<{ user: User; texture: Texture }>

export default async function closet(stdio: Stdio, args: string[]) {
  const program = new Command()

  /* istanbul ignore next */
  if (process.env.NODE_ENV !== 'test') {
    process.stdout = hackStdout(stdio)
    overrideExit(program, stdio)
  }

  program.name('closet').version('0.1.0')
  program
    .command('add <uid> <tid>')
    .description("add texture to someone's closet")
    .action(async (uid: string, tid: string) => {
      const { code, data } = await fetch.post<Response>(
        `/admin/closet/${uid}`,
        { tid },
      )
      if (code === 0) {
        const { texture, user } = data
        stdio.println(
          `Texture "${texture.name}" was added to user ${user.nickname}'s closet.`,
        )
      } else {
        stdio.println('Error occurred.')
      }
    })
  program
    .command('remove <uid> <tid>')
    .description("remove texture from someone's closet")
    .action(async (uid: string, tid: string) => {
      const { code, data } = await fetch.del<Response>(`/admin/closet/${uid}`, {
        tid,
      })
      if (code === 0) {
        const { texture, user } = data
        stdio.println(
          `Texture "${texture.name}" was removed from user ${user.nickname}'s closet.`,
        )
      } else {
        stdio.println('Error occurred.')
      }
    })

  await program.parseAsync(args, { from: 'user' })
}
