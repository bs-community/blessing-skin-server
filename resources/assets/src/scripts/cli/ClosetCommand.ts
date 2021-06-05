import type { Stdio } from 'blessing-skin-shell'
import cac from 'cac'
import * as fetch from '../net'
import type { User, Texture } from '../types'

type Response = fetch.ResponseBody<{ user: User; texture: Texture }>

export default async function closet(stdio: Stdio, args: string[]) {
  const program = cac('closet')
  program.help()

  program
    .command('add <uid> <tid>', "add texture to someone's closet")
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
    .command('remove <uid> <tid>', "remove texture from someone's closet")
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

  program.parse(['', ''].concat(args), { run: false })
  await program.runMatchedCommand()
}
