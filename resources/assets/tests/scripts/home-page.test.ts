import handler from '@/scripts/home-page'

window.blessing.extra = {
  transparent_navbar: false,
}

test('should be transparent at top', () => {
  Object.assign(window, { innerHeight: 900 })
  document.body.innerHTML = '<header class="main-header"></header>'
  handler()
  window.dispatchEvent(new Event('scroll'))
  expect(
    document.querySelector('header')!.classList.contains('transparent')
  ).toBeTrue()
})

test('should not be transparent at bottom', () => {
  Object.assign(window, { innerHeight: 900, scrollY: 800 })
  document.body.innerHTML = '<header class="main-header transparent"></header>'
  handler()
  window.dispatchEvent(new Event('scroll'))
  expect(
    document.querySelector('header')!.classList.contains('transparent')
  ).toBeFalse()
})
