import { get } from '@/scripts/net'
import { showModal } from '@/scripts/notify'
import { flushPromises } from '../utils'
import handler from '@/scripts/notification'

jest.mock('@/scripts/net')
jest.mock('@/scripts/notify')

test('read notification', async () => {
  document.body.innerHTML = `
    <div class="notifications-list">
      <span class="notifications-counter">2</span>
      <a data-nid="1"></a>
      <a data-nid="2"></a>
    </div>
  `
  document.querySelector('.notifications-list')!.addEventListener('click', handler)
  get.mockResolvedValue({
    title: 'title',
    content: 'content',
    time: 'time',
  })

  document.querySelector('a')!.click()
  await flushPromises()
  expect(get).toBeCalledWith('/user/notifications/1')
  expect(showModal).toBeCalledWith('content<br><small>time</small>', 'title')
  expect(document.querySelectorAll('a')).toHaveLength(1)
  expect(document.querySelector('span')!.textContent).toBe('1')

  document.querySelector('a')!.click()
  await flushPromises()
  expect(document.querySelector('span')).toBeNull()
})
