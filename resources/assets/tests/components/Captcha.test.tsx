import Captcha from '@/components/Captcha';
import {t} from '@/scripts/i18n';
import {fireEvent, render} from '@testing-library/react';
import React from 'react';
import Reaptcha from 'reaptcha';
import {expect, it, vi} from 'vitest';

describe('picture captcha', () => {
	it('retrieve value', async () => {
		const reference = React.createRef<Captcha>();
		const {getByPlaceholderText} = render(<Captcha ref={reference}/>);

		fireEvent.input(getByPlaceholderText(t('auth.captcha')), {
			target: {value: 'abc'},
		});
		expect(await reference.current?.execute()).toBe('abc');
	});

	it('refresh on click', () => {
		const spy = vi.spyOn(Date, 'now');

		const reference = React.createRef<Captcha>();
		const {getByAltText} = render(<Captcha ref={reference}/>);

		fireEvent.click(getByAltText(t('auth.captcha')));
		expect(spy).toHaveBeenCalled();
	});

	it('refresh programatically', () => {
		const spy = vi.spyOn(Date, 'now');

		const reference = React.createRef<Captcha>();
		render(<Captcha ref={reference}/>);

		reference.current?.reset();
		expect(spy).toHaveBeenCalled();
	});
});

describe('recaptcha', () => {
	beforeEach(() => {
		window.blessing.extra = {recaptcha: 'sitekey', invisible: false};
	});

	it('retrieve value', async () => {
		window.blessing.extra.invisible = true;
		const spy = vi.spyOn(Reaptcha.prototype, 'execute');

		const reference = React.createRef<Captcha>();
		render(<Captcha ref={reference}/>);

		const value = await reference.current?.execute();
		expect(spy).toHaveBeenCalled();
		expect(value).toBe('token');
	});

	it('refresh programatically', () => {
		const spy = vi.spyOn(Reaptcha.prototype, 'reset');

		const reference = React.createRef<Captcha>();
		render(<Captcha ref={reference}/>);

		reference.current?.reset();
		expect(spy).toHaveBeenCalled();
	});
});
