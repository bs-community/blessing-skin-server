import type {Stdio} from 'blessing-skin-shell';
import cac from 'cac';
import * as fetch from '../net';

type Options = {
	force?: boolean;
	recursive?: boolean;
	help?: boolean;
};

export default async function rm(stdio: Stdio, arguments_: string[]) {
	const program = cac('rm');
	program.help();

	program
		.command('<file>')
		.option(
			'-f, --force',
			'ignore nonexistent files and arguments, never prompt',
		)
		.option(
			'-r, --recursive',
			'remove directories and their contents recursively',
		)
		.option('--no-preserve-root', 'do not treat \'/\' specially');

	const {options} = program.parse(['', ''].concat(arguments_), {
		run: false,
	});
	const path = program.args[0];

	if (!path && !options.help) {
		stdio.println('rm: missing operand');
		stdio.println('Try \'rm --help\' for more information.');
	}

	if (options.force && options.recursive && path?.startsWith('/')) {
		await fetch.post('/admin/resource?clear-cache');
	}
}
