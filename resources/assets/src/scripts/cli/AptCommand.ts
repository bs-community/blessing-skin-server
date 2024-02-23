import type {Stdio} from 'blessing-skin-shell';
import cac from 'cac';
import {install, remove} from './pluginManager';

export default async function apt(stdio: Stdio, arguments_: string[]) {
	const program = cac('apt');
	program.help();

	program
		.command('install <plugin>', 'install a new plugin')
		.action(async (plugin: string) => install(plugin, stdio));

	program
		.command('upgrade <plugin>', 'upgrade an existed plugin')
		.action(async (plugin: string) => install(plugin, stdio));

	program
		.command('remove <plugin>', 'remove a plugin')
		.action(async (plugin: string) => remove(plugin, stdio));

	program.parse(['', ''].concat(arguments_), {run: false});
	await program.runMatchedCommand();
}
