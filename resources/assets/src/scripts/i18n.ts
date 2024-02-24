type I18nTable = {
	[key: string]: string | I18nTable | undefined;
};

export function t(key: string, parameters: Record<string, string> = Object.create(null)): string {
	const segments = key.split('.');
	let temporary = blessing.i18n as I18nTable | undefined;
	let result = '';

	for (const segment of segments) {
		const middle = temporary?.[segment];
		if (!middle) {
			return key;
		}

		if (typeof middle === 'string') {
			result = middle;
		} else {
			temporary = middle;
		}
	}

	for (const slot of Object.keys(parameters)) {
		(result = result.replace(`:${slot}`, parameters[slot] ?? `%{${slot}}`));
	}

	return result;
}

Object.assign(window, {trans: t});
Object.assign(blessing, {t});
