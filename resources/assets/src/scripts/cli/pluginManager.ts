import type { Stdio } from 'blessing-skin-shell'
import prompts from 'prompts'
import * as fetch from '../net'
import { hackStdout, hackStdin } from './configureStdio'
import { Spinner } from './Spinner'

export async function install(plugin: string, stdio: Stdio) {
  const spinner = new Spinner(stdio)
  spinner.start('Installing plugin...')

  const { message, data } = await fetch.post<
    fetch.ResponseBody<{ reason?: string[] } | undefined>
  >('/admin/plugins/market/download', { name: plugin })

  spinner.stop(`  ${message}`)
  const reasons = data?.reason
  if (reasons) {
    stdio.println(reasons.map((reason) => `- ${reason}`).join('\r\n'))
  }
}

export async function remove(plugin: string, stdio: Stdio) {
  const { confirm }: { confirm: boolean } = await prompts({
    name: 'confirm',
    type: 'confirm',
    message: `Are you sure to remove plugin "${plugin}"?`,
    stdin: hackStdin(),
    stdout: hackStdout(stdio),
  })

  if (!confirm) {
    return
  }

  const spinner = new Spinner(stdio)
  spinner.start('Uninstalling plugin...')

  const { message } = await fetch.post<fetch.ResponseBody>(
    '/admin/plugins/manage',
    { action: 'delete', name: plugin },
  )
  spinner.stop(`  ${message}`)
}
