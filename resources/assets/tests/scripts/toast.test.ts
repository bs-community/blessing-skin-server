import { render, fireEvent, screen } from '@testing-library/react'
import { Toast } from '@/scripts/toast'

test('"Toast" class', () => {
  const toast = new Toast(render)

  toast.success('success')
  expect(document.querySelector('.alert-success')!.textContent).toContain(
    'success',
  )

  toast.info('info')
  expect(document.querySelector('.alert-info')!.textContent).toContain('info')

  toast.warning('warning')
  expect(document.querySelector('.alert-warning')!.textContent).toContain(
    'warning',
  )

  toast.error('error')
  expect(document.querySelector('.alert-danger')!.textContent).toContain(
    'error',
  )

  vi.runAllTimers()
  toast.dispose()
})

test('clear toasts', () => {
  const toast = new Toast(render)

  toast.success('success')
  toast.info('info')

  toast.clear()
  expect(document.querySelectorAll('.alert')).toHaveLength(0)

  toast.dispose()
})

test('close toast manually', () => {
  const toast = new Toast(render)
  toast.success('success')

  fireEvent.click(screen.getByText('×'))
  expect(document.querySelectorAll('.alert')).toHaveLength(0)

  toast.dispose()
})
