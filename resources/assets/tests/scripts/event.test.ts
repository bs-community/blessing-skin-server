import * as emitter from '@/scripts/event'

test('add listener and emit event', () => {
  const mockA = jest.fn()
  const mockB = jest.fn()

  emitter.on('a', mockA)
  emitter.on('b', mockB)

  emitter.emit('a')

  expect(mockA).toBeCalledTimes(1)
  expect(mockB).not.toBeCalled()
})
