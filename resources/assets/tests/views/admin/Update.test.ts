import {post} from '@/scripts/net';
import {showModal} from '@/scripts/notify';
import handler from '@/views/admin/Update';
import {expect} from 'vitest';
import {flushPromises} from '../../utils';

vi.mock('@/scripts/notify');
vi.mock('@/scripts/net');

it('click button', async () => {
	post
		.mockResolvedValueOnce({code: 1, message: 'failed'})
		.mockResolvedValue({code: 0, message: 'ok'});

	const button = document.createElement('button');
	button.addEventListener('click', handler);

	const event = new MouseEvent('click');
	button.dispatchEvent(event);
	await flushPromises();
	expect(showModal).toBeCalledWith({mode: 'alert', text: 'failed'});

	button.dispatchEvent(event);
	await flushPromises();
	expect(showModal).toBeCalledWith({mode: 'alert', text: 'ok'});
});
