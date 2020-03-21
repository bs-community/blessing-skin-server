import * as fetch from '@/scripts/net'
import runCommand from '@/scripts/cli/ClosetCommand'
import { Stdio } from './stdio'

jest.mock('@/scripts/net')

test('help message', async () => {
  let stdio = new Stdio()
  await runCommand(stdio, ['-h'])
  expect(stdio.getStdout()).toInclude('Usage')

  stdio = new Stdio()
  await runCommand(stdio, ['--help'])
  expect(stdio.getStdout()).toInclude('Usage')
})

test('missing subcommand', async () => {
  const stdio = new Stdio()
  await runCommand(stdio, [])
  expect(fetch.post).not.toBeCalled()
  expect(fetch.del).not.toBeCalled()
})

test('unsupported subcommand', async () => {
  const stdio = new Stdio()
  await runCommand(stdio, ['abc'])
  expect(fetch.post).not.toBeCalled()
  expect(fetch.del).not.toBeCalled()
})

test('missing uid', async () => {
  const stdio = new Stdio()
  await runCommand(stdio, ['add'])
  expect(stdio.getStdout()).toInclude('User ID')
  expect(fetch.post).not.toBeCalled()
  expect(fetch.del).not.toBeCalled()
})

test('missing tid', async () => {
  const stdio = new Stdio()
  await runCommand(stdio, ['add', '1'])
  expect(stdio.getStdout()).toInclude('Texture ID')
  expect(fetch.post).not.toBeCalled()
  expect(fetch.del).not.toBeCalled()
})

describe('add texture', () => {
  it('succeeded', async () => {
    fetch.post.mockResolvedValue({
      code: 0,
      data: { user: { nickname: 'kumiko' }, texture: { name: 'eupho' } },
    })

    const stdio = new Stdio()
    await runCommand(stdio, ['add', '1', '2'])

    const stdout = stdio.getStdout()
    expect(stdout).toInclude('kumiko')
    expect(stdout).toInclude('eupho')
    expect(fetch.post).toBeCalledWith('/admin/closet/1', { tid: '2' })
  })

  it('failed', async () => {
    fetch.post.mockResolvedValue({ code: 1 })

    const stdio = new Stdio()
    await runCommand(stdio, ['add', '1', '2'])

    const stdout = stdio.getStdout()
    expect(stdout).toInclude('Error occurred.')
    expect(fetch.post).toBeCalledWith('/admin/closet/1', { tid: '2' })
  })
})

describe('remove texture', () => {
  it('succeeded', async () => {
    fetch.del.mockResolvedValue({
      code: 0,
      data: { user: { nickname: 'kumiko' }, texture: { name: 'eupho' } },
    })

    const stdio = new Stdio()
    await runCommand(stdio, ['remove', '1', '2'])

    const stdout = stdio.getStdout()
    expect(stdout).toInclude('kumiko')
    expect(stdout).toInclude('eupho')
    expect(fetch.del).toBeCalledWith('/admin/closet/1', { tid: '2' })
  })

  it('failed', async () => {
    fetch.del.mockResolvedValue({ code: 1 })

    const stdio = new Stdio()
    await runCommand(stdio, ['remove', '1', '2'])

    const stdout = stdio.getStdout()
    expect(stdout).toInclude('Error occurred.')
    expect(fetch.del).toBeCalledWith('/admin/closet/1', { tid: '2' })
  })
})
