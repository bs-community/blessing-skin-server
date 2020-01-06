import { flushPromises } from '../../utils'
import { showModal } from '@/scripts/notify'
import { post } from '@/scripts/net'
import handler from '@/views/admin/Update'

jest.mock('@/scripts/notify')
jest.mock('@/scripts/net')

test('click button', async () => {
  post.mockResolvedValueOnce({ code: 1, message: 'failed' })
    .mockResolvedValue({ code: 0, message: 'ok' })

  const button = document.createElement('button')
  button.addEventListener('click', handler)

  const event = new MouseEvent('click')
  button.dispatchEvent(event)
  await flushPromises()
  expect(showModal).toBeCalledWith({ mode: 'alert', text: 'failed' })

  button.dispatchEvent(event)
  await flushPromises()
  expect(showModal).toBeCalledWith({ mode: 'alert', text: 'ok' })
})
