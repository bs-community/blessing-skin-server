import { logout } from '@/js/logout'
import { post } from '@/js/net'
import { MessageBox } from '@/js/element'

jest.mock('@/js/net')
jest.mock('@/js/element')

test('log out', async () => {
  MessageBox.confirm
    .mockRejectedValueOnce('cancel')
    .mockResolvedValue('confirm')
  post.mockResolvedValue({ msg: '' })

  await logout()
  expect(post).not.toBeCalled()

  await logout()
  expect(post).toBeCalledWith('/auth/logout')
  jest.runAllTimers()
})
