import type {Stdio} from 'blessing-skin-shell';
import * as event from '../event';

export function hackStdin() {
	if (process.env.NODE_ENV === 'test') {
		return process.stdin;
	}

	// @ts-expect-error
	return {
		on(eventName: string, handler: (string_: string, key: string) => void) {
			if (eventName === 'keypress') {
				this._off = event.on('terminalKeyPress', (key: string) => {
					handler(key, key);
				});
			}
		},
		isTTY: true,
		setRawMode() {},
		removeListener() {
			this._off();
		},
	} as NodeJS.ReadStream & {_off(): void};
}

export function hackStdout(stdio: Stdio) {
	return {
		write(message: string) {
			stdio.print(message.replaceAll('\n', '\r\n'));
			return true;
		},
	} as NodeJS.WriteStream;
}
