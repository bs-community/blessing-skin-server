import {spawnSync} from 'node:child_process';
import * as fs from 'node:fs';
import ts from 'typescript';
import prettier from 'prettier';

type Route = {uri: string; name: string | undefined};
const supportedPrefixes = ['auth.', 'user.', 'skinlib.', 'texture.', 'admin.'];

type TreeObject = Record<string, Tree>;
type Tree = TreeObject | string;
const tree: TreeObject = {};

function parseURI(uri: string): ts.ArrowFunction {
	const matches = /{([a-z]+)}/.exec(uri);
	if (matches?.[0] && matches?.[1]) {
		const parameter = matches[1];
		const type
      = parameter.endsWith('id')
      || parameter === 'texture'
      || parameter === 'user'
      || parameter === 'player'
      || parameter === 'report'
      	? ts.factory.createKeywordTypeNode(ts.SyntaxKind.NumberKeyword)
      	: ts.factory.createKeywordTypeNode(ts.SyntaxKind.StringKeyword);

		return ts.factory.createArrowFunction(
			undefined,
			undefined,
			[
				ts.factory.createParameterDeclaration(
					undefined,
					undefined,
					ts.factory.createIdentifier(parameter),
					undefined,
					type,
				),
			],
			undefined,
			ts.factory.createToken(ts.SyntaxKind.EqualsGreaterThanToken),
			ts.factory.createTemplateExpression(
				ts.factory.createTemplateHead(
					'/' + uri.slice(0, matches.index),
					'/' + uri.slice(0, matches.index),
				),
				[
					ts.factory.createTemplateSpan(
						ts.factory.createIdentifier(parameter),
						ts.factory.createTemplateTail(
							uri.slice(matches.index + matches[0].length),
							uri.slice(matches.index + matches[0].length),
						),
					),
				],
			),
		);
	}

	return ts.factory.createArrowFunction(
		undefined,
		undefined,
		[],
		undefined,
		ts.factory.createToken(ts.SyntaxKind.EqualsGreaterThanToken),
		ts.factory.createAsExpression(
			ts.factory.createStringLiteral(`/${uri}`),
			ts.factory.createTypeReferenceNode(ts.factory.createIdentifier('const'), undefined),
		),
	);
}

function parseTree(tree: Tree): ts.ObjectLiteralExpression {
	const properties = Object.entries(tree)
		.sort(([a], [b]) => (a > b ? 1 : -1))
		.map(([key, value]) => {
			if (typeof value === 'string') {
				return ts.factory.createPropertyAssignment(
					ts.factory.createIdentifier(key),
					parseURI(value),
				);
			}

			return ts.factory.createPropertyAssignment(
				ts.factory.createIdentifier(key),
				parseTree(value),
			);
		});

	return ts.factory.createObjectLiteralExpression(properties);
}

const {stdout} = spawnSync(
	'php',
	['artisan', 'route:list', '--json', '--columns=uri,name'],
	{encoding: 'utf-8'},
);
let routes: Route[] = [];
try {
	routes = JSON.parse(stdout);
} catch (error) {
	console.error(stdout);
	throw error;
}

for (const route of routes
	.filter(
		route =>
			route.name
      && supportedPrefixes.some(prefix => route.name!.startsWith(prefix))
      && !route.name.endsWith('.'),
	)) {
	const path = route.name!.split('.');
	const {length} = path;

	path.reduce((object: TreeObject, p, index) => {
		if (index === length - 1) {
			object[p] = route.uri;
			return tree;
		}

		object[p] ||= {};

		return object[p];
	}, tree);
}

const ast = ts.factory.createExportAssignment(
	undefined,
	undefined,
	parseTree(tree),
);
const sourceFile = ts.createSourceFile(
	'urls.ts',
	'',
	ts.ScriptTarget.ES2020,
	false,
	ts.ScriptKind.TS,
);
const printer = ts.createPrinter({
	newLine: ts.NewLineKind.LineFeed,
});
const code = await prettier.format(
	printer.printNode(ts.EmitHint.Unspecified, ast, sourceFile),
	{
		parser: 'typescript',
		semi: false,
		singleQuote: true,
	},
);
fs.writeFileSync('./resources/assets/src/scripts/urls.ts', code, {
	encoding: 'utf-8',
});
