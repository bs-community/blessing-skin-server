import { logout } from '@/scripts/logout'
import { post } from '@/scripts/net'
import { showModal } from '@/scripts/notify'

jest.mock('@/scripts/net')
jest.mock('@/scripts/notify')

test('log out', async () => {
  showModal
    .mockRejectedValueOnce({})
    .mockResolvedValueOnce({ value: '' })
  post.mockResolvedValue({ message: '' })

  await logout()
  expect(post).not.toBeCalled()

  await logout()
  expect(post).toBeCalledWith('/auth/logout')
})
