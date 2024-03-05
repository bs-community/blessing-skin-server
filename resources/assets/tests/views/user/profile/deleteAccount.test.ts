import {expect, vi, test} from 'vitest';
import {flushPromises} from '../../../utils';
import {showModal} from '@/scripts/notify';
import {post} from '@/scripts/net';
import handler from '@/views/user/profile/deleteAccount';

vi.mock('@/scripts/notify');
vi.mock('@/scripts/net');

test('delete account', async () => {
	post
		.mockResolvedValueOnce({code: 1, message: 'w'})
		.mockResolvedValue({code: 0, message: 'o'});

	const form = document.createElement('form');
	form.addEventListener('submit', handler);

	const password = document.createElement('input');
	password.name = 'password';
	password.value = 'abc';
	form.append(password);
	form.password = password;

	const event = new Event('submit');
	form.dispatchEvent(event);
	await flushPromises();
	expect(post).toBeCalledWith('/user/profile?action=delete', {
		password: 'abc',
	});
	expect(showModal).toBeCalledWith({mode: 'alert', text: 'w'});

	form.dispatchEvent(event);
	await flushPromises();
	expect(showModal).toBeCalledWith({mode: 'alert', text: 'o'});
});
