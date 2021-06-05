import { spawnSync } from 'child_process'
import * as fs from 'fs'
import ts from 'typescript'
import prettier from 'prettier'

type Route = { uri: string; name: string | null }
const supportedPrefixes = ['auth.', 'user.', 'skinlib.', 'texture.', 'admin.']

type TreeObject = { [key: string]: Tree }
type Tree = TreeObject | string
const tree: TreeObject = {}

function parseURI(uri: string): ts.ArrowFunction {
  const matches = /\{([a-z]+)\}/.exec(uri)
  if (matches?.[0] && matches?.[1]) {
    const param = matches[1]
    const type =
      param.endsWith('id') ||
      param === 'texture' ||
      param === 'user' ||
      param === 'player' ||
      param === 'report'
        ? ts.createKeywordTypeNode(ts.SyntaxKind.NumberKeyword)
        : ts.createKeywordTypeNode(ts.SyntaxKind.StringKeyword)

    return ts.createArrowFunction(
      undefined,
      undefined,
      [
        ts.createParameter(
          undefined,
          undefined,
          undefined,
          ts.createIdentifier(param),
          undefined,
          type,
          undefined,
        ),
      ],
      undefined,
      ts.createToken(ts.SyntaxKind.EqualsGreaterThanToken),
      ts.createTemplateExpression(
        ts.createTemplateHead(
          '/' + uri.slice(0, matches.index),
          '/' + uri.slice(0, matches.index),
        ),
        [
          ts.createTemplateSpan(
            ts.createIdentifier(param),
            ts.createTemplateTail(
              uri.slice(matches.index + matches[0].length),
              uri.slice(matches.index + matches[0].length),
            ),
          ),
        ],
      ),
    )
  }

  return ts.createArrowFunction(
    undefined,
    undefined,
    [],
    undefined,
    ts.createToken(ts.SyntaxKind.EqualsGreaterThanToken),
    ts.createAsExpression(
      ts.createStringLiteral(`/${uri}`),
      ts.createTypeReferenceNode(ts.createIdentifier('const'), undefined),
    ),
  )
}

function parseTree(tree: Tree): ts.ObjectLiteralExpression {
  const properties = Object.entries(tree)
    .sort(([a], [b]) => (a > b ? 1 : -1))
    .map(([key, value]) => {
      if (typeof value === 'string') {
        return ts.createPropertyAssignment(
          ts.createIdentifier(key),
          parseURI(value),
        )
      } else {
        return ts.createPropertyAssignment(
          ts.createIdentifier(key),
          parseTree(value),
        )
      }
    })

  return ts.createObjectLiteral(properties)
}

const { stdout } = spawnSync(
  'php',
  ['artisan', 'route:list', '--json', '--columns=uri,name'],
  { encoding: 'utf-8' },
)
let routes: Route[] = []
try {
  routes = JSON.parse(stdout)
} catch (e) {
  console.error(stdout)
  throw e
}
routes
  .filter(
    (route) =>
      route.name &&
      supportedPrefixes.some((prefix) => route.name!.startsWith(prefix)) &&
      !route.name.endsWith('.'),
  )
  .forEach((route) => {
    const path = route.name!.split('.')
    const length = path.length

    path.reduce((object: TreeObject, p, index) => {
      if (index === length - 1) {
        object[p] = route.uri
        return tree
      }

      if (!object[p]) {
        object[p] = {}
      }
      return object[p] as TreeObject
    }, tree)
  })

const ast = ts.createExportAssignment(
  undefined,
  undefined,
  undefined,
  parseTree(tree),
)
const sourceFile = ts.createSourceFile(
  'urls.ts',
  '',
  ts.ScriptTarget.ES2017,
  false,
  ts.ScriptKind.TS,
)
const printer = ts.createPrinter({
  newLine: ts.NewLineKind.LineFeed,
})
const code = prettier.format(
  printer.printNode(ts.EmitHint.Unspecified, ast, sourceFile),
  {
    parser: 'typescript',
    semi: false,
    singleQuote: true,
  },
)
fs.writeFileSync('./resources/assets/src/scripts/urls.ts', code, {
  encoding: 'utf-8',
})
