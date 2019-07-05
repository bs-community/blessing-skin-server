import fs from 'fs-extra'
import semver from 'semver'
import execa from 'execa'
import { version } from '../package.json'

const action = process.argv[2] as semver.ReleaseType
const increased = semver.inc(version, action)
if (!increased) {
  throw new Error('Invalid semver action.')
}
const next = increased!

async function saveVersion(path: string): Promise<void> {
  const content = await fs.readFile(path, 'utf-8')
  await fs.writeFile(path, content.replace(version, next!))
}

function git(args: readonly string[]) {
  return execa('git', args, { stdio: 'inherit' })
}

(async () => {
  const files = ['./config/app.php', './package.json']
  await Promise.all(files.map(saveVersion))
  await git(['add'].concat(files))
  await git(['commit', '-m', `Bump version to ${next}`])
  await git(['tag', '-a', next, '-m', next])
  await git(['checkout', 'master'])
  await git(['merge', 'dev'])
  await git(['push', '--all', '--follow-tags'])
  await git(['checkout', 'dev'])
})()
