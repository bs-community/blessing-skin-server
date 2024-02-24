import {Stdio} from './stdio';
import {Spinner} from '@/scripts/cli/Spinner';

test('run', async () => {
	vi.useRealTimers();

	const stdio = new Stdio();
	const spinner = new Spinner(stdio);

	spinner.start();

	await new Promise(resolve => setTimeout(resolve, 500));
	expect(stdio.getStdout().length).toBeGreaterThan(0);

	spinner.stop();
});
