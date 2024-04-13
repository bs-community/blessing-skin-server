import {useEffect, useRef} from 'react';

export default function useMount(selector: string): HTMLElement | undefined {
	const container = useRef<HTMLDivElement | undefined>(null);

	useEffect(() => {
		const mount = document.querySelector(selector)!;
		const div = document.createElement('div');
		container.current = div;

		mount.append(div);

		return () => {
			div.remove();
			container.current = null;
		};
	}, [selector]);

	return container.current;
}
