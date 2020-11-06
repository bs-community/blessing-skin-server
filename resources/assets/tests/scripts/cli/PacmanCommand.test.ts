import * as fetch from '@/scripts/net'
import pacman from '@/scripts/cli/PacmanCommand'
import { Stdio } from './stdio'

jest.mock('@/scripts/net')

test('no arguments', async () => {
  const stdio = new Stdio()
  await pacman(stdio, [])
  expect(stdio.getStdout()).toInclude('help')
  expect(fetch.post).not.toBeCalled()
})

describe('install plugin', () => {
  it('succeeded', async () => {
    fetch.post.mockResolvedValue({ code: 0, message: 'ok' })
    const stdio = new Stdio()

    await pacman(stdio, ['-S', 'test'])
    expect(fetch.post).toBeCalledWith('/admin/plugins/market/download', {
      name: 'test',
    })
    expect(stdio.getStdout()).toInclude('ok')
  })

  it('failed with reasons', async () => {
    fetch.post.mockResolvedValue({
      code: 1,
      message: 'failed',
      data: { reason: ['unresolved'] },
    })
    const stdio = new Stdio()

    await pacman(stdio, ['-S', 'test'])
    expect(fetch.post).toBeCalledWith('/admin/plugins/market/download', {
      name: 'test',
    })
    expect(stdio.getStdout()).toInclude('failed')
    expect(stdio.getStdout()).toInclude('- unresolved')
  })
})

describe('remove plugin', () => {
  beforeAll(() => jest.useRealTimers())

  it('cancelled', async () => {
    const stdio = new Stdio()

    setImmediate(() => process.stdin.emit('keypress', 'n', 'n'))
    await pacman(stdio, ['-R', 'test'])
    expect(fetch.post).not.toBeCalled()
  })

  it('succeeded', async () => {
    fetch.post.mockResolvedValue({ code: 0, message: 'ok' })
    const stdio = new Stdio()

    setImmediate(() => process.stdin.emit('keypress', 'y', 'y'))
    await pacman(stdio, ['-R', 'test'])
    expect(fetch.post).toBeCalledWith('/admin/plugins/manage', {
      action: 'delete',
      name: 'test',
    })
    expect(stdio.getStdout()).toInclude('ok')
  })
})
