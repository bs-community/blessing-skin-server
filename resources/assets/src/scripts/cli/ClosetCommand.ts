import type { Stdio } from 'blessing-skin-shell'
import * as fetch from '../net'
import { User, Texture } from '../types'

type SubCommand = 'add' | 'remove'

type Response = fetch.ResponseBody<{ user: User; texture: Texture }>

export default async function closet(stdio: Stdio, args: string[]) {
  if (args.includes('-h') || args.includes('--help')) {
    stdio.println('Usage: closet <add|remove> <uid> <tid>')
    return
  }

  const command = args[0] as SubCommand | undefined
  const uid = args[1]
  const tid = args[2]

  if (!command) {
    stdio.println('Supported subcommand: add, remove.')
    return
  }
  if (!uid) {
    stdio.println('User ID must be provided.')
    return
  }
  if (!tid) {
    stdio.println('Texture ID must be provided.')
    return
  }

  switch (command) {
    case 'add': {
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
      break
    }
    case 'remove': {
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
      break
    }
  }
}
