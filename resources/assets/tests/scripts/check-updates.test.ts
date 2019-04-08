import {
  checkForUpdates,
  checkForPluginUpdates,
} from '@/scripts/check-updates'
import { init } from '@/scripts/net'

test('check for BS updates', async () => {
  window.fetch = jest.fn()
    .mockResolvedValueOnce({ ok: false })
    .mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve({ available: false }),
    })
    .mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve({ available: true }),
    })

  document.body.innerHTML = '<a href="/admin/update"></a>'

  await checkForUpdates()
  expect(window.fetch).toBeCalledWith('/admin/update/check', init)
  expect(document.querySelector('a')!.innerHTML).toBe('')

  await checkForUpdates()
  expect(document.querySelector('a')!.innerHTML).toBe('')

  await checkForUpdates()
  expect(document.querySelector('a')!.innerHTML).toContain('1')
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

  document.body.innerHTML = '<a href="/admin/plugins/market"></a>'

  await checkForPluginUpdates()
  expect(window.fetch).toBeCalledWith('/admin/plugins/market/check', init)
  expect(document.querySelector('a')!.innerHTML).toBe('')

  await checkForPluginUpdates()
  expect(document.querySelector('a')!.innerHTML).toBe('')

  await checkForPluginUpdates()
  expect(document.querySelector('a')!.innerHTML).toContain('1')
})

test('do not update anything if element not found', async () => {
  window.fetch = jest.fn()
    .mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve({ available: true, latest: '4.0.0' }),
    })
    .mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve({ available: true, plugins: [{}] }),
    })

  await Promise.all([checkForUpdates, checkForPluginUpdates])
})
