import { logout } from '@/scripts/logout'
import { post } from '@/scripts/net'
import { MessageBox } from 'element-ui'

jest.mock('element-ui')
jest.mock('@/scripts/net')

test('log out', async () => {
  jest.spyOn(MessageBox, 'confirm')
    .mockRejectedValueOnce('cancel')
    .mockResolvedValue('confirm')
  post.mockResolvedValue({ message: '' })

  await logout()
  expect(post).not.toBeCalled()

  await logout()
  expect(post).toBeCalledWith('/auth/logout')
  jest.runAllTimers()
})
