import * as emitter from '@/scripts/event';

test('add listener and emit event', () => {
	const mockA1 = vi.fn();
	const mockA2 = vi.fn();
	const mockB = vi.fn();

	emitter.on('a', mockA1);
	emitter.on('a', mockA2);
	emitter.on('b', mockB);

	emitter.emit('a');

	expect(mockA1).toBeCalledTimes(1);
	expect(mockA2).toBeCalledTimes(1);
	expect(mockB).not.toBeCalled();
});

test('not throw for un-existed event', () => {
	emitter.emit('c');
});

test('unsubscribe event', () => {
	const mock = vi.fn();

	const off = emitter.on('c', mock);
	off();

	expect(mock).not.toBeCalled();
});
