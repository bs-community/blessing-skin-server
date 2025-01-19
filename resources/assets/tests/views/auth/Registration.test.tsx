import {t} from '@/scripts/i18n';
import * as fetch from '@/scripts/net';
import urls from '@/scripts/urls';
import Registration from '@/views/auth/Registration';
import {fireEvent, render, waitFor} from '@testing-library/react';
import {expect} from 'vitest';

vi.mock('@/scripts/net');

beforeEach(() => {
	window.blessing.extra = {player: false};
});

it('confirmation is not matched', () => {
	const {getByText, getByPlaceholderText, queryByText} = render(<Registration/>);

	fireEvent.input(getByPlaceholderText(t('auth.email')), {
		target: {value: 'a@b.c'},
	});
	fireEvent.input(getByPlaceholderText(t('auth.password')), {
		target: {value: 'password'},
	});
	fireEvent.input(getByPlaceholderText(t('auth.repeat-pwd')), {
		target: {value: 'password1'},
	});
	fireEvent.input(getByPlaceholderText(t('auth.nickname')), {
		target: {value: 't'},
	});
	fireEvent.input(getByPlaceholderText(t('auth.captcha')), {
		target: {value: 'a'},
	});
	fireEvent.click(getByText(t('auth.register')));

	expect(queryByText(t('auth.invalidConfirmPwd'))).toBeInTheDocument();
	expect(fetch.post).not.toBeCalled();
});

it('succeeded', async () => {
	fetch.post.mockResolvedValue({code: 0, message: 'ok'});
	const {getByText, getByPlaceholderText, getByRole, queryByText} = render(<Registration/>);

	fireEvent.input(getByPlaceholderText(t('auth.email')), {
		target: {value: 'a@b.c'},
	});
	fireEvent.input(getByPlaceholderText(t('auth.password')), {
		target: {value: 'password'},
	});
	fireEvent.input(getByPlaceholderText(t('auth.repeat-pwd')), {
		target: {value: 'password'},
	});
	fireEvent.input(getByPlaceholderText(t('auth.nickname')), {
		target: {value: 't'},
	});
	fireEvent.input(getByPlaceholderText(t('auth.captcha')), {
		target: {value: 'a'},
	});
	fireEvent.click(getByText(t('auth.register')));
	await waitFor(() => {
		expect(fetch.post).toBeCalledWith(urls.auth.register(), {
			email: 'a@b.c',
			password: 'password',
			nickname: 't',
			captcha: 'a',
		});
	});
	expect(queryByText('ok')).toBeInTheDocument();
	expect(getByRole('status')).toHaveClass('alert-success');
	vi.runAllTimers();
});

it('failed', async () => {
	fetch.post.mockResolvedValue({code: 1, message: 'failed'});
	const {getByText, getByPlaceholderText, queryByText} = render(<Registration/>);

	fireEvent.input(getByPlaceholderText(t('auth.email')), {
		target: {value: 'a@b.c'},
	});
	fireEvent.input(getByPlaceholderText(t('auth.password')), {
		target: {value: 'password'},
	});
	fireEvent.input(getByPlaceholderText(t('auth.repeat-pwd')), {
		target: {value: 'password'},
	});
	fireEvent.input(getByPlaceholderText(t('auth.nickname')), {
		target: {value: 't'},
	});
	fireEvent.input(getByPlaceholderText(t('auth.captcha')), {
		target: {value: 'a'},
	});
	fireEvent.click(getByText(t('auth.register')));
	await waitFor(() => {
		expect(fetch.post).toBeCalledWith(urls.auth.register(), {
			email: 'a@b.c',
			password: 'password',
			nickname: 't',
			captcha: 'a',
		});
	});
	expect(queryByText('failed')).toBeInTheDocument();
});

it('register with new player', async () => {
	window.blessing.extra = {player: true};
	fetch.post.mockResolvedValue({code: 0, message: 'ok'});
	const {getByText, getByPlaceholderText, queryByText} = render(<Registration/>);

	fireEvent.input(getByPlaceholderText(t('auth.email')), {
		target: {value: 'a@b.c'},
	});
	fireEvent.input(getByPlaceholderText(t('auth.password')), {
		target: {value: 'password'},
	});
	fireEvent.input(getByPlaceholderText(t('auth.repeat-pwd')), {
		target: {value: 'password'},
	});
	fireEvent.input(getByPlaceholderText(t('auth.player-name')), {
		target: {value: 'player'},
	});
	fireEvent.input(getByPlaceholderText(t('auth.captcha')), {
		target: {value: 'a'},
	});
	fireEvent.click(getByText(t('auth.register')));
	await waitFor(() => {
		expect(fetch.post).toBeCalledWith(urls.auth.register(), {
			email: 'a@b.c',
			password: 'password',
			player_name: 'player',
			captcha: 'a',
		});
	});
	expect(queryByText('ok')).toBeInTheDocument();
});
