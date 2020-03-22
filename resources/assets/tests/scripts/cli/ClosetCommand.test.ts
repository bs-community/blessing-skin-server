import * as fetch from '@/scripts/net'
import runCommand from '@/scripts/cli/ClosetCommand'
import { Stdio } from './stdio'

jest.mock('@/scripts/net')

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
