import { logout } from '@/scripts/logout'
import { post } from '@/scripts/net'
import { MessageBox } from '@/scripts/element'

jest.mock('@/scripts/net')
jest.mock('@/scripts/element')

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
