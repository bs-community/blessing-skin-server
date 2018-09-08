import { logout } from '@/js/logout';
import { post } from '@/js/net';
import { swal } from '@/js/notify';

jest.mock('@/js/net');
jest.mock('@/js/notify');

test('log out', async () => {
    swal.mockResolvedValueOnce({ dismiss: 1 }).mockResolvedValueOnce({});
    post.mockResolvedValue({ msg: '' });

    await logout();
    expect(post).not.toBeCalled();

    await logout();
    expect(post).toBeCalledWith('/auth/logout');
    jest.runAllTimers();
});
