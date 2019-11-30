import 'bootstrap'
import * as modal from '@/scripts/modal'

test('show modal', async () => {
  process.nextTick(() => {
    expect(
      document.querySelector('.modal-title')!.textContent,
    ).toBe('general.tip')
    document.querySelector<HTMLButtonElement>('.btn-primary')!.click()
  })
  const { value } = await modal.showModal()
  expect(value).toBe('')
})
