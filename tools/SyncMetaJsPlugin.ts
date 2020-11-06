import { promises as fs } from 'fs'
import * as path from 'path'
import type { Compiler } from 'webpack'

class SyncMetaJsPlugin {
  apply(compiler: Compiler) {
    compiler.hooks.assetEmitted.tapPromise(
      'HtmlWebpackEnhancementPlugin',
      async (name, { source }) => {
        if (compiler.options.mode !== 'development') {
          return
        }

        if (name === 'meta.js' || name === 'sw.js') {
          const filePath = path.resolve(process.cwd(), 'public', name)

          await fs.writeFile(filePath, source.source())
        }
      },
    )
  }
}

export default SyncMetaJsPlugin
