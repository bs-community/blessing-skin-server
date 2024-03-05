import {logout} from '@/scripts/logout';
import {post} from '@/scripts/net';
import {showModal} from '@/scripts/notify';
import urls from '@/scripts/urls';

vi.mock('@/scripts/net');
vi.mock('@/scripts/notify');

test('log out', async () => {
	showModal.mockRejectedValueOnce(null).mockResolvedValueOnce({value: ''});
	post.mockResolvedValue({message: ''});

	await logout();
	expect(post).not.toBeCalled();

	await logout();
	expect(post).toBeCalledWith(urls.auth.logout());
});
