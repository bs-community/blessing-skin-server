import * as fetch from '@/scripts/net'
import runCommand from '@/scripts/cli/RmCommand'
import { Stdio } from './stdio'

vi.mock('@/scripts/net')

test('missing operand', async () => {
  const stdio = new Stdio()
  await runCommand(stdio, [])
  expect(stdio.getStdout()).toInclude('missing operand')
  expect(fetch.post).not.toBeCalled()
})

test('without "rf"', async () => {
  const stdio = new Stdio()
  await runCommand(stdio, ['/'])
  expect(fetch.post).not.toBeCalled()
})

test('not from root', async () => {
  const stdio = new Stdio()
  await runCommand(stdio, ['-rf', '.'])
  expect(fetch.post).not.toBeCalled()
})

test('send request', async () => {
  const stdio = new Stdio()
  await runCommand(stdio, ['-rf', '/'])
  expect(fetch.post).toBeCalledWith('/admin/resource?clear-cache')
})
