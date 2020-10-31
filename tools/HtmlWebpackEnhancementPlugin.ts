import { promises as fs } from 'fs'
import * as path from 'path'
import HtmlWebpackPlugin from 'html-webpack-plugin'
import type { Compiler } from 'webpack'
import type { Options } from 'html-webpack-plugin'

class HtmlWebpackEnhancementPlugin {
  apply(compiler: Compiler) {
    compiler.hooks.compilation.tap(
      'HtmlWebpackEnhancementPlugin',
      (compilation) => {
        const hooks = HtmlWebpackPlugin.getHooks(compilation)

        hooks.beforeAssetTagGeneration.tap(
          'HtmlWebpackEnhancementPlugin',
          (data) => {
            // @ts-ignore
            const options: Options = data.plugin.options
            const entries = (options.templateParameters ?? []) as string[]
            data.assets.js = data.assets.js.filter((name) =>
              entries.some((entry) =>
                new RegExp(`^${entry}\\.`).test(path.basename(name, 'js')),
              ),
            )
            data.assets.css = data.assets.css.filter((name) =>
              entries.some((entry) =>
                new RegExp(`^${entry}\\.`).test(path.basename(name, 'css')),
              ),
            )
            return data
          },
        )

        hooks.alterAssetTags.tap('HtmlWebpackEnhancementPlugin', (data) => {
          data.assetTags.scripts = data.assetTags.scripts.map((tag) => {
            tag.attributes.crossorigin = 'anonymous'
            return tag
          })
          data.assetTags.styles = data.assetTags.styles.map((tag) => {
            tag.attributes.crossorigin = 'anonymous'
            return tag
          })

          return data
        })

        hooks.afterTemplateExecution.tap(
          'HtmlWebpackEnhancementPlugin',
          (data) => {
            if (
              compilation.compiler.options.mode === 'production' &&
              data.headTags.length > 0
            ) {
              data.bodyTags = data.headTags
              data.headTags = []
            }

            return data
          },
        )

        hooks.beforeEmit.tapPromise(
          'HtmlWebpackEnhancementPlugin',
          async (data) => {
            const filePath = path.resolve(
              process.cwd(),
              'resources',
              'views',
              'assets',
              data.outputName,
            )
            await fs.writeFile(filePath, data.html)

            return data
          },
        )
      },
    )
  }
}

export default HtmlWebpackEnhancementPlugin
