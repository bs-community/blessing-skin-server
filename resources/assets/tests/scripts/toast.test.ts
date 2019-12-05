import {
  showToast, Toast, ToastQueue,
} from '@/scripts/toast'

beforeEach(() => {
  document.body.innerHTML = ''
})

test('"Toast" class', () => {
  const toast = new Toast()

  toast.success('success')
  expect(
    document.querySelector('.alert-success')!.textContent,
  ).toContain('success')

  toast.info('info')
  expect(
    document.querySelector('.alert-info')!.textContent,
  ).toContain('info')

  toast.warning('warning')
  expect(
    document.querySelector('.alert-warning')!.textContent,
  ).toContain('warning')

  toast.error('error')
  expect(
    document.querySelector('.alert-danger')!.textContent,
  ).toContain('error')

  // Should pass.
  toast.success()
  toast.info()
  toast.warning()
  toast.error()
})

test('top position', () => {
  showToast([], 'info')
  expect(
    document.querySelector<HTMLDivElement>('.alert-info')!.parentElement!.style.top,
  ).toBe('35px')

  showToast(
    [{ height: 20, el: { offsetTop: 30, offsetHeight: 20 } as HTMLDivElement }],
    'error',
  )
  expect(
    document.querySelector<HTMLDivElement>('.alert-danger')!.parentElement!.style.top,
  ).toBe('62px')
})

test('delay show', () => {
  const queue: ToastQueue = []
  showToast(queue, 'info')
  jest.advanceTimersByTime(100)
  expect(document.querySelector('.fade')!.classList.contains('show')).toBeTrue()
  expect(queue).toHaveLength(1)
})

test('move queue', () => {
  const queue: ToastQueue = []
  const fake = { offsetTop: 30, style: {} } as HTMLDivElement

  showToast(queue, 'info')
  queue[0].height = 10
  queue.push({ height: 0, el: fake })
  jest.runAllTimers()
  expect(fake.style.top).toBe('8px')
})
