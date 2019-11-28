import 'bootstrap'
import * as notify from '@/scripts/notify'

test('show modal', async () => {
  process.nextTick(() => {
    expect(
      document.querySelector('.modal-title')!.textContent,
    ).toBe('general.tip')
    document.querySelector<HTMLButtonElement>('.btn-primary')!.click()
  })
  const { value } = await notify.showModal()
  expect(value).toBe('')
})
