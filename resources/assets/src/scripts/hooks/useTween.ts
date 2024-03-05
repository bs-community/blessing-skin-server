import {useState, useEffect, useRef} from 'react';
import TWEEN from '@tweenjs/tween.js';

export default function useTween<T = any>(initialValue: T) {
	const [value, setValue] = useState<T>(initialValue);
	const reference = useRef<T>(value);
	const [destination, setDestination] = useState<T>(initialValue);

	useEffect(() => {
		function animate() {
			requestAnimationFrame(animate);
			TWEEN.update();
			setValue(reference.current);
		}

		const tween = new TWEEN.Tween(reference);
		tween.to({current: destination}, 1000).start();
		animate();
	}, [destination]);

	return [value, setDestination] as const;
}
