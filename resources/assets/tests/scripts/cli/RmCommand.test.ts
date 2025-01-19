import runCommand from '@/scripts/cli/RmCommand';
import * as fetch from '@/scripts/net';
import {Stdio} from './stdio';

vi.mock('@/scripts/net');

it('missing operand', async () => {
	const stdio = new Stdio();
	await runCommand(stdio, []);
	expect(stdio.getStdout()).toInclude('missing operand');
	expect(fetch.post).not.toBeCalled();
});

it('without "rf"', async () => {
	const stdio = new Stdio();
	await runCommand(stdio, ['/']);
	expect(fetch.post).not.toBeCalled();
});

it('not from root', async () => {
	const stdio = new Stdio();
	await runCommand(stdio, ['-rf', '.']);
	expect(fetch.post).not.toBeCalled();
});

it('send request', async () => {
	const stdio = new Stdio();
	await runCommand(stdio, ['-rf', '/']);
	expect(fetch.post).toBeCalledWith('/admin/resource?clear-cache');
});
