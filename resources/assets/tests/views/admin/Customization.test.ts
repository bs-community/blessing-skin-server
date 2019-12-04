import {
  registerNavbarPicker,
  registerSidebarPicker,
} from '@/views/admin/Customization'

test('preview navbar color', () => {
  const nav = document.createElement('nav')
  nav.className = 'navbar-primary navbar-dark'
  const picker = document.createElement('div')
  picker.innerHTML = `
    <label><input type="radio" name="navbar" value="cyan"></label>
    <label><input type="radio" name="navbar" value="orange"></label>
  `
  const cyan = picker.querySelector<HTMLInputElement>('[value=cyan]')!
  const orange = picker.querySelector<HTMLInputElement>('[value=orange]')!

  registerNavbarPicker(nav, picker, 'primary')
  cyan.click()
  expect(nav.className).toContain('navbar-cyan')
  expect(nav.className).toContain('navbar-dark')
  expect(nav.className).not.toContain('navbar-light')
  orange.click()
  expect(nav.className).toContain('navbar-orange')
  expect(nav.className).not.toContain('navbar-cyan')
  expect(nav.className).toContain('navbar-light')
  expect(nav.className).not.toContain('navbar-dark')
})

test('preview sidebar color', () => {
  const sidebar = document.createElement('aside')
  sidebar.className = 'sidebar-dark-primary'

  const darkPicker = document.createElement('div')
  darkPicker.innerHTML = `
    <label><input type="radio" name="sidebar" value="dark-cyan"></label>`
  const darkCyan = darkPicker.querySelector<HTMLInputElement>('[value="dark-cyan"]')!

  const lightPicker = document.createElement('div')
  lightPicker.innerHTML = `
    <label><input type="radio" name="sidebar" value="light-cyan"></label>`
  const lightCyan = lightPicker.querySelector<HTMLInputElement>('[value="light-cyan"]')!

  registerSidebarPicker(
    sidebar,
    { dark: darkPicker, light: lightPicker },
    'dark-primary',
  )
  darkCyan.click()
  expect(sidebar.className).toContain('sidebar-dark-cyan')
  lightCyan.click()
  expect(sidebar.className).toContain('sidebar-light-cyan')
})
