import childProcess from 'child_process'
import util from 'util'
import fs from 'fs'

(async () => {
  const [manifest, commit]: [object & { commit: string }, string] = await Promise.all([
    util
      .promisify(fs.readFile)('./public/app/manifest.json', 'utf8')
      .then(JSON.parse),
    util
      .promisify(childProcess.exec)('git log --pretty=%H -1')
      .then(value => value.stdout.trim()),
  ])

  manifest.commit = commit
  await util.promisify(fs.writeFile)(
    './public/app/manifest.json',
    JSON.stringify(manifest, null, 2)
  )
})()
