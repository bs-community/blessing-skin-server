import React from 'react'
import { render, fireEvent, screen } from '@testing-library/react'
import { Toast, ToastContainer } from '@/scripts/toast'

test('"Toast" class', () => {
  render(<ToastContainer />)
  const toast = new Toast()

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

  jest.runAllTimers()
  toast.dispose()
})

test('clear toasts', () => {
  render(<ToastContainer />)
  const toast = new Toast()

  toast.success('success')
  toast.info('info')

  toast.clear()
  expect(document.querySelectorAll('.alert')).toHaveLength(0)

  toast.dispose()
})

test('close toast manually', () => {
  render(<ToastContainer />)
  const toast = new Toast()
  toast.success('success')

  fireEvent.click(screen.getByText('×'))
  expect(document.querySelectorAll('.alert')).toHaveLength(0)

  toast.dispose()
})
