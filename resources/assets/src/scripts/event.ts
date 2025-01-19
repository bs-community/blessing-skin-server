const bus = new Map<string | symbol, Set<(...args: any[]) => void>>();

export function on(event: string | symbol, listener: (...args: any[]) => void) {
	if (!bus.has(event)) {
		bus.set(event, new Set());
	}

	const listeners = bus.get(event)!;
	listeners.add(listener);

	return () => {
		listeners.delete(listener);
	};
}

export function emit(event: string | symbol, payload?: unknown) {
	bus.get(event)?.forEach(listener => {
		listener(payload);
	});
}

blessing.event = {on, emit};
