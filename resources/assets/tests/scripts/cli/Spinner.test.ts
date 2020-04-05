import { Spinner } from '@/scripts/cli/Spinner'
import { Stdio } from './stdio'

test('run', () => {
  const stdio = new Stdio()
  const spinner = new Spinner(stdio)

  spinner.start()

  jest.runTimersToTime(500)
  expect(stdio.getStdout().length).toBeGreaterThan(0)

  spinner.stop()
})
