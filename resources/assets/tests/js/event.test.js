import * as emitter from '@/js/event'

test('mount variable to global', () => {
  expect(window.bsEmitter).toBeFrozen()
})

test('add listener and emit event', () => {
  const mockA = jest.fn()
  const mockB = jest.fn()

  emitter.on('a', mockA)
  emitter.on('b', mockB)

  emitter.emit('a')

  expect(mockA).toBeCalledTimes(1)
  expect(mockB).not.toBeCalled()
})
