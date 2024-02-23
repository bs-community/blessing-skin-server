import type {Stdio} from 'blessing-skin-shell';
import cac from 'cac';
import {install, remove} from './pluginManager';

type Options = {
	sync?: string;
	remove?: string;
};

export default async function pacman(stdio: Stdio, arguments_: string[]) {
	if (arguments_.length === 0) {
		stdio.println('error: no operation specified (use -h for help)');
		return;
	}

	const program = cac('pacman');
	program.help();

	program.option('-S, --sync <plugin>', 'install or upgrade a plugin');
	program.option('-R, --remove <plugin>', 'remove a plugin');

	const {options} = program.parse(['', ''].concat(arguments_), {run: false});

	const options_: Options = options;
	/* istanbul ignore else */
	if (options_.sync) {
		await install(options_.sync, stdio);
	} else if (options_.remove) {
		await remove(options_.remove, stdio);
	}
}
