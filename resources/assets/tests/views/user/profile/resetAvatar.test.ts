import { showModal, toast } from '@/scripts/notify'
import { post } from '@/scripts/net'
import resetAvatar from '@/views/user/profile/resetAvatar'

jest.mock('@/scripts/notify')
jest.mock('@/scripts/net')

test('reset avatar', async () => {
  showModal.mockRejectedValueOnce(null).mockResolvedValue({ value: '' })
  post.mockResolvedValue({ message: 'ok' })
  document.body.innerHTML = '<img alt="User Image" src="a">'

  await resetAvatar()
  expect(post).not.toBeCalled()

  await resetAvatar()
  expect(post).toBeCalledWith('/user/profile/avatar', { tid: 0 })
  expect(toast.success).toBeCalledWith('ok')
  expect(document.querySelector('img')!.src).toBe('/avatar/0')
})
