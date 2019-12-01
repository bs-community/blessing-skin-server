import $ from 'jquery'
import { showModal } from '@/scripts/modal'

test('show modal', async () => {
  process.nextTick(() => {
    expect(
      document.querySelector('.modal-title')!.textContent,
    ).toBe('general.tip')
    document.querySelector<HTMLButtonElement>('.btn-primary')!.click()
  })
  const { value } = await showModal()
  expect(value).toBe('')

  $('.modal').trigger('hidden.bs.modal')
  expect(document.querySelector('.modal')).toBeNull()
})
