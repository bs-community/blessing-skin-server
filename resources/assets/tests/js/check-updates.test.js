import {
  checkForUpdates,
  checkForPluginUpdates,
} from '@/js/check-updates'
import { init } from '@/js/net'

test('check for BS updates', async () => {
  window.fetch = jest.fn()
    .mockResolvedValueOnce({ ok: false })
    .mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve({ available: false }),
    })
    .mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve({ available: true, latest: '4.0.0' }),
    })

  document.body.innerHTML = `
        <a href="/admin/update"></a>
    `

  await checkForUpdates()
  expect(window.fetch).toBeCalledWith('/admin/update/check', init)
  expect(document.querySelector('a').innerHTML).toBe('')

  await checkForUpdates()
  expect(document.querySelector('a').innerHTML).toBe('')

  await checkForUpdates()
  expect(document.querySelector('a').innerHTML).toContain('4.0.0')
})

test('check for plugins updates', async () => {
  window.fetch = jest.fn()
    .mockResolvedValueOnce({ ok: false })
    .mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve({ available: false }),
    })
    .mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve({ available: true, plugins: [{}] }),
    })

  document.body.innerHTML = `
        <a href="/admin/plugins/market"></a>
    `

  await checkForPluginUpdates()
  expect(window.fetch).toBeCalledWith('/admin/plugins/market/check', init)
  expect(document.querySelector('a').innerHTML).toBe('')

  await checkForPluginUpdates()
  expect(document.querySelector('a').innerHTML).toBe('')

  await checkForPluginUpdates()
  expect(document.querySelector('a').innerHTML).toContain('1')
})
