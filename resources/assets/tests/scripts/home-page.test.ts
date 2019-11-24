import handler from '@/scripts/home-page'

window.blessing.extra = {
  transparent_navbar: false,
}

test('should be transparent at top', () => {
  Object.assign(window, { innerHeight: 900 })
  document.body.innerHTML = '<nav class="navbar"></nav>'
  handler()
  window.dispatchEvent(new Event('scroll'))
  expect(
    document.querySelector('nav')!.classList.contains('transparent')
  ).toBeTrue()
})

test('should not be transparent at bottom', () => {
  Object.assign(window, { innerHeight: 900, scrollY: 800 })
  document.body.innerHTML = '<nav class="navbar transparent"></nav>'
  handler()
  window.dispatchEvent(new Event('scroll'))
  expect(
    document.querySelector('nav')!.classList.contains('transparent')
  ).toBeFalse()
})
