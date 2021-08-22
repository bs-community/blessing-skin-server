import { scrollHander } from '@/scripts/homePage'

describe('scroll handler', () => {
  beforeAll(() => {
    window.blessing.extra = {
      transparent_navbar: false,
    }
  })

  it('should be transparent at top', () => {
    Object.assign(window, { innerHeight: 900 })
    document.body.innerHTML = '<nav class="navbar"></nav>'
    scrollHander()
    window.dispatchEvent(new Event('scroll'))
    expect(
      document.querySelector('nav')!.classList.contains('transparent'),
    ).toBeTrue()
  })

  it('should not be transparent at bottom', () => {
    Object.assign(window, { innerHeight: 900, scrollY: 800 })
    document.body.innerHTML = '<nav class="navbar transparent"></nav>'
    scrollHander()
    window.dispatchEvent(new Event('scroll'))
    expect(
      document.querySelector('nav')!.classList.contains('transparent'),
    ).toBeFalse()
  })
})
